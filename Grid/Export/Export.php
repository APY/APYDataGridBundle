<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\Column\ArrayColumn;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Twig\Template;

abstract class Export implements ExportInterface, ContainerAwareInterface
{
    const DEFAULT_TEMPLATE = 'APYDataGridBundle::blocks.html.twig';

    protected $title;

    protected $fileName;

    protected $fileExtension = null;

    protected $mimeType = 'application/octet-stream';

    protected $parameters = [];

    protected $container;

    protected $templates;

    protected $twig;

    protected $grid;

    protected $params = [];

    protected $content;

    protected $charset;

    protected $role;

    /**
     * Default Export constructor.
     *
     * @param string $title    Title of the export
     * @param string $fileName FileName of the export
     * @param array  $params   Additionnal parameters for the export
     * @param string $charset  Charset of the exported data
     * @param string $role     Security role
     *
     * @return \APY\DataGridBundle\Grid\Export\Export
     */
    public function __construct($title, $fileName = 'export', $params = [], $charset = 'UTF-8', $role = null)
    {
        $this->title = $title;
        $this->fileName = $fileName;
        $this->params = $params;
        $this->charset = $charset;
        $this->role = $role;
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     *
     * @return \APY\DataGridBundle\Grid\Export\Export
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

        $this->twig = $this->container->get('twig');

        return $this;
    }

    /**
     * gets the Container associated with this Controller.
     *
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * gets the export Response.
     *
     * @return Response
     */
    public function getResponse()
    {
        // Response
        $kernelCharset = $this->container->getParameter('kernel.charset');
        if ($this->charset != $kernelCharset && function_exists('mb_strlen')) {
            $this->content = mb_convert_encoding($this->content, $this->charset, $kernelCharset);
            $filesize = mb_strlen($this->content, '8bit');
        } else {
            $filesize = strlen($this->content);
            $this->charset = $kernelCharset;
        }

        $headers = [
            'Content-Description'       => 'File Transfer',
            'Content-Type'              => $this->getMimeType(),
            'Content-Disposition'       => sprintf('attachment; filename="%s"', $this->getBaseName()),
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control'             => 'must-revalidate',
            'Pragma'                    => 'public',
            'Content-Length'            => $filesize,
        ];

        $response = new Response($this->content, 200, $headers);
        $response->setCharset($this->charset);
        $response->expire();

        return $response;
    }

    /**
     * sets the Content of the export.
     *
     * @param string $content
     *
     * @return self
     */
    public function setContent($content = '')
    {
        $this->content = $content;

        return $this;
    }

    /**
     * gets the Content of the export.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get data form the grid.
     *
     * @param Grid $grid
     *
     * @return array
     *
     * array(
     *     'titles' => array(
     *         'column_id_1' => 'column_title_1',
     *         'column_id_2' => 'column_title_2'
     *     ),
     *     'rows' =>array(
     *          array(
     *              'column_id_1' => 'cell_value_1_1',
     *              'column_id_2' => 'cell_value_1_2'
     *          ),
     *          array(
     *              'column_id_1' => 'cell_value_2_1',
     *              'column_id_2' => 'cell_value_2_2'
     *          )
     *     )
     * )
     */
    protected function getGridData($grid)
    {
        $result = [];

        $this->grid = $grid;

        if ($this->grid->isTitleSectionVisible()) {
            $result['titles'] = $this->getGridTitles();
        }

        $result['rows'] = $this->getGridRows();

        return $result;
    }

    protected function getRawGridData($grid)
    {
        $result = [];
        $this->grid = $grid;

        if ($this->grid->isTitleSectionVisible()) {
            $result['titles'] = $this->getRawGridTitles();
        }

        $result['rows'] = $this->getRawGridRows();

        return $result;
    }

    /**
     * Get data form the grid in a flat array.
     *
     * @param Grid $grid
     *
     * @return array
     *
     * array(
     *     '0' => array(
     *         'column_id_1' => 'column_title_1',
     *         'column_id_2' => 'column_title_2'
     *     ),
     *     '1' => array(
     *          'column_id_1' => 'cell_value_1_1',
     *          'column_id_2' => 'cell_value_1_2'
     *      ),
     *     '2' => array(
     *          'column_id_1' => 'cell_value_2_1',
     *          'column_id_2' => 'cell_value_2_2'
     *      )
     * )
     */
    protected function getFlatGridData($grid)
    {
        $data = $this->getGridData($grid);

        $flatData = [];
        if (isset($data['titles'])) {
            $flatData[] = $data['titles'];
        }

        return array_merge($flatData, $data['rows']);
    }

    protected function getFlatRawGridData($grid)
    {
        $data = $this->getRawGridData($grid);

        $flatData = [];
        if (isset($data['titles'])) {
            $flatData[] = $data['titles'];
        }

        return array_merge($flatData, $data['rows']);
    }

    protected function getGridTitles()
    {
        $titlesHTML = $this->renderBlock('grid_titles', ['grid' => $this->grid]);

        preg_match_all('#<th[^>]*?>(.*)?</th>#isU', $titlesHTML, $matches);

        if (empty($matches)) {
            preg_match_all('#<td[^>]*?>(.*)?</td>#isU', $titlesHTML, $matches);
        }

        if (empty($matches)) {
            new \Exception('Table header (th or td) tags not found.');
        }

        $titlesClean = array_map([$this, 'cleanHTML'], $matches[0]);

        $i = 0;
        $titles = [];

        foreach ($this->grid->getColumns() as $column) {
            if ($column->isVisible(true)) {
                if (!isset($titlesClean[$i])) {
                    throw new \OutOfBoundsException('There are more visible columns than titles found.');
                }
                $titles[$column->getId()] = $titlesClean[$i++];
            }
        }

        return $titles;
    }

    protected function getRawGridTitles()
    {
        $translator = $this->container->get('translator');

        $titles = [];
        foreach ($this->grid->getColumns() as $column) {
            if ($column->isVisible(true)) {
                $titles[] = utf8_decode($translator->trans(/* @Ignore */$column->getTitle()));
            }
        }

        return $titles;
    }

    protected function getGridRows()
    {
        $rows = [];
        foreach ($this->grid->getRows() as $i => $row) {
            foreach ($this->grid->getColumns() as $column) {
                if ($column->isVisible(true)) {
                    $cellHTML = $this->getGridCell($column, $row);
                    $rows[$i][$column->getId()] = $this->cleanHTML($cellHTML);
                }
            }
        }

        return $rows;
    }

    protected function getRawGridRows()
    {
        $rows = [];
        foreach ($this->grid->getRows() as $i => $row) {
            foreach ($this->grid->getColumns() as $column) {
                if ($column->isVisible(true)) {
                    $rows[$i][$column->getId()] = $row->getField($column->getId());
                }
            }
        }

        return $rows;
    }

    protected function getGridCell($column, $row)
    {
        $values = $row->getField($column->getId());

        // Cast a datetime won't work.
        if ($column instanceof ArrayColumn || !is_array($values)) {
            $values = [$values];
        }

        $separator = $column->getSeparator();

        $block = null;
        $return = [];
        foreach ($values as $sourceValue) {
            $value = $column->renderCell($sourceValue, $row, $this->container->get('router'));

            $id = $this->grid->getId();

            if (($id != '' && ($block !== null
                        || $this->hasBlock($block = 'grid_' . $id . '_column_' . $column->getRenderBlockId() . '_cell')
                        || $this->hasBlock($block = 'grid_' . $id . '_column_' . $column->getType() . '_cell')
                        || $this->hasBlock($block = 'grid_' . $id . '_column_' . $column->getParentType() . '_cell')))
                || $this->hasBlock($block = 'grid_' . $id . '_column_id_' . $column->getRenderBlockId() . '_cell')
                || $this->hasBlock($block = 'grid_' . $id . '_column_type_' . $column->getType() . '_cell')
                || $this->hasBlock($block = 'grid_' . $id . '_column_type_' . $column->getParentType() . '_cell')
                || $this->hasBlock($block = 'grid_column_' . $column->getRenderBlockId() . '_cell')
                || $this->hasBlock($block = 'grid_column_' . $column->getType() . '_cell')
                || $this->hasBlock($block = 'grid_column_' . $column->getParentType() . '_cell')
                || $this->hasBlock($block = 'grid_column_id_' . $column->getRenderBlockId() . '_cell')
                || $this->hasBlock($block = 'grid_column_type_' . $column->getType() . '_cell')
                || $this->hasBlock($block = 'grid_column_type_' . $column->getParentType() . '_cell')) {
                $html = $this->renderBlock($block, ['grid' => $this->grid, 'column' => $column, 'row' => $row, 'value' => $value, 'sourceValue' => $sourceValue]);
            } else {
                $html = $this->renderBlock('grid_column_cell', ['grid' => $this->grid, 'column' => $column, 'row' => $row, 'value' => $value, 'sourceValue' => $sourceValue]);
                $block = null;
            }

            // Fix blank separator. The <br /> will be removed by the HTML cleaner.
            if (false !== strpos($separator, 'br')) {
                $html = str_replace($separator, ',', $html);
            }

            $return[] = $html;
        }

        $value = implode($separator, $return);

        return $value;
    }

    /**
     * Has block.
     *
     * @param $name string
     *
     * @return bool
     */
    protected function hasBlock($name)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name, [])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render block.
     *
     * @param $name string
     * @param $parameters string
     *
     * @return string
     */
    protected function renderBlock($name, $parameters)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name, [])) {
                return $template->renderBlock($name, array_merge($parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, 'ee'));
    }

    /**
     * Template Loader.
     *
     * @throws \Exception
     *
     * @return Template[]
     */
    protected function getTemplates()
    {
        if (empty($this->templates)) {
            $this->setTemplate($this->grid->getTemplate());
        }

        return $this->templates;
    }

    /**
     * set template.
     *
     * @param Template|string $template
     *
     * @throws \Exception
     *
     * @return \APY\DataGridBundle\Grid\Export\Export
     */
    public function setTemplate($template)
    {
        if (is_string($template)) {
            if (substr($template, 0, 8) === '__SELF__') {
                $this->templates = $this->getTemplatesFromString(substr($template, 8));
                $this->templates[] = $this->twig->loadTemplate(static::DEFAULT_TEMPLATE);
            } else {
                $this->templates = $this->getTemplatesFromString($template);
            }
        } elseif ($this->templates === null) {
            $this->templates[] = $this->twig->loadTemplate(static::DEFAULT_TEMPLATE);
        } else {
            throw new \Exception('Unable to load template');
        }

        return $this;
    }

    protected function getTemplatesFromString($theme)
    {
        $templates = [];

        $template = $this->twig->loadTemplate($theme);
        while ($template instanceof Template) {
            $templates[] = $template;
            $template = $template->getParent([]);
        }

        return $templates;
    }

    protected function cleanHTML($value)
    {
        // Handle image
        $value = preg_replace('#<img[^>]*title="([^"]*)"[^>]*?/>#isU', '\1', $value);

        // Clean indent
        $value = preg_replace('/>[\s\n\t\r]*</', '><', $value);

        // Clean HTML tags
        $value = strip_tags($value);

        // Convert Special Characters in HTML
        $value = html_entity_decode($value, ENT_QUOTES);

        // Remove whitespace
        $value = preg_replace('/\s\s+/', ' ', $value);

        // Fix space
        $value = preg_replace('/\s,/', ',', $value);

        // Trim
        $value = trim($value);

        return $value;
    }

    /**
     * set title.
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set file name.
     *
     * @param string $fileName
     *
     * @return self
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * get file name.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * set file extension.
     *
     * @param string $fileExtension
     *
     * @return self
     */
    public function setFileExtension($fileExtension)
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    /**
     * get file extension.
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }

    /**
     * get base name.
     *
     * @return string
     */
    public function getBaseName()
    {
        return $this->fileName . (isset($this->fileExtension) ? ".$this->fileExtension" : '');
    }

    /**
     * set response mime type.
     *
     * @param string $mimeType
     *
     * @return self
     */
    public function setMimeType($mimeType)
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    /**
     * get response mime type.
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * set response charset.
     *
     * @param string $charset
     *
     * @return self
     */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * get response charset.
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * set parameters.
     *
     * @param array $parameters
     *
     * @return self
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * get parameters.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * has parameter.
     *
     * @return mixed
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * add parameter.
     *
     * @param $name
     * @param $value
     *
     * @return \APY\DataGridBundle\Grid\Export\Export
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * get parameter.
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        if (!$this->hasParameter($name)) {
            throw new \InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * set role.
     *
     * @param mixed $role
     *
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }
}

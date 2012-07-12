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

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

abstract class Export implements ContainerAwareInterface
{
    const DEFAULT_TEMPLATE = 'APYDataGridBundle::blocks.html.twig';

    protected $title;

    protected $fileName;

    protected $fileExtension = null;

    protected $mimeType = 'application/octet-stream';

    protected $parameters = array();

    protected $container;

    protected $template;

    protected $templates;

    protected $twig;

    protected $grid;

    protected $params = array();

    protected $content;

    protected $charset;

    public function __construct($title, $fileName = 'export', $params = array(), $charset = 'UTF-8')
    {
        $this->title = $title;
        $this->fileName = $fileName;
        $this->params = $params;
        $this->charset = $charset;
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;

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
     * function call by the grid to fill the content of the export
     *
     * @param Grid $grid The grid
     */
    abstract public function computeData($grid);

    /**
     * gets the export Response
     *
     * @return Response
     */
    public function getResponse()
    {
        // Response
        if (function_exists('mb_strlen')) {
            $this->content = mb_convert_encoding($this->content, $this->charset, $this->container->getParameter('kernel.charset'));
            $filesize = mb_strlen($this->content, $this->container->getParameter('kernel.charset'));
        } else {
            $filesize = strlen($this->content);
            $this->charset = $this->container->getParameter('kernel.charset');
        }

        $headers = array(
            'Content-Description' => 'File Transfer',
            'Content-Type' => $this->getMimeType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $this->getBaseName()),
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => $filesize
        );

        $response = new Response($this->content, 200, $headers);
        $response->setCharset($this->charset);
        $response->expire();

        return $response;
    }

    /**
     * sets the Content of the export
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
     * gets the Content of the export
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get data form the grid
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
        $result = array();

        $this->grid = $grid;

        $this->twig = $this->container->get('twig');

        $this->template = $this->grid->getTemplate();

        if ($this->grid->isTitleSectionVisible()) {
            $result['titles'] = $this->getGridTitles();
        }

        $result['rows'] = $this->getGridRows();

        return $result;
    }

    protected function getRawGridData($grid)
    {
        $result = array();
        $this->grid = $grid;

        if ($this->grid->isTitleSectionVisible()) {
            $result['titles'] = $this->getRawGridTitles();
        }

        $result['rows'] = $this->getRawGridRows();

        return $result;
    }

    /**
     * Get data form the grid in a flat array
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

        $flatData = array();
        if (isset($data['titles'])) {
            $flatData[] = $data['titles'];
        }

        return array_merge($flatData, $data['rows']);
    }

    protected function getFlatRawGridData($grid)
    {
        $data = $this->getRawGridData($grid);

        $flatData = array();
        if (isset($data['titles'])) {
            $flatData[] = $data['titles'];
        }

        return array_merge($flatData, $data['rows']);
    }

    protected function getGridTitles()
    {
        $titlesHTML = $this->renderBlock('grid_titles', array('grid' => $this->grid));
        
        preg_match_all('#<th[^>]*?>(.*)?</th>#isU', $titlesHTML, $matches);

        if (empty($matches)) {
            preg_match_all('#<td[^>]*?>(.*)?</td>#isU', $titlesHTML, $matches);
        }

        if (empty($matches)) {
            new \Exception('Table header (th or td) tags not found.');
        }

        $titlesClean = array_map(array($this, 'cleanHTML'), $matches[0]);

        $i = 0;
        $titles = array();
        foreach ($this->grid->getColumns() as $column) {
            if ($column->isVisible(true)) {
                if (!isset($titlesClean[$i])) {
                    throw new \OutOfBoundsException('There are more more visible columns than titles found.');
                }
                $titles[$column->getId()] = $titlesClean[$i++];
            }
        }

        return $titles;
    }

    protected function getRawGridTitles()
    {
        $translator = $this->container->get('translator');

        $titles = array();
        foreach ($this->grid->getColumns() as $column) {
            if ($column->isVisible(true)) {
                $titles[] = utf8_decode($translator->trans($column->getTitle()));
            }
        }

        return $titles;
    }

    protected function getGridRows()
    {
        $rows = array();
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
        $rows = array();
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
        $value = $column->renderCell($row->getField($column->getId()), $row, $this->container->get('router'));

        if ($this->hasBlock($block = 'grid_'.$this->grid->getHash().'_column_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($block = 'grid_'.$this->grid->getHash().'_column_'.$column->getType().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getType().'_cell'))
        {
            return $this->renderBlock($block, array('column' => $column, 'value' => $value, 'row' => $row));
        }

        return $value;
    }

    /**
     * Has block
     *
     * @param $name string
     * @return boolean
     */
    protected function hasBlock($name)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render block
     *
     * @param $name string
     * @param $parameters string
     * @return string
     */
    protected function renderBlock($name, $parameters)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                return $template->renderBlock($name, array_merge($parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, 'ee'));
    }

    /**
     * Template Loader
     *
     * @return \Twig_TemplateInterface[]
     * @throws \Exception
     */
    protected function getTemplates()
    {
        $template = $this->grid->getTemplate();

        if (empty($this->templates)) {
            //get template name
            if (is_string($template)) {
                if (substr($template, 0, 8) === '__SELF__') {
                    $this->templates = $this->getTemplatesFromString(substr($template, 8));
                    $this->templates[] = $this->twig->loadTemplate(static::DEFAULT_TEMPLATE);
                } else {
                    $this->templates = $this->getTemplatesFromString($template);
                }
            } elseif ($this->template === null) {
                $this->templates[] = $this->twig->loadTemplate(static::DEFAULT_TEMPLATE);
            } else {
                throw new \Exception('Unable to load template');
            }
        }

        return $this->templates;
    }

    protected function getTemplatesFromString($theme)
    {
        $templates = array();

        $template = $this->twig->loadTemplate($theme);
        while ($template != null) {
            $templates[] = $template;
            $template = $template->getParent(array());
        }

        return $templates;
    }

    protected function cleanHTML($value)
    {
        // Clean indent
        $value = preg_replace('/>[\s\n\t\r]*</', '><', $value);

        // Clean HTML tags
        $value = strip_tags($value);

        // Convert Special Characters in HTML
        $value = html_entity_decode($value);
        
        $value = trim($value);

        return $value;
    }

    /**
     * set title
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
     * get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * set file name
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
     * get file name
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * set file extension
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
     * get file extension
     *
     * @return string
     */
    public function getFileExtension()
    {
        return $this->fileExtension;
    }



    /**
     * get base name
     *
     * @return string
     */
    public function getBaseName()
    {
        return $this->fileName.(isset($this->fileExtension) ? ".$this->fileExtension" : '');
    }

    /**
     * set response mime type
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
     * get response mime type
     *
     * @return string
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }

    /**
     * set response charset
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
     * get response charset
     *
     * @return string
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * set parameters
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
     * get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

        /**
     * has parameter
     *
     * @return mixed
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     * add parameter
     *
     * @param array $template
     *
     * @return self
     */
    public function addParameter($name, $value)
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * get parameter
     *
     * @return mixed
     */
    public function getParameter($name)
    {
        if (!hasParameter($name)) {
            throw new \InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    /**
     * set template
     *
     * @param string $template
     *
     * @return self
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * get template
     *
     * @return boolean
     */
    public function getTemplate()
    {
        return $this->template ?:$this::DEFAULT_TEMPLATE;
    }
}

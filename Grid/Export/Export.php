<?php

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\Column\ArrayColumn;
use APY\DataGridBundle\Grid\GridInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Template;

abstract class Export implements ExportInterface
{
    public const DEFAULT_TEMPLATE = 'APYDataGridBundle::blocks.html.twig';

    protected string $title;

    protected string $fileName;

    protected ?string $fileExtension = null;

    protected string $mimeType = 'application/octet-stream';

    protected array $parameters = [];

    protected ?array $templates = null;

    protected ?Environment $twig = null;

    protected ?GridInterface $grid = null;

    protected array $params = [];

    protected ?string $content = null;

    protected string $charset;

    protected ?string $role;

    protected ?RouterInterface $router = null;

    protected ?TranslatorInterface $translator = null;

    protected string $appCharset = 'UTF-8';

    /**
     * @param string      $title    Title of the export
     * @param string      $fileName FileName of the export
     * @param array       $params   Additionnal parameters for the export
     * @param string      $charset  Charset of the exported data
     * @param string|null $role     Security role
     */
    public function __construct(string $title, string $fileName = 'export', array $params = [], string $charset = 'UTF-8', ?string $role = null)
    {
        $this->setTitle($title)
            ->setFileName($fileName)
            ->setCharset($charset)
            ->setRole($role);
        $this->params = $params;
    }

    public function getResponse(): Response
    {
        if ($this->charset !== $this->appCharset && \function_exists('mb_strlen')) {
            $this->content = \mb_convert_encoding($this->content, $this->charset, $this->appCharset);
            $filesize = \mb_strlen($this->content, '8bit');
        } else {
            $filesize = \strlen($this->content);
            $this->charset = $this->appCharset;
        }

        $headers = [
            'Content-Description' => 'File Transfer',
            'Content-Type' => $this->getMimeType(),
            'Content-Disposition' => \sprintf('attachment; filename="%s"', $this->getBaseName()),
            'Content-Transfer-Encoding' => 'binary',
            'Cache-Control' => 'must-revalidate',
            'Pragma' => 'public',
            'Content-Length' => $filesize,
        ];

        $response = new Response($this->content, 200, $headers);
        $response->setCharset($this->charset);
        $response->expire();

        return $response;
    }

    public function setContent(string $content = ''): static
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Get data form the grid.
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
    protected function getGridData(GridInterface $grid): array
    {
        $result = [];

        $this->grid = $grid;

        if ($this->grid->isTitleSectionVisible()) {
            $result['titles'] = $this->getGridTitles();
        }

        $result['rows'] = $this->getGridRows();

        return $result;
    }

    protected function getRawGridData(GridInterface $grid): array
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
    protected function getFlatGridData(GridInterface $grid): array
    {
        $data = $this->getGridData($grid);

        $flatData = [];
        if (isset($data['titles'])) {
            $flatData[] = $data['titles'];
        }

        return \array_merge($flatData, $data['rows']);
    }

    protected function getFlatRawGridData(GridInterface $grid): array
    {
        $data = $this->getRawGridData($grid);

        $flatData = [];
        if (isset($data['titles'])) {
            $flatData[] = $data['titles'];
        }

        return \array_merge($flatData, $data['rows']);
    }

    protected function getGridTitles(): array
    {
        $titlesHTML = $this->renderBlock('grid_titles', ['grid' => $this->grid]);

        \preg_match_all('#<th[^>]*?>(.*)?</th>#isU', $titlesHTML, $matches);

        if (empty($matches)) {
            \preg_match_all('#<td[^>]*?>(.*)?</td>#isU', $titlesHTML, $matches);
        }

        if (empty($matches)) {
            throw new \RuntimeException('Table header (th or td) tags not found.');
        }

        $titlesClean = \array_map([$this, 'cleanHTML'], $matches[0]);

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

    protected function getRawGridTitles(): array
    {
        if (!$this->translator) {
            throw new \RuntimeException(\sprintf('Call setTranslator(TranslatorInterface $translator) for %s instance first', __CLASS__));
        }

        $titles = [];
        foreach ($this->grid->getColumns() as $column) {
            if ($column->isVisible(true)) {
                $titles[] = \utf8_decode($this->translator->trans(/* @Ignore */ $column->getTitle()));
            }
        }

        return $titles;
    }

    protected function getGridRows(): array
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

    protected function getRawGridRows(): array
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

    protected function getGridCell($column, $row): string
    {
        if (!$this->router) {
            throw new \RuntimeException(\sprintf('Call setRouter(RouterInterface $router) for %s instance first', __CLASS__));
        }
        $values = $row->getField($column->getId());

        // Cast a datetime won't work.
        if ($column instanceof ArrayColumn || !\is_array($values)) {
            $values = [$values];
        }

        $separator = $column->getSeparator();

        $block = null;
        $return = [];
        foreach ($values as $sourceValue) {
            $value = $column->renderCell($sourceValue, $row, $this->router);

            $id = $this->grid->getId();

            if (('' !== $id && (null !== $block
                        || $this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getRenderBlockId().'_cell')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getType().'_cell')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getParentType().'_cell')))
                || $this->hasBlock($block = 'grid_'.$id.'_column_id_'.$column->getRenderBlockId().'_cell')
                || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getType().'_cell')
                || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getParentType().'_cell')
                || $this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_cell')
                || $this->hasBlock($block = 'grid_column_'.$column->getType().'_cell')
                || $this->hasBlock($block = 'grid_column_'.$column->getParentType().'_cell')
                || $this->hasBlock($block = 'grid_column_id_'.$column->getRenderBlockId().'_cell')
                || $this->hasBlock($block = 'grid_column_type_'.$column->getType().'_cell')
                || $this->hasBlock($block = 'grid_column_type_'.$column->getParentType().'_cell')) {
                $html = $this->renderBlock($block, ['grid' => $this->grid, 'column' => $column, 'row' => $row, 'value' => $value, 'sourceValue' => $sourceValue]);
            } else {
                $html = $this->renderBlock('grid_column_cell', ['grid' => $this->grid, 'column' => $column, 'row' => $row, 'value' => $value, 'sourceValue' => $sourceValue]);
                $block = null;
            }

            // Fix blank separator. The <br /> will be removed by the HTML cleaner.
            if (\str_contains($separator, 'br')) {
                $html = \str_replace($separator, ',', $html);
            }

            $return[] = $html;
        }

        return \implode($separator, $return);
    }

    protected function hasBlock(string $name): bool
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name, [])) {
                return true;
            }
        }

        return false;
    }

    protected function renderBlock(string $name, array $parameters): string
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name, [])) {
                return $template->renderBlock($name, \array_merge($parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(\sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, 'ee'));
    }

    /**
     * @return Template[]
     */
    protected function getTemplates(): array
    {
        if (empty($this->templates)) {
            $this->setTemplate($this->grid->getTemplate());
        }

        return $this->templates;
    }

    public function setTemplate(Template|string $template): static
    {
        if (\is_string($template)) {
            if (\str_starts_with($template, '__SELF__')) {
                $this->templates = $this->getTemplatesFromString(\substr($template, 8));
                $this->templates[] = $this->twig->loadTemplate($this->twig->getTemplateClass(static::DEFAULT_TEMPLATE), static::DEFAULT_TEMPLATE);
            } else {
                $this->templates = $this->getTemplatesFromString($template);
            }
        } elseif (null === $this->templates) {
            $this->templates[] = $this->twig->loadTemplate($this->twig->getTemplateClass(static::DEFAULT_TEMPLATE), static::DEFAULT_TEMPLATE);
        } else {
            throw new \RuntimeException('Unable to load template');
        }

        return $this;
    }

    protected function getTemplatesFromString($theme): array
    {
        $templates = [];

        $template = $this->twig->loadTemplate($this->twig->getTemplateClass($theme), $theme);
        while ($template instanceof Template) {
            $templates[] = $template;
            $template = $template->getParent([]);
        }

        return $templates;
    }

    protected function cleanHTML(mixed $value): string
    {
        // Handle image
        $value = \preg_replace('#<img[^>]*title="([^"]*)"[^>]*?/>#isU', '\1', $value);

        // Clean indent
        $value = \preg_replace('/>[\s\n\t\r]*</', '><', $value);

        // Clean HTML tags
        $value = \strip_tags($value);

        // Convert Special Characters in HTML
        $value = \html_entity_decode($value, \ENT_QUOTES);

        // Remove whitespace
        $value = \preg_replace('/\s\s+/', ' ', $value);

        // Fix space
        $value = \preg_replace('/\s,/', ',', $value);

        return \trim($value);
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setFileName($fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileExtension(string $fileExtension): static
    {
        $this->fileExtension = $fileExtension;

        return $this;
    }

    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    public function getBaseName(): string
    {
        $ext = $this->getFileExtension();

        return $this->fileName.($ext ? ".$ext" : '');
    }

    public function setMimeType(string $mimeType): static
    {
        $this->mimeType = $mimeType;

        return $this;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function setCharset(string $charset): static
    {
        $this->charset = $charset;

        return $this;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function hasParameter(string $name): bool
    {
        return \array_key_exists($name, $this->parameters);
    }

    public function addParameter(string $name, mixed $value): static
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    public function getParameter(string $name): mixed
    {
        if (!$this->hasParameter($name)) {
            throw new \InvalidArgumentException(\sprintf('The parameter "%s" must be defined.', $name));
        }

        return $this->parameters[$name];
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setTwig(Environment $twig): static
    {
        $this->twig = $twig;

        return $this;
    }

    public function setRouter(?RouterInterface $router): self
    {
        $this->router = $router;

        return $this;
    }

    public function setTranslator(?TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }
}

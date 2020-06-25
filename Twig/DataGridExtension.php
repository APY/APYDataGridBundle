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

namespace APY\DataGridBundle\Twig;

use APY\DataGridBundle\Grid\Grid;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\Template;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DataGridExtension extends AbstractExtension implements GlobalsInterface
{
    const DEFAULT_TEMPLATE = 'APYDataGridBundle::blocks.html.twig';

    /**
     * @var Template[]
     */
    protected $templates = [];

    /**
     * @var string
     */
    protected $theme;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var array
     */
    protected $names;

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var string
     */
    protected $defaultTemplate;

    public function __construct(RouterInterface $router, $defaultTemplate)
    {
        $this->router = $router;
        $this->defaultTemplate = $defaultTemplate;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('data_grid_boolean_column_value_checker', [$this, 'booleanChecker']),
            new TwigFilter('data_grid_boolean_alt_value', [$this, 'booleanAltValueGetter']),
        ];
    }

    public function getGlobals(): array
    {
        return [
            'grid' => null,
            'column' => null,
            'row' => null,
            'value' => null,
            'submitOnChange' => null,
            'withjs' => true,
            'op' => 'eq',
        ];
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('grid', [$this, 'getGrid'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('grid_html', [$this, 'getGridHtml'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('grid_url', [$this, 'getGridUrl'], [
                'is_safe' => ['html'],
            ]),
            new TwigFunction('grid_filter', [$this, 'getGridFilter'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('grid_column_operator', [$this, 'getGridColumnOperator'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('grid_cell', [$this, 'getGridCell'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('grid_search', [$this, 'getGridSearch'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
            new TwigFunction('grid_pager', [$this, 'getGridPager']),
            new TwigFunction('grid_*', [$this, 'getGrid_'], [
                'needs_environment' => true,
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * @param unknown $grid
     * @param unknown $theme
     * @param string  $id
     * @param array   $params
     */
    public function initGrid($grid, $theme = null, $id = '', array $params = [])
    {
        $this->theme = $theme;
        $this->templates = [];

        $this->names[$grid->getHash()] = ($id == '') ? $grid->getId() : $id;
        $this->params = $params;
    }

    /**
     * Render grid block.
     *
     * @param Environment                   $environment
     * @param \APY\DataGridBundle\Grid\Grid $grid
     * @param string                        $theme
     * @param string                        $id
     * @param array                         $params
     * @param bool                          $withjs
     *
     * @return string
     */
    public function getGrid(Environment $environment, $grid, $theme = null, $id = '', array $params = [], $withjs = true)
    {
        $this->initGrid($grid, $theme, $id, $params);

        // For export
        $grid->setTemplate($theme);

        return $this->renderBlock($environment, 'grid', ['grid' => $grid, 'withjs' => $withjs]);
    }

    /**
     * Render grid block (html only).
     *
     * @param Environment                   $environment
     * @param \APY\DataGridBundle\Grid\Grid $grid
     * @param string                        $theme
     * @param string                        $id
     * @param array                         $params
     *
     * @return string
     */
    public function getGridHtml(Environment $environment, $grid, $theme = null, $id = '', array $params = [])
    {
        return $this->getGrid($environment, $grid, $theme, $id, $params, false);
    }

    /**
     * @param Environment $environment
     * @param string      $name
     * @param mixed       $grid
     *
     * @return string
     */
    public function getGrid_(Environment $environment, $name, $grid)
    {
        return $this->renderBlock($environment, 'grid_' . $name, ['grid' => $grid]);
    }

    public function getGridPager()
    {
        return '';
    }

    /**
     * Cell Drawing override.
     *
     * @param Environment                            $environment
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Row           $row
     * @param \APY\DataGridBundle\Grid\Grid          $grid
     *
     * @return string
     */
    public function getGridCell(Environment $environment, $column, $row, $grid)
    {
        $value = $column->renderCell($row->getField($column->getId()), $row, $this->router);

        $id = $this->names[$grid->getHash()];

        if (($id != '' && ($this->hasBlock($environment, $block = 'grid_' . $id . '_column_' . $column->getRenderBlockId() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_' . $column->getType() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_' . $column->getParentType() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_id_' . $column->getRenderBlockId() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_type_' . $column->getType() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_type_' . $column->getParentType() . '_cell')))
            || $this->hasBlock($environment, $block = 'grid_column_' . $column->getRenderBlockId() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_column_' . $column->getType() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_column_' . $column->getParentType() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_column_id_' . $column->getRenderBlockId() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_column_type_' . $column->getType() . '_cell')
            || $this->hasBlock($environment, $block = 'grid_column_type_' . $column->getParentType() . '_cell')
        ) {
            return $this->renderBlock($environment, $block, ['grid' => $grid, 'column' => $column, 'row' => $row, 'value' => $value]);
        }

        return $this->renderBlock($environment, 'grid_column_cell', ['grid' => $grid, 'column' => $column, 'row' => $row, 'value' => $value]);
    }

    /**
     * Filter Drawing override.
     *
     * @param Environment                            $environment
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Grid          $grid
     *
     * @return string
     */
    public function getGridFilter(Environment $environment, $column, $grid, $submitOnChange = true)
    {
        $id = $this->names[$grid->getHash()];

        if (($id != '' && ($this->hasBlock($environment, $block = 'grid_' . $id . '_column_' . $column->getRenderBlockId() . '_filter')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_id_' . $column->getRenderBlockId() . '_filter')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_type_' . $column->getType() . '_filter')
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_type_' . $column->getParentType() . '_filter'))
            || $this->hasBlock($environment, $block = 'grid_' . $id . '_column_filter_type_' . $column->getFilterType()))
            || $this->hasBlock($environment, $block = 'grid_column_' . $column->getRenderBlockId() . '_filter')
            || $this->hasBlock($environment, $block = 'grid_column_id_' . $column->getRenderBlockId() . '_filter')
            || $this->hasBlock($environment, $block = 'grid_column_type_' . $column->getType() . '_filter')
            || $this->hasBlock($environment, $block = 'grid_column_type_' . $column->getParentType() . '_filter')
            || $this->hasBlock($environment, $block = 'grid_column_filter_type_' . $column->getFilterType())
        ) {
            return $this->renderBlock($environment, $block, ['grid' => $grid, 'column' => $column, 'submitOnChange' => $submitOnChange && $column->isFilterSubmitOnChange()]);
        }

        return '';
    }

    /**
     * Column Operator Drawing override.
     *
     * @param Environment                            $environment
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Grid          $grid
     * @param bool                                   $submitOnChange
     *
     * @return string
     */
    public function getGridColumnOperator(Environment $environment, $column, $grid, $operator, $submitOnChange = true)
    {
        return $this->renderBlock($environment, 'grid_column_operator', ['grid' => $grid, 'column' => $column, 'submitOnChange' => $submitOnChange, 'op' => $operator]);
    }

    /**
     * @param string                                 $section
     * @param \APY\DataGridBundle\Grid\Grid          $grid
     * @param \APY\DataGridBundle\Grid\Column\Column $param
     *
     * @return string
     */
    public function getGridUrl($section, $grid, $param = null)
    {
        $prefix = $grid->getRouteUrl() . (strpos($grid->getRouteUrl(), '?') ? '&' : '?') . $grid->getHash() . '[';

        switch ($section) {
            case 'order':
                if ($param->isSorted()) {
                    return $prefix . Grid::REQUEST_QUERY_ORDER . ']=' . $param->getId() . '|' . ($param->getOrder() == 'asc' ? 'desc' : 'asc');
                } else {
                    return $prefix . Grid::REQUEST_QUERY_ORDER . ']=' . $param->getId() . '|asc';
                }
            case 'page':
                return $prefix . Grid::REQUEST_QUERY_PAGE . ']=' . $param;
            case 'limit':
                return $prefix . Grid::REQUEST_QUERY_LIMIT . ']=';
            case 'reset':
                return $prefix . Grid::REQUEST_QUERY_RESET . ']=';
            case 'export':
                return $prefix . Grid::REQUEST_QUERY_EXPORT . ']=' . $param;
        }
    }

    /**
     * @param Environment $environment
     * @param mixed       $grid
     * @param mixed       $theme
     * @param string      $id
     * @param array       $params
     *
     * @return string
     */
    public function getGridSearch(Environment $environment, $grid, $theme = null, $id = '', array $params = [])
    {
        $this->initGrid($grid, $theme, $id, $params);

        return $this->renderBlock($environment, 'grid_search', ['grid' => $grid]);
    }

    /**
     * @param $value
     *
     * @return bool
     */
    public function booleanChecker($value)
    {
        if (is_string($value)) {
            $value = strtolower($value);
            if ($value == 'true' || $value == 'si') {
                return 'true';
            }

            return 'false';
        }

        return $value ? 'true' : 'false';
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function booleanAltValueGetter($value)
    {
        if (is_numeric($value)) {
            return $value;
        }

        $booleanCheckerResult = $this->booleanChecker($value);

        return $booleanCheckerResult == 'true' ? 'sì' : 'no';
    }

    /**
     * Render block.
     *
     * @param Environment $environment
     * @param string      $name
     * @param array       $parameters
     *
     * @throws \InvalidArgumentException If the block could not be found
     *
     * @return string
     */
    protected function renderBlock(Environment $environment, $name, $parameters)
    {
        foreach ($this->getTemplates($environment) as $template) {
            if ($template->hasBlock($name, [])) {
                return $template->renderBlock($name, array_merge($environment->getGlobals(), $parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme));
    }

    /**
     * Has block.
     *
     * @param Environment $environment
     * @param mixed       $name
     *
     * @return bool
     */
    protected function hasBlock(Environment $environment, $name)
    {
        foreach ($this->getTemplates($environment) as $template) {
            /** @var Template $template */
            if ($template->hasBlock($name, [])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Template Loader.
     *
     * @param Environment $environment
     *
     * @throws \Exception
     *
     * @return Template[]
     */
    protected function getTemplates(Environment $environment)
    {
        if (empty($this->templates)) {
            if ($this->theme instanceof Template) {
                $this->templates[] = $this->theme;
                $this->templates[] = $environment->loadTemplate($this->defaultTemplate);
            } elseif (is_string($this->theme)) {
                $this->templates = $this->getTemplatesFromString($environment, $this->theme);
            } elseif (null === $this->theme) {
                $this->templates = $this->getTemplatesFromString($environment, $this->defaultTemplate);
            } else {
                throw new \Exception('Unable to load template');
            }
        }

        return $this->templates;
    }

    /**
     * @param Environment $environment
     * @param mixed       $theme
     *
     * @return Template[]
     */
    protected function getTemplatesFromString(Environment $environment, $theme)
    {
        $this->templates = [];

        $template = $environment->loadTemplate($theme);
        while ($template instanceof Template) {
            $this->templates[] = $template;
            $template = $template->getParent([]);
        }

        return $this->templates;
    }
}

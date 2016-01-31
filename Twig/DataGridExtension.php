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
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\NullAdapter;
use Symfony\Component\Routing\RouterInterface;

class DataGridExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    const DEFAULT_TEMPLATE = 'APYDataGridBundle::blocks.html.twig';

    /**
     * @var \Twig_Environment
     */
    protected $environment;

    /**
     * @var \Twig_TemplateInterface[]
     */
    protected $templates = array();

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
    protected $params = array();

    /**
     * @var array
     */
    protected $pagerFantaDefs;

    /**
     * @var string
     */
    protected $defaultTemplate;

    /**
     * @param RouterInterface $router
     * @param string $defaultTemplate
     */
    public function __construct($router, $defaultTemplate)
    {
        $this->router = $router;
        $this->defaultTemplate = $defaultTemplate;
    }

    public function setPagerFanta(array $def)
    {
        $this->pagerFantaDefs=$def;
    }

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'grid' => null,
            'column' => null,
            'row' => null,
            'value' => null,
            'submitOnChange' => null,
            'withjs' => true,
            'pagerfanta' => false,
            'op' => 'eq'
        );
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('grid', array($this, 'getGrid'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_html', array($this, 'getGridHtml'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_url', array($this, 'getGridUrl'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_filter', array($this, 'getGridFilter'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_column_operator', array($this, 'getGridColumnOperator'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_cell', array($this, 'getGridCell'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_search', array($this, 'getGridSearch'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_pager', array($this, 'getGridPager'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_pagerfanta', array($this, 'getPagerfanta'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('grid_*', array($this, 'getGrid_'), array('is_safe' => array('html')))
        );
    }

    public function initGrid($grid, $theme = null, $id = '', array $params = array())
    {
        $this->theme = $theme;
        $this->templates = array();

        $this->names[$grid->getHash()] = ($id == '') ? $grid->getId() : $id;
        $this->params = $params;
    }

    /**
     * Render grid block
     *
     * @param \APY\DataGridBundle\Grid\Grid $grid
     * @param string $theme
     * @param string $id
     *
     * @return string
     */
    public function getGrid($grid, $theme = null, $id = '', array $params = array(), $withjs = true)
    {
        $this->initGrid($grid, $theme, $id, $params);

        // For export
        $grid->setTemplate($theme);

        return $this->renderBlock('grid', array('grid' => $grid, 'withjs' => $withjs));
    }

    /**
     * Render grid block (html only)
     *
     * @param \APY\DataGridBundle\Grid\Grid $grid
     * @param string $theme
     * @param string $id
     *
     * @return string
     */
    public function getGridHtml($grid, $theme = null, $id = '', array $params = array())
    {
        return $this->getGrid($grid, $theme, $id, $params, false);
    }

    public function getGrid_($name, $grid)
    {
        return $this->renderBlock('grid_' . $name, array('grid' => $grid));
    }

    public function getGridPager($grid)
    {
        return $this->renderBlock('grid_pager', array('grid' => $grid, 'pagerfanta' => $this->pagerFantaDefs['enable']));
    }

    /**
     * Cell Drawing override
     *
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Row $row
     * @param \APY\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridCell($column, $row, $grid)
    {
        $value = $column->renderCell($row->getField($column->getId()), $row, $this->router);

        $id = $this->names[$grid->getHash()];

        if (($id != '' && ($this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getRenderBlockId().'_cell')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getType().'_cell')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getParentType().'_cell')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_id_'.$column->getRenderBlockId().'_cell')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getType().'_cell')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getParentType().'_cell')))
         || $this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getType().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getParentType().'_cell')
         || $this->hasBlock($block = 'grid_column_id_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($block = 'grid_column_type_'.$column->getType().'_cell')
         || $this->hasBlock($block = 'grid_column_type_'.$column->getParentType().'_cell')
        ) {
            return $this->renderBlock($block, array('grid' => $grid, 'column' => $column, 'row' => $row, 'value' => $value));
        }

        return $this->renderBlock('grid_column_cell', array('grid' => $grid, 'column' => $column, 'row' => $row, 'value' => $value));
    }

    /**
     * Filter Drawing override
     *
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridFilter($column, $grid, $submitOnChange = true)
    {
        $id = $this->names[$grid->getHash()];

        if (($id != '' && ($this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getRenderBlockId().'_filter')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_id_'.$column->getRenderBlockId().'_filter')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getType().'_filter')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getParentType().'_filter'))
                        || $this->hasBlock($block = 'grid_'.$id.'_column_filter_type_'.$column->getFilterType()))
         || $this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_filter')
         || $this->hasBlock($block = 'grid_column_id_'.$column->getRenderBlockId().'_filter')
         || $this->hasBlock($block = 'grid_column_type_'.$column->getType().'_filter')
         || $this->hasBlock($block = 'grid_column_type_'.$column->getParentType().'_filter')
         || $this->hasBlock($block = 'grid_column_filter_type_'.$column->getFilterType())
         ) {
            return $this->renderBlock($block, array('grid' => $grid, 'column' => $column, 'submitOnChange' => $submitOnChange && $column->isFilterSubmitOnChange()));
        }

        return '';
    }

    /**
     * Column Operator Drawing override
     *
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridColumnOperator($column, $grid, $operator, $submitOnChange = true)
    {
        return $this->renderBlock('grid_column_operator', array('grid' => $grid, 'column' => $column, 'submitOnChange' => $submitOnChange, 'op' => $operator));
    }

    /**
     * @param string $section
     * @param \APY\DataGridBundle\Grid\Grid $grid
     * @param \APY\DataGridBundle\Grid\Column\Column $param
     * @return string
     */
    public function getGridUrl($section, $grid, $param = null)
    {
        preg_match("/(.*)(#\w*$)/", $grid->getRouteUrl(), $routeParts);
        if(isset($routeParts[2])){
            $prefix = $routeParts[1].(strpos($routeParts[1], '?') ? '&' : '?').$grid->getHash().'[';
            $suffix = $routeParts[2];
        } else {
            $prefix = $grid->getRouteUrl().(strpos($grid->getRouteUrl(), '?') ? '&' : '?').$grid->getHash().'[';
            $suffix = null;
        }

        switch ($section) {
            case 'order':
                if ($param->isSorted()) {
                    return $prefix.Grid::REQUEST_QUERY_ORDER.']='.$param->getId().'|'.($param->getOrder() == 'asc' ? 'desc' : 'asc').$suffix;
                } else {
                    return $prefix.Grid::REQUEST_QUERY_ORDER.']='.$param->getId().'|asc'.$suffix;
                }
            case 'page':
                return $prefix.Grid::REQUEST_QUERY_PAGE.']='.$param.$suffix;
            case 'limit':
                return $prefix.Grid::REQUEST_QUERY_LIMIT.']='.$param.$suffix;
            case 'reset':
                return $prefix.Grid::REQUEST_QUERY_RESET.']='.$suffix;
            case 'export':
                return $prefix.Grid::REQUEST_QUERY_EXPORT.']='.$param.$suffix;
        }
    }

    public function getGridSearch($grid, $theme = null, $id = '', array $params = array())
    {
        $this->initGrid($grid, $theme, $id, $params);

        return $this->renderBlock('grid_search', array('grid' => $grid));
    }

    public function getPagerfanta($grid)
    {
        $adapter = new NullAdapter($grid->getTotalCount());

        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($grid->getLimit());
        $pagerfanta->setCurrentPage($grid->getPage() + 1);

        $url = $this->getGridUrl('page', $grid, '');
        $routeGenerator = function ($page) use ($url) {
            return sprintf('%s%d', $url, $page - 1);
        };

        $view = new $this->pagerFantaDefs['view_class'];
        $html = $view->render($pagerfanta, $routeGenerator, $this->pagerFantaDefs['options']);

        return $html;
    }

    /**
     * Render block
     *
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     *
     * @throws \InvalidArgumentException If the block could not be found
     */
    protected function renderBlock($name, $parameters)
    {
        foreach ($this->getTemplates() as $template) {
            if ($template->hasBlock($name)) {
                return $template->renderBlock($name, array_merge($this->environment->getGlobals(), $parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme));
    }

    /**
     * Has block
     *
     * @param $name string
     *
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
     * Template Loader
     *
     * @return \Twig_Template[]
     *
     * @throws \Exception
     */
    protected function getTemplates()
    {
        if (empty($this->templates)) {
            if ($this->theme instanceof \Twig_Template) {
                $this->templates[] = $this->theme;
                $this->templates[] = $this->environment->loadTemplate($this->defaultTemplate);
            } elseif (is_string($this->theme)) {
                $this->templates = $this->getTemplatesFromString($this->theme);
            } elseif ($this->theme === null) {
                $this->templates = $this->getTemplatesFromString($this->defaultTemplate);
            } else {
                throw new \Exception('Unable to load template');
            }
        }

        return $this->templates;
    }

    protected function getTemplatesFromString($theme)
    {
        $this->templates = array();

        $template = $this->environment->loadTemplate($theme);
        while ($template != null) {
            $this->templates[] = $template;
            $template = $template->getParent(array());
        }

        return $this->templates;
    }

    public function getName()
    {
        return 'datagrid_twig_extension';
    }
}

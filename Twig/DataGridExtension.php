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

class DataGridExtension extends \Twig_Extension
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
    * @var \Symfony\Component\Routing\Router
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
     * 
     * @var array
     */
    protected $pagerFantaDefs;
    
    public function __construct($router)
    {
        $this->router = $router;
    }
    
    public function setPagerFanta(array $def)
    {
        $this->pagerFantaDefs=$def;
    }
    
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;

        // Avoids the exception "Variable does not exist" with the _self template
        $globals = $this->environment->getGlobals();

        if (!isset($globals['grid'])) {
            $this->environment->addGlobal('grid', null);
        }

        if (!isset($globals['column'])) {
            $this->environment->addGlobal('column', null);
        }

        if (!isset($globals['row'])) {
            $this->environment->addGlobal('row', null);
        }

        if (!isset($globals['value'])) {
            $this->environment->addGlobal('value', null);
        }

        if (!isset($globals['submitOnChange'])) {
            $this->environment->addGlobal('submitOnChange', null);
        }

        if (!isset($globals['withjs'])) {
            $this->environment->addGlobal('withjs', true);
        }

        if (!isset($globals['pagerfanta'])) {
            $this->environment->addGlobal('pagerfanta', false);
        }

        if (!isset($globals['op'])) {
            $this->environment->addGlobal('op', 'eq');
        }
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'grid'              => new \Twig_Function_Method($this, 'getGrid', array('is_safe' => array('html'))),
            'grid_html'         => new \Twig_Function_Method($this, 'getGridHtml', array('is_safe' => array('html'))),
            'grid_url'          => new \Twig_Function_Method($this, 'getGridUrl', array('is_safe' => array('html'))),
            'grid_filter'       => new \Twig_Function_Method($this, 'getGridFilter', array('is_safe' => array('html'))),
            'grid_column_operator' => new \Twig_Function_Method($this, 'getGridColumnOperator', array('is_safe' => array('html'))),
            'grid_cell'         => new \Twig_Function_Method($this, 'getGridCell', array('is_safe' => array('html'))),
            'grid_search'       => new \Twig_Function_Method($this, 'getGridSearch', array('is_safe' => array('html'))),
            'grid_pager'        => new \Twig_Function_Method($this, 'getGridPager', array('is_safe' => array('html'))),
            'grid_pagerfanta'   => new \Twig_Function_Method($this, 'getPagerfanta', array('is_safe' => array('html'))),
            // Other methods with only the grid as input and output argument (Twig >= 1.5.0)
            'grid_*'            => new \Twig_Function_Method($this, 'getGrid_', array('is_safe' => array('html')))
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
                        || $this->hasBlock($block = 'grid_'.$id.'_column_'.$column->getParentType().'_cell')))
         || $this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getType().'_cell')
         || $this->hasBlock($block = 'grid_column_'.$column->getParentType().'_cell'))
        {
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
                        || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getType().'_filter')
                        || $this->hasBlock($block = 'grid_'.$id.'_column_type_'.$column->getParentType().'_filter'))
                        || $this->hasBlock($block = 'grid_'.$id.'_column_filter_type_'.$column->getFilterType()))
         || $this->hasBlock($block = 'grid_column_'.$column->getRenderBlockId().'_filter')
         || $this->hasBlock($block = 'grid_column_type_'.$column->getType().'_filter')
         || $this->hasBlock($block = 'grid_column_type_'.$column->getParentType().'_filter')
         || $this->hasBlock($block = 'grid_column_filter_type_'.$column->getFilterType()))
        {
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
        $prefix = $grid->getRouteUrl().(strpos($grid->getRouteUrl(), '?') ? '&' : '?').$grid->getHash().'[';

        switch ($section) {
            case 'order':
                if ($param->isSorted()) {
                    return $prefix.Grid::REQUEST_QUERY_ORDER.']='.$param->getId().'|'.($param->getOrder() == 'asc' ? 'desc' : 'asc');
                } else {
                    return $prefix.Grid::REQUEST_QUERY_ORDER.']='.$param->getId().'|asc';
                }
            case 'page':
                return $prefix.Grid::REQUEST_QUERY_PAGE.']='.$param;
            case 'limit':
                return $prefix.Grid::REQUEST_QUERY_LIMIT.']=';
            case 'reset':
                return $prefix.Grid::REQUEST_QUERY_RESET.']=';
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
        $routeGenerator = function($page) use ($url) {
            return sprintf('%s%d', $url, $page - 1);
        };

        $view = new $this->pagerFantaDefs['view_class'];
        $html = $view->render($pagerfanta, $routeGenerator, $this->pagerFantaDefs['options']);

        return $html;
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
                return $template->renderBlock($name, array_merge($this->environment->getGlobals(), $parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme));
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
     * Template Loader
     *
     * @return \Twig_TemplateInterface[]
     * @throws \Exception
     */
    protected function getTemplates()
    {
        if (empty($this->templates)) {
            if ($this->theme instanceof \Twig_Template) {
                $this->templates[] = $this->theme;
                $this->templates[] = $this->environment->loadTemplate(static::DEFAULT_TEMPLATE);
            } elseif (is_string($this->theme)) {
                $this->templates = $this->getTemplatesFromString($this->theme);
            } elseif ($this->theme === null) {
                $this->templates[] = $this->environment->loadTemplate(static::DEFAULT_TEMPLATE);
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

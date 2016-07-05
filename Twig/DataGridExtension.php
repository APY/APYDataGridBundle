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

/**
 * DataGrid Twig Extension
 * 
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 * 
 * Updated by Nicolas Claverie <info@artscore-studio.fr>
 *
 */
class DataGridExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    const DEFAULT_TEMPLATE = 'APYDataGridBundle::blocks.html.twig';

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

    /**
     * @param array $def
     */
    public function setPagerFanta(array $def)
    {
        $this->pagerFantaDefs=$def;
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
            new \Twig_SimpleFunction('grid', array($this, 'getGrid'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_html', array($this, 'getGridHtml'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_url', array($this, 'getGridUrl'), array(
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_filter', array($this, 'getGridFilter'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_column_operator', array($this, 'getGridColumnOperator'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_cell', array($this, 'getGridCell'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_search', array($this, 'getGridSearch'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_pager', array($this, 'getGridPager'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_pagerfanta', array($this, 'getPagerfanta'), array(
                'is_safe' => array('html')
            )),
            new \Twig_SimpleFunction('grid_*', array($this, 'getGrid_'), array(
                'needs_environment' => true,
                'is_safe' => array('html')
            ))
        );
    }

    /**
     * @param unknown $grid
     * @param unknown $theme
     * @param string $id
     * @param array $params
     */
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
     * @param \Twig_Environment $environment
     * @param \APY\DataGridBundle\Grid\Grid $grid
     * @param string $theme
     * @param string $id
     *
     * @return string
     */
    public function getGrid(\Twig_Environment $environment, $grid, $theme = null, $id = '', array $params = array(), $withjs = true)
    {
        $this->initGrid($grid, $theme, $id, $params);

        // For export
        $grid->setTemplate($theme);

        return $this->renderBlock($environment, 'grid', array('grid' => $grid, 'withjs' => $withjs));
    }

    /**
     * Render grid block (html only)
     *
     * @param \Twig_Environment $environment
     * @param \APY\DataGridBundle\Grid\Grid $grid
     * @param string $theme
     * @param string $id
     *
     * @return string
     */
    public function getGridHtml(\Twig_Environment $environment, $grid, $theme = null, $id = '', array $params = array())
    {
        return $this->getGrid($environment, $grid, $theme, $id, $params, false);
    }

    /**
     * @param \Twig_Environment $environment
     * @param string $name
     * @param unknown $grid
     */
    public function getGrid_(\Twig_Environment $environment, $name, $grid)
    {
        return $this->renderBlock($environment, 'grid_' . $name, array('grid' => $grid));
    }

    /**
     * @param \Twig_Environment $environment
     * @param unknown $grid
     * @return string
     */
    public function getGridPager(\Twig_Environment $environment, $grid)
    {
        return $this->renderBlock($environment, 'grid_pager', array('grid' => $grid, 'pagerfanta' => $this->pagerFantaDefs['enable']));
    }

    /**
     * Cell Drawing override
     *
     * @param \Twig_Environment $environment
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Row $row
     * @param \APY\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridCell(\Twig_Environment $environment, $column, $row, $grid)
    {
        $value = $column->renderCell($row->getField($column->getId()), $row, $this->router);

        $id = $this->names[$grid->getHash()];

        if (($id != '' && ($this->hasBlock($environment, $block = 'grid_'.$id.'_column_'.$column->getRenderBlockId().'_cell')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_'.$column->getType().'_cell')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_'.$column->getParentType().'_cell')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_id_'.$column->getRenderBlockId().'_cell')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_type_'.$column->getType().'_cell')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_type_'.$column->getParentType().'_cell')))
         || $this->hasBlock($environment, $block = 'grid_column_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($environment, $block = 'grid_column_'.$column->getType().'_cell')
         || $this->hasBlock($environment, $block = 'grid_column_'.$column->getParentType().'_cell')
         || $this->hasBlock($environment, $block = 'grid_column_id_'.$column->getRenderBlockId().'_cell')
         || $this->hasBlock($environment, $block = 'grid_column_type_'.$column->getType().'_cell')
         || $this->hasBlock($environment, $block = 'grid_column_type_'.$column->getParentType().'_cell')
        ) {
            return $this->renderBlock($environment, $block, array('grid' => $grid, 'column' => $column, 'row' => $row, 'value' => $value));
        }

        return $this->renderBlock($environment, 'grid_column_cell', array('grid' => $grid, 'column' => $column, 'row' => $row, 'value' => $value));
    }

    /**
     * Filter Drawing override
     *
     * @param \Twig_Environment $environment
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridFilter(\Twig_Environment $environment, $column, $grid, $submitOnChange = true)
    {
        $id = $this->names[$grid->getHash()];

        if (($id != '' && ($this->hasBlock($environment, $block = 'grid_'.$id.'_column_'.$column->getRenderBlockId().'_filter')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_id_'.$column->getRenderBlockId().'_filter')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_type_'.$column->getType().'_filter')
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_type_'.$column->getParentType().'_filter'))
                        || $this->hasBlock($environment, $block = 'grid_'.$id.'_column_filter_type_'.$column->getFilterType()))
         || $this->hasBlock($environment, $block = 'grid_column_'.$column->getRenderBlockId().'_filter')
         || $this->hasBlock($environment, $block = 'grid_column_id_'.$column->getRenderBlockId().'_filter')
         || $this->hasBlock($environment, $block = 'grid_column_type_'.$column->getType().'_filter')
         || $this->hasBlock($environment, $block = 'grid_column_type_'.$column->getParentType().'_filter')
         || $this->hasBlock($environment, $block = 'grid_column_filter_type_'.$column->getFilterType())
         ) {
            return $this->renderBlock($environment, $block, array('grid' => $grid, 'column' => $column, 'submitOnChange' => $submitOnChange && $column->isFilterSubmitOnChange()));
        }

        return '';
    }

    /**
     * Column Operator Drawing override
     *
     * @param \Twig_Environment $environment
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @param \APY\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridColumnOperator(\Twig_Environment $environment, $column, $grid, $operator, $submitOnChange = true)
    {
        return $this->renderBlock($environment, 'grid_column_operator', array('grid' => $grid, 'column' => $column, 'submitOnChange' => $submitOnChange, 'op' => $operator));
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
            case 'export':
                return $prefix.Grid::REQUEST_QUERY_EXPORT.']='.$param;
        }
    }

    /**
     * @param \Twig_Environment $environment
     * @param unknown $grid
     * @param unknown $theme
     * @param string $id
     * @param array $params
     * @return string
     */
    public function getGridSearch(\Twig_Environment $environment, $grid, $theme = null, $id = '', array $params = array())
    {
        $this->initGrid($grid, $theme, $id, $params);

        return $this->renderBlock($environment, 'grid_search', array('grid' => $grid));
    }

    /**
     * @param unknown $grid
     */
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
     * @param \Twig_Environment $environment
     * @param string $name
     * @param array  $parameters
     *
     * @return string
     *
     * @throws \InvalidArgumentException If the block could not be found
     */
    protected function renderBlock(\Twig_Environment $environment, $name, $parameters)
    {
        foreach ($this->getTemplates($environment) as $template) {
            if ($template->hasBlock($name)) {
                return $template->renderBlock($name, array_merge($environment->getGlobals(), $parameters, $this->params));
            }
        }

        throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme));
    }

    /**
     * Has block
     *
     * @param \Twig_Environment $environment
     * @param $name string
     *
     * @return boolean
     */
    protected function hasBlock(\Twig_Environment $environment, $name)
    {
        foreach ($this->getTemplates($environment) as $template) {
            if ($template->hasBlock($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Template Loader
     *
     * @param \Twig_Environment $environment
     * @return \Twig_Template[]
     *
     * @throws \Exception
     */
    protected function getTemplates(\Twig_Environment $environment)
    {
        if (empty($this->templates)) {
            if ($this->theme instanceof \Twig_Template) {
                $this->templates[] = $this->theme;
                $this->templates[] = $environment->loadTemplate($this->defaultTemplate);
            } elseif (is_string($this->theme)) {
                $this->templates = $this->getTemplatesFromString($environment, $this->theme);
            } elseif ($this->theme === null) {
                $this->templates = $this->getTemplatesFromString($environment, $this->defaultTemplate);
            } else {
                throw new \Exception('Unable to load template');
            }
        }

        return $this->templates;
    }

    /**
     * @param \Twig_Environment $environment
     * @param unknown $theme
     */
    protected function getTemplatesFromString(\Twig_Environment $environment, $theme)
    {
        $this->templates = array();

        $template = $environment->loadTemplate($theme);
        while ($template != null) {
            $this->templates[] = $template;
            $template = $template->getParent(array());
        }

        return $this->templates;
    }

    /**
     * {@inheritDoc}
     * @see Twig_ExtensionInterface::getName()
     */
    public function getName()
    {
        return 'datagrid_twig_extension';
    }
}

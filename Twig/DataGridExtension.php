<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Twig;

/**
 * @todo use {% grid_theme grid '' %} instead of second argument in grid function
 */
class DataGridExtension extends \Twig_Extension {

    /**
     * @var \Twig_Environment
     */
    protected $environment;
    /**
     * @var \Twig_Template
     */
    protected $template;
    protected $theme;
    /**
    * @var \Symfony\Component\Routing\Router
    */
    protected $router;

    public function __construct($router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
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
            'grid_titles'       => new \Twig_Function_Method($this, 'getGridTitles', array('is_safe' => array('html'))),
            'grid_filters'      => new \Twig_Function_Method($this, 'getGridFilters', array('is_safe' => array('html'))),
            'grid_rows'         => new \Twig_Function_Method($this, 'getGridItems', array('is_safe' => array('html'))),
            'grid_pager'        => new \Twig_Function_Method($this, 'getGridPager', array('is_safe' => array('html'))),
            'grid_actions'      => new \Twig_Function_Method($this, 'getGridActions', array('is_safe' => array('html'))),
            'grid_url'          => new \Twig_Function_Method($this, 'getGridUrl'),
            'grid_filter'       => new \Twig_Function_Method($this, 'getGridFilter'),
            'grid_cell'         => new \Twig_Function_Method($this, 'getGridCell', array('is_safe' => array('html'))),
        );
    }

    /**
     * Render grid block.
     *
     * @param $grid
     * @param $theme
     * @return string
     */
    public function getGrid($grid, $theme = null)
    {
        $this->theme = $theme;
        return $this->renderBlock('grid', array('grid' => $grid));
    }

    public function getGridTitles($grid)
    {
        return $this->renderBlock('grid_titles', array('grid' => $grid));
    }

    public function getGridFilters($grid)
    {
        return $this->renderBlock('grid_filters', array('grid' => $grid));
    }

    public function getGridItems($grid)
    {
        return $this->renderBlock('grid_rows', array('grid' => $grid));
    }

    public function getGridPager($grid)
    {
        return $this->renderBlock('grid_pager', array('grid' => $grid));
    }

    public function getGridActions($grid)
    {
        return $this->renderBlock('grid_actions', array('grid' => $grid));
    }

    /**
     * Cell Drawing override
     *
     * @param \Sorien\DataGridBundle\Grid\Column\Column $column
     * @param \Sorien\DataGridBundle\Grid\Row $row
     *
     * @return string
     */
    public function getGridCell($column, $row)
    {
        $value = $column->renderCell($row->getField($column->getId()), $row, $this->router);
        $block = 'grid_column_'.$column->getId().'_cell';

        return $this->hasBlock($block) ? $this->renderBlock($block, array('column' => $column, 'value' => $value, 'row' => $row)) : $value;
    }

    /**
     * Filter Drawing override
     *
     * @param \Sorien\DataGridBundle\Grid\Column\Column $column
     * @param \Sorien\DataGridBundle\Grid\Grid $grid
     *
     * @return string
     */
    public function getGridFilter($column, $grid)
    {
        $block = 'grid_column_'.$column->getId().'_filter';

        return $this->hasBlock($block) ?  $this->renderBlock($block, array('column' => $column, 'hash' => $grid->getHash())) : $column->renderFilter($grid->getHash());
    }

    public function getGridUrl($section, $grid, $param = null)
    {
        if ($section == 'order')
        {
            if ($param->isSorted())
            {
                return $grid->getRouteUrl().'?'.$grid->getHash().'[_order]='.$param->getId().'|'.$this->nextOrder($param->getOrder());
            }
            else
            {
                return $grid->getRouteUrl().'?'.$grid->getHash().'[_order]='.$param->getId().'|asc';
            }
        }
        elseif ($section == 'page')
        {
            return $grid->getRouteUrl().'?'.$grid->getHash().'[_page]='.$param;
        }
        elseif ($section == 'limit')
        {
            return $grid->getRouteUrl().'?'.$grid->getHash().'[_limit]=';
        }
    }

    public function nextOrder($value)
    {
        return  $value == 'asc' ? 'desc' : 'asc';
    }

    /**
     * Render block 
     *
     * @param $name string
     * @param $parameters string
     * @return string
     */
    private function renderBlock($name, $parameters)
    {
        //load template if needed
        if (is_null($this->template))
        {
            $this->loadTemplate();
        }

        if ($this->template->hasBlock($name))
        {
            return $this->template->renderBlock($name, $parameters);
        }
        elseif (($parent = $this->template->getParent(array())) !== false)
        {
            return $parent->renderBlock($name, $parameters);
        }
        else
        {
            throw new \InvalidArgumentException(sprintf('Block "%s" doesn\'t exist in grid template "%s".', $name, $this->theme));
        }
    }

    /**
     * Return true is block exist in template or parent template
     * @param string $name
     *
     * @return boolean
     */
    private function hasBlock($name)
    {
        //load template if needed
        if (is_null($this->template))
        {
            $this->loadTemplate();
        }

        if ($this->template->hasBlock($name))
        {
            return true;
        }

        if (!$this->template->hasBlock($name) && ($parent = $this->template->getParent(array())) !== false)
        {
            return $parent->hasBlock($name);
        }
    }

    private function loadTemplate()
    {
        //get template name
        if(is_null($this->theme))
        {
            $this->theme = 'SorienDataGridBundle::grid.html.twig';
        }

        $this->template = $this->environment->loadTemplate($this->theme);
    }

    public function getName()
    {
        return 'datagrid_twig_extension';
    }
}
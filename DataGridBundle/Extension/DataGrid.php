<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Extension;

/**
 * @todo use {% grid_theme grid '' %} instead of second argument in grid function
 */
class DataGrid extends \Twig_Extension {

    /**
     * @var \Twig_Environment
     */
    protected $environment;
    protected $template;
    protected $theme;

    public function __construct()
    {
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
            'grid'         => new \Twig_Function_Method($this, 'getGrid', array('is_safe' => array('html'))),
            'grid_titles'  => new \Twig_Function_Method($this, 'getGridTitles', array('is_safe' => array('html'))),
            'grid_filters' => new \Twig_Function_Method($this, 'getGridFilters', array('is_safe' => array('html'))),
            'grid_items'   => new \Twig_Function_Method($this, 'getGridItems', array('is_safe' => array('html'))),
            'grid_pager'   => new \Twig_Function_Method($this, 'getGridPager', array('is_safe' => array('html')))
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
        return $this->renderBlock('grid', array('grid' => $grid->getData()));
    }

    public function getGridTitles($columns)
    {
        return $this->renderBlock('grid_titles', array('columns' => $columns));
    }

    public function getGridFilters($columns)
    {
        return $this->renderBlock('grid_filters', array('columns' => $columns));
    }

    public function getGridItems($items)
    {
        return $this->renderBlock('grid_items', array('items' => $items));
    }

    public function getGridPager($columns)
    {
        return $this->renderBlock('grid_pager', array('columns' => $columns));
    }


    /**
     * Render block.
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
            //get template name
            if(is_null($this->theme))
            {
                $this->theme = 'DataGridBundle::datagrid.html.twig';
            }

            $this->template = $this->environment->loadTemplate($this->theme);
        }

        //todo exception if no proper block exist
        return $this->template->renderBlock($name, $parameters);
    }

    public function getName()
    {
        return 'datagrid_twig_extension';
    }
}
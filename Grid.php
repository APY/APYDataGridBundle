<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle;

use Sorien\DataGridBundle\DataGrid\Columns;
use Sorien\DataGridBundle\DataGrid\Actions;
use Sorien\DataGridBundle\DataGrid\Rows;
use Sorien\DataGridBundle\Column\Column;
use Sorien\DataGridBundle\Column\Action;
use Sorien\DataGridBundle\Source\Source;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Grid
{
    /**
     * @var \Symfony\Component\HttpFoundation\Session;
     */
    private $session;
    /**
    * @var \Symfony\Component\HttpFoundation\Request
    */
    private $request;

    /**
    * @var \Symfony\Component\Routing\Router
    */
    private $router;
    private $route;
    private $routeUrl;
    private $id;
    /**
    * @var \Sorien\DataGridBundle\Source\Source
    */
    private $source;

    private $totalCount;
    private $page;
    private $limit;
    private $limits;

    /**
    * @var \Sorien\DataGridBundle\Column\Column[]
    */
    private $columns;

    /**
    * @var \Sorien\DataGridBundle\DataGrid\Rows
    */
    private $rows;

    /**
    * @var \Sorien\DataGridBundle\DataGrid\Actions
    */
    private $actions;

    private $showFilters;
    private $showTitles;


    /**
     * @param $source Data Source
     * @param $controller
     * @param $route string if null current route will be used 
     * @param string $id set if you are using more then one grid inside controller
     */
    public function __construct($source, $controller, $route = '', $id = '')
    {
        if(!$source instanceof Source)
        {
            throw new \Exception('Supplied Source have to extend Source class.');
        }       
        
        $this->source = $source;

        $this->session = $controller->get('request')->getSession();
        $this->request = $controller->get('request');
        $this->router = $controller->get('router');

        $this->setRoute($route);

        $name = explode('::', $this->request->get('_controller'));
        $this->id = md5($name[0].$id);
        $this->limits = array('20' => '20', '50' => '50', '100' => '100');
        $this->limit = key($this->limits);
        $this->page = 0;
        $this->showTitles = $this->showFilters = true;

        $this->columns = new Columns();
        $this->actions = new Actions();

        //get cols from source
        $this->source->prepare($this->columns, $this->actions);

        //store column data
        $this->fetchAndSaveColumnData();

        //execute actions
        $this->executeActions();

        //store drid data
        $this->fetchAndSaveGridData();
    }

    /**
     * Retrieve Column Data from Session and Request
     *
     * @param string $column
     * @param bool $onlyFromRequest
     * @return null|string
     */
    private function getData($column, $onlyFromRequest = false)
    {
        $result = null;
        if (!$onlyFromRequest && is_array($data = $this->session->get($this->getHash())))
        {
            if (isset($data[$column]))
            {
                $result = $data[$column];
            }
        }

        if (is_array($data = $this->request->get($this->getHash())))
        {
            if (isset($data[$column]))
            {
                $result = $data[$column];
            }
        }

        return $result;
    }

    /**
     * Set and Store Columns data
     *
     * @return void
     */
    private function fetchAndSaveColumnData()
    {
        $storage = array();

        foreach ($this->columns as $column)
        {
            $column->setData($this->getData($column->getId()));

            if (!is_null($data = $column->getData()))
            {
                $storage[$column->getId()] = $data;
            }
        }

        if (!empty($storage))
        {
            $this->session->set($this->getHash(), $storage);
        }
    }

    /**
     * Set and Store Initial Grid data
     *
     * @return void
     */
    private function fetchAndSaveGridData()
    {
        $storage = array();

        //set internal data
        $limit = $this->getData('_limit');
        if (!is_null($limit))
        {
            $this->limit = $limit;
        }

        $page = $this->getData('_page');
        if (!is_null($page) && $page > 0)
        {
            $this->page = $page;
        }

        $order = $this->getData('_order');
        if (!is_null($order))
        {
            list($columnId, $columnOrder) = explode('|', $order);

            $column = $this->columns->getColumnById($columnId);
            if (!is_null($column))
            {
                $column->setOrder($columnOrder);
            }

            $storage['_order'] = $order;
        }

        if ($this->limit != key($this->limits))
        {
            $storage['_limit'] = $this->limit;
        }

        if ($this->page > 0)
        {
            $storage['_page'] = $this->page;
        }

        // save data to sessions if needed
        if (!empty($storage))
        {
            $this->session->set($this->getHash(), $storage);
        }
    }

    public function executeActions()
    {
        $id = $this->getData('__action_id', true);
        $data = $this->getData('__action', true);

        if ($id > -1 && is_array($data))
        {
            $action = $this->actions->getAction($id);

            if (is_callable($action['callback']))
            {
                call_user_func($action['callback'], array_keys($data), false, $this->session);
            }
        }
    }


    /**
     * Prepare Grid for Drawing
     *
     * @return void
     */
    public function prepare()
    {
        $this->rows = $this->source->execute($this->columns->getIterator(true), $this->page, $this->limit);

        if(!$this->rows instanceof Rows)
        {
            throw new \Exception('Source have to return Rows object.');
        }

        //add action column
        if ($this->actions->count() > 0)
        {
            $this->columns->addColumn(new Action($this->getHash()), 0);
        }

        $primaryColumnId = $this->columns->getPrimaryColumn()->getId();

        foreach ($this->rows as $row)
        {
            foreach ($this->columns as $column)
            {
                $row->setField($column->getId(), $column->renderCell($row->getField($column->getId()), $row, $this->router, $row->getField($primaryColumnId)));
            }
        }

        //autohie titles when no title is set
        if (!$this->showTitles)
        {
            $this->showTitles = false;
            foreach ($this->columns as $column)
            {
                if (!$this->showTitles) break;

                if ($column->getTitle() != '')
                {
                    $this->showTitles = true;
                    break;
                }
            }
        }

        //get size
        $this->totalCount = $this->source->getTotalCount($this->columns);

        return $this;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($columns)
    {
        if(!$columns instanceof Columns)
        {
            throw new \Exception('Supplied object have to extend Columns class.');
        }

        $this->columns = $columns;
        $this->fetchAndSaveColumnData();

        return $this;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getActions()
    {
        return $this->actions;
    }
    
    public function setRoute($route = '')
    {
        $this->route = $route == '' ? $this->request->get('_route') : $route;

        return $this;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getRouteUrl()
    {
        if ($this->routeUrl == '')
        {
            $this->routeUrl = $this->router->generate($this->route);
        }

        return $this->routeUrl;
    }

    public function isReadyForRedirect()
    {
        return ($this->route == $this->request->get('_route'));
    }
    
    public function getHash()
    {
        return 'grid_'.$this->id;
    }

    public function setLimits($limits)
    {
        $this->limits = $limits;
    }

    public function getLimits()
    {
        return $this->limits;
    }

    public function getCurrentLimit()
    {
        return $this->limit;
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getPageCount()
    {
        return ceil($this->getTotalCount() / $this->getCurrentLimit());
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * @return bool
     */
    public function isTitleSectionVisible()
    {
        return $this->showTitles;
    }

    /**
     * @return bool
     */
    public function isFilterSectionVisible()
    {
        return $this->showFilters;
    }

    public function hideFilters()
    {
        $this->showFilters = false;
    }

    public function hideTitles()
    {
        $this->showTitles = false;
    }

}

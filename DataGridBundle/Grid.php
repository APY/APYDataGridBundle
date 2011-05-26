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
use Sorien\DataGridBundle\Column\MassAction;
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

    private $totalRows;
    private $page;
    private $limit;
    private $limits;

    /**
    * @var \Sorien\DataGridBundle\Column\Column[]
    */
    private $columns;
    private $rows;
    private $actions;

    private $updated;

    /**
     * @param $source Data Source
     * @param $controller
     * @param $route string if null current route will be used 
     * @param string $id set if you are using more then one grid inside controller
     */
    public function __construct($source, $controller, $route = null, $id = '')
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

        $name = explode('::', $controller->get('request')->get('_controller'));
        $this->id = md5($name[0].$id);

        $this->columns = new Columns();
        $this->actions = new Actions();

        $this->source->initialize($controller);
        
        //get cols from source
        $this->source->prepare($this->columns, $this->actions);

        if ($this->actions->count() > 0)
        {
           $this->columns->insertColumn(0, new MassAction());
        }

        $this->limits = array('20' => '20', '50' => '50', '100' => '100');
        $this->limit = key($this->limits);
        $this->page = 0;

        $this->update();
    }

    private function getData($column)
    {
        $result = null;
        if (is_array($data = $this->session->get($this->getHash())))
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

    public function update()
    {
        $saveData = array();

        foreach ($this->columns as $column)
        {
            $column->setData($this->getData($column->getId()));
        }

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
        }

        if (!is_null($order))
        {
            $saveData['_order'] = $order;
        }

        foreach ($this->columns as $column)
        {
            $data = $column->getData();
            if (!is_null($data))
            {
                $saveData[$column->getId()] = $data;
            }
        }

        if ($this->limit != key($this->limits))
        {
            $saveData['_limit'] = $this->limit;
        }

        if ($this->page > 0)
        {
            $saveData['_page'] = $this->page;
        }

        // if we need save sessions
        if (!empty($saveData)) 
        {
            $this->session->set($this->getHash(), $saveData);
        }

        $this->updated = true;
    }

    /**
     * Get data form Source Object
     * @return void
     */
    public function prepare()
    {
        foreach ($this->columns as $column)
        {
            if ($column->isVisible())
            {
                $column->prepareFilter($this->getHash());
            }
        }

        $this->rows = $this->source->execute($this->columns, $this->page, $this->limit);

        if(!$this->rows instanceof Rows)
        {
            throw new \Exception('Source have to return Rows object.');
        }

        foreach ($this->rows as $row)
        {
            foreach ($this->columns as $column)
            {
                $row->setField($column->getId(), $column->renderCell($row->getField($column->getId()), $row, $this->router));
            }
        }

        //get size
        $this->totalRows = $this->source->getTotalCount();

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
        $this->update();
        
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
    
    public function setRoute($route = null)
    {
        $this->route = is_null($route) ? $this->request->get('_route') : $route;

        return $this;
    }

    public function getRouteUrl()
    {
        if ($this->routeUrl == null)
        {
            $this->routeUrl = $this->router->generate($this->route);
        }

        return $this->routeUrl;
    }

    public function getReadyForRedirect()
    {
        return ($this->updated && $this->route == $this->request->get('_route'));
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

}

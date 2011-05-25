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

        $this->updated = false;
        $this->update();
    }

    public function update()
    {
        $saveData = array();

        if (is_array($grid = $this->session->get($this->getHash())))
        {
            foreach ($this->columns as $column)
            {
                if (isset($grid[$column->getId()]) && is_array($grid[$column->getId()])) 
                {
                    //set orders
                    if (isset($grid[$column->getId()]['order'])) 
                    {
                        $column->setOrder($grid[$column->getId()]['order']);
                    }

                    //set filters
                    if (isset($grid[$column->getId()]['filter'])) 
                    {
                        $column->setFilterData($grid[$column->getId()]['filter']);
                    }
                }
            }
        }

        //set order form get
        if (is_array($orders = $this->request->query->get($this->getHash())))
        {
            foreach ($this->columns as $column)
            {
                if (isset($orders[$column->getId()])) 
                {
                    $column->setOrder($orders[$column->getId()]['order']);
                    $saveData[$column->getId()]['order'] = $column->getOrder();

                    if ($column->isFiltred()) 
                    {
                        $saveData[$column->getId()]['filter'] = $column->getFilterData();
                    }

                }
            }
        }

        //set filter from post
        if (is_array($filters = $this->request->request->get($this->getHash())))
        {
            foreach ($this->columns as $column)
            {
                if (isset($filters[$column->getId()])) 
                {
                    $column->setFilterData($filters[$column->getId()]['filter']);
                    $saveData[$column->getId()]['filter'] = $column->getFilterData();

                    if ($column->isSorted()) 
                    {
                        $saveData[$column->getId()]['order'] = $column->getOrder();
                    }
                }
            }
        }

        // if we need save sessions
        if (!empty($saveData)) 
        {
            $this->session->set($this->getHash(), $saveData);
        }

        //@todo remove
        $this->limit = 20;

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

                if ($column->isSorted())
                {
                    $column->setOrderUrl($this->getRouteUrl().'?'.$this->getHash().'['.$column->getId().'][order]='.column::nextOrder($column->getOrder()));
                }
                else
                {
                    $column->setOrderUrl($this->getRouteUrl().'?'.$this->getHash().'['.$column->getId().'][order]=asc');
                }
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
    
    private function getHash()
    {
        return 'grid_'.$this->id;
    }
    
}

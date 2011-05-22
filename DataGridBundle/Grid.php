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

use Sorien\DataGridBundle\Source;
use Sorien\DataGridBundle\Columns;
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

    private $url;
    private $id;
    /**
    * @var Source
    */
    private $source;
    private $totalRows;
    private $page;
    private $data;
    /**
    * @var Column[]
    */
    private $columns;
    private $rows;

    /**
     * @param $source Data Source
     * @param $controller
     * @param $route route for
     * @param string $id set if you are using more then one grid inside controller
     */
    public function __construct($source, $controller, $route = null, $id = '')
    {
        $this->source = $source;

        $this->session = $controller->get('request')->getSession();
        $this->request = $controller->get('request');
        $this->router = $controller->get('router');

        $this->url = (!is_null($route)) ? $this->router->generate($route) : '';

        $name = explode('::', $controller->get('request')->get('_controller'));
        $this->id = md5($name[0].$id);

        $this->columns = new Columns();
        //get cols from source
        $this->source->prepare($this->columns);
        $saveData = array();

        if (is_array($grid = $this->session->get('grid_'.$this->id)))
        {
            foreach ($this->columns as $column)
            {
                if (isset($grid[$column->getId()]) && is_array($grid[$column->getId()]) )
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
        if (is_array($orders = $this->request->query->get('grid_'.$this->id)))
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
        if (is_array($filters = $this->request->request->get('grid_'.$this->id)))
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
            $this->session->set('grid_'.$this->id, $saveData);
        }

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
                $column->prepareFilter('grid_'.$this->id);

                if ($column->isSorted())
                {
                    $column->setOrderUrl($this->url.'?grid_'.$this->id.'['.$column->getId().'][order]='.column::nextOrder($column->getOrder()));
                }
                else
                {
                    $column->setOrderUrl($this->url.'?grid_'.$this->id.'['.$column->getId().'][order]=asc');
                }
            }
        }

        $this->rows = $this->source->execute($this->columns, $this->page);

        if(!$this->rows instanceof Rows)
        {
            throw new \Exception('Source have to return Rows object.');
        }

        foreach ($this->rows as $row)
        {
            foreach ($this->columns as $column)
            {
                $row->setField($column->getId(), $column->drawCell($row->getField($column->getId()), $row, $this->router));
            }
        }

        //get size
        $this->totalRows = $this->source->getTotalCount();

        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function getRows()
    {
        return $this->rows;
    }
}

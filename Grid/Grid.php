<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sorien\DataGridBundle\Grid;

use Sorien\DataGridBundle\Grid\Columns;
use Sorien\DataGridBundle\Grid\Rows;
use Sorien\DataGridBundle\Grid\Action\MassActionInterface;
use Sorien\DataGridBundle\Grid\Action\RowActionInterface;
use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Column\MassActionColumn;
use Sorien\DataGridBundle\Grid\Column\ActionsColumn;
use Sorien\DataGridBundle\Grid\Source\Source;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Grid
{
    const UNLIMITED = 0;

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

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    private $container;

    /**
     * @var array
     */
    private $routeParameters;

    /**
     * @var string
     */
    private $routeUrl;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $hash;

    /**
    * @var \Sorien\DataGridBundle\Grid\Source\Source
    */
    private $source;

    private $totalCount;
    private $page;
    private $limit;
    private $limits;

    /**
    * @var \Sorien\DataGridBundle\Grid\Column\Columns
    */
    private $columns;

    /**
    * @var \Sorien\DataGridBundle\Grid\DataSorien\DataGridBundle\Grid\Rows
    */
    private $rows;

    private $massActions;
    private $rowActions;

    /**
     * @var boolean
     */
    private $showFilters;

    /**
     * @var boolean
     */
    private $showTitles;

    /**
     * @param \Source\Source $source Data Source
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param string $id set if you are using more then one grid inside controller
     */
    public function __construct($container, $source = null, $id = '')
    {
        if(!is_null($source) && !($source instanceof Source))
        {
            throw new \InvalidArgumentException(sprintf('Supplied Source have to extend Source class and not %s', get_class($source)));
        }

        $this->container = $container;

        $this->router = $container->get('router');
        $this->request = $container->get('request');
        $this->session = $this->request->getSession();

        $this->setId($id);

        $this->setLimits(array(20 => '20', 50 => '50', 100 => '100'));
        $this->page = 0;
        $this->showTitles = $this->showFilters = true;

        $this->columns = new Columns($container->get('security.context'));
        $this->massActions = array();
        $this->rowActions = array();

        $this->routeParameters = $this->request->attributes->all();
        unset($this->routeParameters['_route']);
        unset($this->routeParameters['_controller']);

        if (!is_null($source))
        {
            $this->setSource($source);
        }
    }
    
    function addColumn($column, $position = 0)
    {
        $this->columns->addColumn($column, $position);
        
        return $this;
    }
    
    function addMassAction(MassActionInterface $action)
    {
        if ($this->source instanceof Source)
        {
            throw new \RuntimeException('The actions have to be defined before the source.');
        }
        $this->massActions[] = $action;
        
        return $this;
    }
    
    function addRowAction(RowActionInterface $action)
    {
        $this->rowActions[$action->getColumn()][] = $action;
        
        return $this;
    }

    public function setSource($source)
    {
        if(!is_null($this->source))
        {
            throw new \InvalidArgumentException('Source can be set just once.');
        }

        if (!($source instanceof Source))
        {
            throw new \InvalidArgumentException('Supplied Source have to extend Source class.');
        }

        $this->source = $source;

        $this->source->initialise($this->container);

        //get cols from source
        $this->source->getColumns($this->columns);

        //store column data
        $this->fetchAndSaveColumnData();

        //execute massActions
        $this->executeMassActions();
        
        //store grid data
        $this->fetchAndSaveGridData();

        return $this;
    }

    /**
     * Retrieve Column Data from Session and Request
     *
     * @param string $column
     * @param bool $fromRequest
     * @param bool $fromSession
     * @return null|string
     */
    private function getData($column, $fromRequest = true, $fromSession = true)
    {
        $result = null;

        if ($fromSession && is_array($data = $this->session->get($this->getHash())))
        {
            if (isset($data[$column]))
            {
                $result = $data[$column];
            }
        }

        if ($fromRequest && is_array($data = $this->request->get($this->getHash())))
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
        $storage = $this->session->get($this->getHash());

        foreach ($this->columns as $column)
        {
            $column->setData($this->getData($column->getId()));

            if (($data = $column->getData()) !== null)
            {
                $storage[$column->getId()] = $data;
            }
            else
            {
                unset($storage[$column->getId()]);
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
        $storage = $this->session->get($this->getHash());
        
        //set internal data
        if ($limit = $this->getData('_limit'))
        {
            $this->limit = $limit;
        }

        if ($page = $this->getData('_page'))
        {
            $this->setPage($page);
        }

        if (!is_null($order = $this->getData('_order')))
        {
            list($columnId, $columnOrder) = explode('|', $order);

            $column = $this->columns->getColumnById($columnId);
            if (!is_null($column))
            {
                $column->setOrder($columnOrder);
            }

            $storage['_order'] = $order;
        }

        if ($this->getCurrentLimit() != $this->getData('_limit', false) && $this->getCurrentLimit() >= 0)
        {
            $storage['_limit'] = $this->getCurrentLimit();
        }

        if ($this->getPage() >= 0)
        {
            $storage['_page'] = $this->getPage();
        }

        // save data to sessions if needed
        if (!empty($storage))
        {
            $this->session->set($this->getHash(), $storage);
        }
    }

    public function executeMassActions()
    {        
        $id = $this->getData('__action_id', true, false);
        $data = $this->getData('__action', true, false);

        if ($id > -1 && is_array($data))
        {
            if (array_key_exists($id, $this->massActions))
            {
                $action = $this->massActions[$id];
                
                if (is_callable($action->getCallback()))
                {
                    call_user_func($action->getCallback(), array_keys($data), false, $this->session);
                }
                else
                {
                    throw new \RuntimeException(sprintf('Callback %s is not callable.', $action->getCallback()));
                }
            }
            else
            {
                throw new \OutOfBoundsException(sprintf('Action %s is not defined.', $id));
            }
        }
    }

    /**
     * Prepare Grid for Drawing
     *
     * @return Grid
     */
    public function prepare()
    {        
        $this->rows = $this->source->execute($this->columns->getIterator(true), $this->page, $this->limit);

        if(!$this->rows instanceof Rows)
        {
            throw new \Exception('Source have to return Rows object.');
        }
        
        //add row actions column
        if (count($this->rowActions) > 0)
        {
            foreach ($this->rowActions as $column => $rowActions) {
                if ($rowAction = $this->columns->hasColumnById($column, true)) {
                    $rowAction->setRowActions($rowActions);
                }
                else {
                    $this->columns->addColumn(new ActionsColumn($column, 'Actions', $rowActions));
                }
            }
        }

        //add mass actions column
        if (count($this->massActions) > 0)
        {
            $this->columns->addColumn(new MassActionColumn($this->getHash()), 1);
        }
        
        $primaryColumnId = $this->columns->getPrimaryColumn()->getId();

        foreach ($this->rows as $row)
        {
            foreach ($this->columns as $column)
            {
                $row->setPrimaryField($primaryColumnId);
            }
        }

        //@todo refactor autohide titles when no title is set
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
        if(!is_int($this->totalCount = $this->source->getTotalCount($this->columns)))
        {
            throw new \Exception(sprintf('Source function getTotalCount need to return integer result, returned: %s', gettype($this->totalCount)));
        }
        
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
            throw new \InvalidArgumentException('Supplied object have to extend Columns class.');
        }

        $this->columns = $columns;
        $this->fetchAndSaveColumnData();

        return $this;
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function getMassActions()
    {
        return $this->massActions;
    }
    
    public function getRowActions()
    {
        return $this->rowActions;
    }

    public function setRouteParameter($parameter, $value)
    {
        $this->routeParameters[$parameter] = $value;
    }

    public function getRouteParameters()
    {
        return $this->routeParameters;
    }
    
    public function getRouteUrl()
    {
        if ($this->routeUrl == '')
        {
            $this->routeUrl = $this->router->generate($this->request->get('_route'), $this->getRouteParameters());
        }

        return $this->routeUrl;
    }

    public function isReadyForRedirect()
    {
        $data = $this->request->get($this->getHash());
        return !empty($data);
    }
    
    public function getHash()
    {
        return 'grid_'.$this->hash;
    }

    /**
     * @param mixed $limits
     * @return \Sorien\DataGridBundle\Grid\Grid
     */
    public function setLimits($limits)
    {
        if (is_array($limits))
        {
            $this->limits = $limits;
            $this->limit = (int)key($this->limits);
        }
        elseif (is_int($limits))
        {
            $this->limits = array($limits => (string)$limits);
            $this->limit = $limits;
        }
        else
        {
            throw new \InvalidArgumentException('Limit has to be array or integer');
        }

        return $this;
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
        if ((int)$page > 0)
        {
            $this->page = (int)$page;
        }
        else
        {
            throw new \InvalidArgumentException('Page has to have a positive number');
        }
        return $this;
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
     * @todo fix according as isFilterSectionVisible
     */
    public function isTitleSectionVisible()
    {
        return $this->showTitles ;
    }

    public function isFilterSectionVisible()
    {
        if ($this->showFilters == true)
        {
            foreach ($this->columns as $column)
            {
                if ($column->isFilterable())
                {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @return bool return true if pager is visible
     */
    public function isPagerSectionVisible()
    {
        $limits = sizeof($this->getLimits());
        return $limits > 1 || ($limits == 0 && $this->getCurrentLimit() < $this->totalCount);
    }

    /**
     * Function will hide all column filters
     */
    public function hideFilters()
    {
        $this->showFilters = false;
    }

    /**
     * function will hide all column titles
     */
    public function hideTitles()
    {
        $this->showTitles = false;
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Column\Column $extension
     * @return void
     */
    public function addColumnExtension($extension)
    {
        $this->columns->addExtension($extension);
    }

    /**
     * Sets unique filter identification
     *
     * @param $id
     * @return Grid
     */
    public function setId($id)
    {
        $this->id = $id;
        $this->hash = md5($this->request->get('_controller').$id);
        
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
    
    public function deleteAction($ids)
    {
        $this->source->delete($ids);
    }

    function __clone()
    {
        /**
         * clone all objects
         */
        $this->columns = clone $this->columns;
    }
}
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

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\Exception\PropertyAccessDeniedException;

use Sorien\DataGridBundle\Grid\Columns;
use Sorien\DataGridBundle\Grid\Rows;
use Sorien\DataGridBundle\Grid\Action\MassActionInterface;
use Sorien\DataGridBundle\Grid\Action\RowActionInterface;
use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Column\MassActionColumn;
use Sorien\DataGridBundle\Grid\Column\ActionsColumn;
use Sorien\DataGridBundle\Grid\Column\PopulatableColumnInterface;
use Sorien\DataGridBundle\Grid\Source\Source;


class Grid
{
    const REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED = '__action_all_keys';
    const REQUEST_QUERY_MASS_ACTION = '__action_id';
    const REQUEST_QUERY_PAGE = '_page';
    const REQUEST_QUERY_LIMIT = '_limit';
    const REQUEST_QUERY_ORDER = '_order';

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
     * @var \Sorien\DataGridBundle\Grid\Columns|\Sorien\DataGridBundle\Grid\Column\Column[]
     */
    private $columns;

    /**
     * @var @var \Sorien\DataGridBundle\Grid\Rows
     */
    private $rows;

    /**
     * @var \Sorien\DataGridBundle\Grid\Action\MassAction[]
     */
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
     * @var array|object
     */
    private $data = null;

    /**
     * @var string
     */
    private $prefixTitle = '';

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param \Source\Source $source Data Source
     */
    public function __construct($container, $source = null)
    {
        if(!is_null($source) && !($source instanceof Source))
        {
            throw new \InvalidArgumentException(sprintf('Supplied Source have to extend Source class and not %s', get_class($source)));
        }

        $this->container = $container;

        $this->router = $container->get('router');
        $this->request = $container->get('request');
        $this->session = $this->request->getSession();

        $this->id = '';

        $this->setLimits(array(20 => '20', 50 => '50', 100 => '100'));
        $this->page = 0;
        $this->showTitles = $this->showFilters = true;

        $this->columns = new Columns($container->get('security.context'));
        $this->massActions = array();
        $this->rowActions = array();

        $this->routeParameters = $this->request->attributes->all();
        unset($this->routeParameters['_route']);
        unset($this->routeParameters['_controller']);
        unset($this->routeParameters['_route_params']);

        if (!is_null($source))
        {
            $this->setSource($source);
        }
    }

    /**
     * Sets Source to the Grid
     *
     * @param $source
     * @return Grid
     * @throws \InvalidArgumentException
     */
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

        //update populatable columns
        foreach($this->columns as $column) {
            if($column instanceof PopulatableColumnInterface) {
                $column->populate($this->source);
            }
        }

        //generate hash
        $this->createHash();

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
    private function getDataFromContext($column, $fromRequest = true, $fromSession = true)
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
            $column->setData($this->getDataFromContext($column->getId()));

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
        if ($limit = $this->getDataFromContext(self::REQUEST_QUERY_LIMIT))
        {
            $this->limit = $limit;
        }

        if ($page = $this->getDataFromContext(self::REQUEST_QUERY_PAGE))
        {
            $this->setPage($page);
        }

        if (!is_null($order = $this->getDataFromContext(self::REQUEST_QUERY_ORDER)))
        {
            list($columnId, $columnOrder) = explode('|', $order);

            $column = $this->columns->getColumnById($columnId);
            if (!is_null($column))
            {
                $column->setOrder($columnOrder);
            }

            $storage[self::REQUEST_QUERY_ORDER] = $order;
        }

        if ($this->getCurrentLimit() != $this->getDataFromContext(self::REQUEST_QUERY_LIMIT, false) && $this->getCurrentLimit() >= 0)
        {
            $storage[self::REQUEST_QUERY_LIMIT] = $this->getCurrentLimit();
        }

        if ($this->getPage() >= 0)
        {
            $storage[self::REQUEST_QUERY_PAGE] = $this->getPage();
        }

        // save data to sessions if needed
        if (!empty($storage))
        {
            $this->session->set($this->getHash(), $storage);
        }
    }

    public function executeMassActions()
    {
        $actionId = $this->getDataFromContext(Grid::REQUEST_QUERY_MASS_ACTION, true, false);
        $actionAllKeys = (boolean)$this->getDataFromContext(Grid::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED, true, false);
        $actionKeys = $actionAllKeys == false ? $this->getDataFromContext(MassActionColumn::ID, true, false) : array();

        if ($actionId > -1 && is_array($actionKeys))
        {
            if (array_key_exists($actionId, $this->massActions))
            {
                $action = $this->massActions[$actionId];

                if (is_callable($action->getCallback()))
                {
                    call_user_func($action->getCallback(), array_keys($actionKeys), $actionAllKeys, $this->session);
                }
                elseif (substr_count($action->getCallback(), ':') == 2)
                {
                    $this->container->get('http_kernel')->forward($action->getCallback(), array('primaryKeys' => array_keys($actionKeys), 'allPrimaryKeys' => $actionAllKeys));
                }
                else
                {
                    throw new \RuntimeException(sprintf('Callback %s is not callable or Controller action', $action->getCallback()));
                }
            }
            else
            {
                throw new \OutOfBoundsException(sprintf('Action %s is not defined.', $actionId));
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
        if ($this->isDataLoaded())
        {
            $this->rows = $this->executeFromData($this->columns->getIterator(true), $this->page, $this->limit);
        }
        else {
            $this->rows = $this->source->execute($this->columns->getIterator(true), $this->page, $this->limit);
        }

        if(!$this->rows instanceof Rows)
        {
            throw new \Exception('Source have to return Rows object.');
        }

        if (count($this->rows) == 0 && $this->page > 0){
            $this->page = 0;
            $this->prepare();
        }

        //add row actions column
        if (count($this->rowActions) > 0)
        {
            foreach ($this->rowActions as $column => $rowActions)
            {
                if ($rowAction = $this->columns->hasColumnById($column, true))
                {
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

        foreach ($this->rows as $row) {
            $row->setPrimaryField($primaryColumnId);
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
        if ($this->isDataLoaded())
        {
            $this->totalCount = $this->getTotalCountFromData();
        }
        else {
            $this->totalCount = $this->source->getTotalCount($this->columns);
        }

        if(!is_int($this->totalCount))
        {
            throw new \Exception(sprintf('Source function getTotalCount need to return integer result, returned: %s', gettype($this->totalCount)));
        }

        return $this;
    }

    /**
     * Adds custom column to the grid
     *
     * @param $column
     * @param int $position
     * @return Grid
     */
    function addColumn($column, $position = 0)
    {
        $this->columns->addColumn($column, $position);

        return $this;
    }

    /**
     * Returns Grid Columns
     *
     * @return Column\Column[]|Columns
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Sets Array of Columns to the grid
     *
     * @param $columns
     * @return Grid
     * @throws \InvalidArgumentException
     */
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

    /**
     * Adds Mass Action
     *
     * @param Action\MassActionInterface $action
     * @return Grid
     */
    function addMassAction(MassActionInterface $action)
    {
        if ($this->source instanceof Source)
        {
            throw new \RuntimeException('The actions have to be defined before the source.');
        }
        $this->massActions[] = $action;

        return $this;
    }

    /**
     * Returns Mass Actions
     *
     * @return Action\MassAction[]
     */
    public function getMassActions()
    {
        return $this->massActions;
    }

    /**
     * Adds Row Action
     *
     * @param Action\RowActionInterface $action
     * @return Grid
     */
    function addRowAction(RowActionInterface $action)
    {
        $this->rowActions[$action->getColumn()][] = $action;

        return $this;
    }

    /**
     * Returns Row Actions
     *
     * @return Action\RowAction[]
     */
    public function getRowActions()
    {
        return $this->rowActions;
    }

    /**
     * Sets Route Parameters
     *
     * @param string $parameter
     * @param mixed $value
     *
     * @return Grid
     */
    public function setRouteParameter($parameter, $value)
    {
        $this->routeParameters[$parameter] = $value;
    }

    /**
     * Returns Route Parameters
     *
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Returns Route URL
     *
     * @return string
     */
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

    public function createHash()
    {
        $this->hash = 'grid_'.md5($this->request->get('_controller').$this->columns->getHash().$this->source->getHash().$this->getId());
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Init value for filters
     *
     * @param array Hash of columnName => initValue
     * @return Grid
     */
    public function initFilters(array $filters) {
        foreach ($filters as $columnName => $value) {
            if (is_null($this->getDataFromContext($columnName, false, true))) {
                $this->columns->getColumnById($columnName)->setData($value);
            }
        }

        return $this;
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

        return $this;
    }

    /**
     * Returns unique filter identifier
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets Limits
     *
     * @param mixed $limits e.g. array(10 => '10', 1000 => '1000')
     * @return Grid
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

    /**
     * Returns limits
     *
     * @return array
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * Returns selected Limit (Rows Per Page)
     * @return mixed
     */
    public function getCurrentLimit()
    {
        return $this->limit;
    }

    /**
     * Sets current Page
     *
     * @param $page
     * @return Grid
     */
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

    /**
     * Returns current page
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }


    /**
     * Returnd grid display data as rows - internal helper for templates
     *
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Return count of available pages
     *
     * @return float
     */
    public function getPageCount()
    {
        return ceil($this->getTotalCount() / $this->getCurrentLimit());
    }

    /**
     * Returns count of filtred rows(items) from source
     *
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Return true if if title panel is visible in template - internal helper
     *
     * @return bool
     */
    public function isTitleSectionVisible()
    {
        if ($this->showTitles == true)
        {
            foreach ($this->columns as $column)
            {
                // Not tested yet
                if ($column->getTitle() != '')
                {
                    return true;
                }
            }
        }
    }

    /**
     * Return true if if filter panel is visible shown in template - internal helper
     *
     * @return bool
     */
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
     * Return true if if pager panel is visible in template - internal helper
     *
     * @return bool return true if pager is visible
     */
    public function isPagerSectionVisible()
    {
        $limits = sizeof($this->getLimits());
        return $limits > 1 || ($limits == 0 && $this->getCurrentLimit() < $this->totalCount);
    }

    /**
     * Hides Filters Panel
     *
     * @return Grid
     */
    public function hideFilters()
    {
        $this->showFilters = false;
    }

    /**
     * Hides Titles panel
     *
     * @return Grid
     */
    public function hideTitles()
    {
        $this->showTitles = false;
    }

    /**
     * Adds Column Extension - internal helper
     *
     * @param Column\Column $extension
     * @return void
     */
    public function addColumnExtension($extension)
    {
        $this->columns->addExtension($extension);
    }

    /**
     * Set a prefix title
     *
     * @param $prefixTitle string
     */
    public function setPrefixTitle($prefixTitle)
    {
        $this->prefixTitle = $prefixTitle;
        return $this;
    }

    /**
     * Get the prefix title
     *
     * @return string
     */
    public function getPrefixTitle()
    {
        return $this->prefixTitle;
    }

    /**
     * Default delete action
     *
     * @param $ids
     */
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

    /**
     * Redirects or Renders a view - helper function
     *
     * @param array $parameters An array of parameters to pass to the view
     * @param string $view The view name
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function gridResponse(array $parameters = array(), $view = null, Response $response = null)
    {
        if ($this->isReadyForRedirect())
        {
            return new RedirectResponse($this->getRouteUrl());
        }
        else
        {
            if (is_null($view))
            {
                return $parameters;
            }
            else
            {
                return $this->container->get('templating')->renderResponse($view, $parameters, $response);
            }
        }
    }

    /****** DATA SOURCE ******/

    /**
     * Use data instead of fetching the source
     *
     * @param array|object $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get the loaded data
     *
     * @return array|object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check if data is loaded
     *
     * @return boolean
     */
    public function isDataLoaded()
    {
        return !is_null($this->data);
    }

    /**
     * Find data from array|object
     *
     * @abstract
     * @param \Sorien\DataGridBundle\Grid\Column\Column[] $columns
     * @param int $page
     * @param int $limit
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    private function executeFromData($columns, $page = 0, $limit = 0)
    {
        // Populate from data
        $items = array();
        foreach ($this->data as $key => $item)
        {
            foreach ($columns as $column)
            {
                $fieldName = $column->getField();
                $functionName = ucfirst($fieldName);
                if (isset($item->$fieldName)) {
                    $fieldValue = $item->$fieldName;
                }
                else if (is_callable(array($item, 'get'.$functionName))) {
                    $fieldValue = call_user_func(array($item, 'get'.$functionName));
                }
                else if (is_callable(array($item, 'has'.$functionName))) {
                    $fieldValue = call_user_func(array($item, 'has'.$functionName));
                }
                else if (is_callable(array($item, 'is'.$functionName))) {
                    $fieldValue = call_user_func(array($item, 'is'.$functionName));
                }
                else {
                    throw new PropertyAccessDeniedException(sprintf('Property "%s" is not public or has no accessor.', $fieldName));
                }

                $items[$key][$fieldName] = $fieldValue;
            }
        }

        // Order
        foreach ($columns as $column)
        {
            if ($column->isSorted())
            {
                $sortedItems = array();
                foreach ($items as $key => $item)
                {
                    $sortedItems[$key] = $item[$column->getField()];
                }

                // Type ? (gettype function)
                array_multisort($sortedItems, ($column->getOrder() == 'asc') ? SORT_ASC : SORT_DESC, SORT_STRING, $items);
                break;
            }
        }

        // Pager
        if ($limit > 0)
        {
            $items = array_slice($items, $page * $limit, $limit);
        }


        $result = new Rows();
        foreach ($items as $item)
        {
            $row = new Row();
            foreach ($item as $fieldName => $fieldValue) {
                $row->setField($fieldName, $fieldValue);
            }

            //call overridden prepareRow or associated closure
            if (($modifiedRow = $this->source->prepareRow($row)) != null)
            {
                $result->addRow($modifiedRow);
            }
        }

        return $result;
    }

    /**
     * Get Total count of data items
     *
     * @return int
     */
    private function getTotalCountFromData()
    {
        return count($this->data);
    }

    /**
    * sets a list of columns to hide when the grid is output
    * @param array $hiddenColumns
    */
    public function setHiddenColumns(array $hiddenColumns)
    {
        if(empty($this->source))
        {
            throw new \InvalidArgumentException('setHiddenColumns needs the grid source set beforehand');
        }

        if(empty($hiddenColumns))
        {
            throw new \InvalidArgumentException('setHiddenColumns needs an array of column ids');
        }

        foreach ($hiddenColumns as $column_id) {
            $this->columns->getColumnById($column_id)->setVisible(false);
        }
    }

    /**
    * sets a list of columns to show when the grid is output
    * it acts as a mask; Other columns will be set as hidden
    * @param array $visibleColumns
    */
    public function setVisibleColumns(array $visibleColumns)
    {
        if(empty($this->source))
        {
            throw new \InvalidArgumentException('setVisibleColumns needs the grid source set beforehand');
        }

        $columnNames = array();
        foreach ($this->columns as $column) {
            $columnNames[] = $column->getId();
        }

        $this->setHiddenColumns(array_diff($columnNames, $visibleColumns));
    }
}

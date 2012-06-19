<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace APY\DataGridBundle\Grid;

use Symfony\Component\HttpFoundation\RedirectResponse;

use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Rows;
use APY\DataGridBundle\Grid\Action\MassActionInterface;
use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\MassActionColumn;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Source\Source;

class Grid
{
    const REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED = '__action_all_keys';
    const REQUEST_QUERY_MASS_ACTION = '__action_id';
    const REQUEST_QUERY_EXPORT = '__export_id';
    const REQUEST_QUERY_PAGE = '_page';
    const REQUEST_QUERY_LIMIT = '_limit';
    const REQUEST_QUERY_ORDER = '_order';
    const REQUEST_QUERY_TEMPLATE = '_template';
    const REQUEST_QUERY_RESET = '_reset';

    /**
     * @var \Symfony\Component\HttpFoundation\Session;
     */
    protected $session;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $routeParameters;

    /**
     * @var string
     */
    protected $routeUrl;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var \APY\DataGridBundle\Grid\Source\Source
     */
    protected $source;

    protected $totalCount;
    protected $page;
    protected $limit;
    protected $limits;

    /**
     * @var \APY\DataGridBundle\Grid\Columns|\APY\DataGridBundle\Grid\Column\Column[]
     */
    protected $columns;

    /**
     * @var \APY\DataGridBundle\Grid\Rows
     */
    protected $rows;

    /**
     * @var \APY\DataGridBundle\Grid\Action\MassAction[]
     */
    protected $massActions;

    /**
     * @var \APY\DataGridBundle\Grid\Action\RowAction[]
     */
    protected $rowActions;

    /**
     * @var boolean
     */
    protected $showFilters;

    /**
     * @var boolean
     */
    protected $showTitles;

    /**
     * @var array|object
     */
    protected $data = null;

    /**
     * @var string
     */
    protected $prefixTitle = '';

    /**
     * @var boolean
     */
    protected $persistence = false;

    /**
     * @var boolean
     */
    protected $newSession = false;

    /**
     * @var string
     */
    protected $noDataMessage;

    /**
     * @var string
     */
    protected $noResultMessage;

    /**
     * @var \APY\DataGridBundle\Grid\Export\Export[]
     */
    protected $exports;

    /**
     * @var boolean
     */
    protected $isReadyForExport = false;

    /**
     * @var \Response
     */
    protected $exportResponse;

    protected $maxResults = null;

    protected $items = array();

    // Lazy parameters for the default action column
    protected $actionsColumnSize;

    protected $actionsColumnSeparator;

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param \Source\Source $source Data Source
     */
    public function __construct($container, Source $source = null)
    {
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
        $this->exports = array();

        $this->routeParameters = $this->request->attributes->all();
        unset($this->routeParameters['_route']);
        unset($this->routeParameters['_controller']);
        unset($this->routeParameters['_route_params']);

        if (!is_null($source)) {
            $this->setSource($source);
        }
    }

    /**
     * Sets Source to the Grid
     *
     * @param $source
     *
     * @return Grid
     *
     * @throws \InvalidArgumentException
     */
    public function setSource(Source $source)
    {
        if(!is_null($this->source)) {
            throw new \InvalidArgumentException('Source can be set just once.');
        }

        $this->source = $source;

        $this->source->initialise($this->container);

        //get cols from source
        $this->source->getColumns($this->columns);

        //generate hash
        $this->createHash();

        // Persistence or reset - kill previous session
        if ((!$this->request->isXmlHttpRequest() && !$this->persistence && $this->request->headers->get('referer') != $this->request->getUriForPath($this->request->getPathInfo()))
         || !is_null($this->getDataFromContext(self::REQUEST_QUERY_RESET, true, false))) {
            $this->session->remove($this->getHash());
        }

        if (is_null($this->session->get($this->getHash()))) {
            $this->newSession = true;
        }

        //store column data
        $this->fetchAndSaveColumnData();

        //execute massActions
        $this->executeMassActions();

        //execute exports
        $this->executeExports();

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
     *
     * @return null|string
     */
    protected function getDataFromContext($column, $fromRequest = true, $fromSession = true)
    {
        $result = null;

        if ($fromSession && is_array($data = $this->session->get($this->getHash()))) {
            if (isset($data[$column])) {
                $result = $data[$column];
            }
        }

        if ($fromRequest && is_array($data = $this->request->get($this->getHash()))) {
            if (isset($data[$column])) {
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
    protected function fetchAndSaveColumnData()
    {
        $storage = $this->session->get($this->getHash());

        foreach ($this->columns as $column) {
            if ($column->isFilterable()) {
                $column->setData($this->getDataFromContext($column->getId()));
            } else {
                $column->setData($this->getDataFromContext($column->getId(), false));
            }

            if (($data = $column->getData()) !== null) {
                $storage[$column->getId()] = $data;
            } else {
                unset($storage[$column->getId()]);
            }
        }

        if (!empty($storage)) {
            $this->session->set($this->getHash(), $storage);
        }
    }

    /**
     * Set and Store Initial Grid data
     *
     * @return void
     */
    protected function fetchAndSaveGridData()
    {
        $storage = $this->session->get($this->getHash());
        //set internal data

        // Detection filtering
        $filtering = false;
        foreach ($this->columns as $column) {
            if (!is_null($this->getDataFromContext($column->getId(), true, false))) {
                $filtering = true;
                break;
            }
        }

        // Page
        // Set to the first page if this is a request of order, limit, mass action or filtering
        if (!is_null($this->getDataFromContext(self::REQUEST_QUERY_ORDER, true, false))
         || !is_null($this->getDataFromContext(self::REQUEST_QUERY_LIMIT, true, false))
         || !is_null($this->getDataFromContext(self::REQUEST_QUERY_MASS_ACTION, true, false))
         || $filtering) {
            $this->setPage(0);
        } elseif ($page = $this->getDataFromContext(self::REQUEST_QUERY_PAGE)) {
            $this->setPage($page);
        }

        $storage[self::REQUEST_QUERY_PAGE] = $this->getPage();

        // Order
        if (!is_null($order = $this->getDataFromContext(self::REQUEST_QUERY_ORDER))) {
            list($columnId, $columnOrder) = explode('|', $order);

            $column = $this->columns->getColumnById($columnId);
            if ($column->isSortable()) {
                $column->setOrder($columnOrder);

                $storage[self::REQUEST_QUERY_ORDER] = $order;
            }
        }

        // Limit
        if ($limit = $this->getDataFromContext(self::REQUEST_QUERY_LIMIT)) {
            if (isset($this->limits[$limit])) {
                $this->limit = $limit;

                $storage[self::REQUEST_QUERY_LIMIT] = $this->limit;
            }
        }

        // save data to sessions if needed
        if (!empty($storage)) {
            $this->session->set($this->getHash(), $storage);
        }
    }

    public function executeMassActions()
    {
        $actionId = $this->getDataFromContext(Grid::REQUEST_QUERY_MASS_ACTION, true, false);
        
        if ($actionId > -1) {
            if (array_key_exists($actionId, $this->massActions)) {
                $action = $this->massActions[$actionId];
                $actionAllKeys = (boolean)$this->getDataFromContext(Grid::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED, true, false);
                $actionKeys = $actionAllKeys == false ? (array) $this->getDataFromContext(MassActionColumn::ID, true, false) : array();

                if (is_callable($action->getCallback())) {
                    call_user_func($action->getCallback(), array_keys($actionKeys), $actionAllKeys, $this->session, $action->getParameters());
                } elseif (strpos($action->getCallback(), ':') !== false) {
                    $this->container->get('http_kernel')->forward($action->getCallback(), array_merge(array('primaryKeys' => array_keys($actionKeys), 'allPrimaryKeys' => $actionAllKeys), $action->getParameters()));
                } else {
                    throw new \RuntimeException(sprintf('Callback %s is not callable or Controller action', $action->getCallback()));
                }
            } else {
                throw new \OutOfBoundsException(sprintf('Action %s is not defined.', $actionId));
            }
        }
    }

    public function executeExports()
    {
        $exportId = $this->getDataFromContext(Grid::REQUEST_QUERY_EXPORT, true, false);

        if ($exportId > -1) {
            if (array_key_exists($exportId, $this->exports)) {
                $this->isReadyForExport = true;

                $this->page = 0;
                $this->limit = 0;
                $this->prepare();

                $export = $this->exports[$exportId];
                $export->setContainer($this->container);
                $export->computeData($this);

                $this->exportResponse = $export->getResponse();
            } else {
                throw new \OutOfBoundsException(sprintf('Export %s is not defined.', $exportId));
            }
        }
    }

    public function getExportResponse()
    {
        return $this->exportResponse;
    }

    /**
     * Prepare Grid for Drawing
     *
     * @return Grid
     */
    public function prepare()
    {
        if ($this->source->isDataLoaded()) {
            $this->rows = $this->source->executeFromData($this->columns->getIterator(true), $this->page, $this->limit, $this->maxResults);
        } else {
            $this->rows = $this->source->execute($this->columns->getIterator(true), $this->page, $this->limit, $this->maxResults);
        }

        if(!$this->rows instanceof Rows) {
            throw new \Exception('Source have to return Rows object.');
        }

        if (count($this->rows) == 0 && $this->page > 0){
            $this->page = 0;
            $this->prepare();

            return $this;
        }

        //add row actions column
        if (count($this->rowActions) > 0) {
            foreach ($this->rowActions as $column => $rowActions) {
                if ($rowAction = $this->columns->hasColumnById($column, true)) {
                    $rowAction->setRowActions($rowActions);
                } else {
                    $actionColumn = new ActionsColumn($column, 'Actions', $rowActions);
                    if ($this->actionsColumnSize>-1) {
                        $actionColumn->setSize($this->actionsColumnSize);
                    }

                    if (isset($this->actionsColumnSeparator)) {
                        $actionColumn->setSeparator($this->actionsColumnSeparator);
                    }
                    $this->columns->addColumn($actionColumn);
                }
            }
        }

        //add mass actions column
        if (count($this->massActions) > 0) {
            $this->columns->addColumn(new MassActionColumn(), 1);
        }

        $primaryColumnId = $this->columns->getPrimaryColumn()->getId();

        foreach ($this->rows as $row) {
            $row->setPrimaryField($primaryColumnId);
        }

        //@todo refactor autohide titles when no title is set
        if (!$this->showTitles) {
            $this->showTitles = false;
            foreach ($this->columns as $column) {
                if (!$this->showTitles) {
                    break;
                }

                if ($column->getTitle() != '') {
                    $this->showTitles = true;

                    break;
                }
            }
        }

        //get size
        if ($this->source->isDataLoaded()) {
            $this->source->populateSelectFiltersFromData($this->columns);
            $this->totalCount = $this->source->getTotalCountFromData($this->maxResults);
        } else {
            $this->source->populateSelectFilters($this->columns);
            $this->totalCount = $this->source->getTotalCount($this->maxResults);
        }

        if(!is_int($this->totalCount)) {
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
    public function addColumn($column, $position = 0)
    {
        if ($this->source === null) {
            throw new \InvalidArgumentException('addColumns needs the grid source set beforehand');
        }

        $this->columns->addColumn($column, $position);

        return $this;
    }

    /**
     * Get a column by its identifier
     *
     * @param $columnId
     * @return Column
     */
    public function getColumn($columnId)
    {
        return $this->columns->getColumnById($columnId);
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
    public function setColumns(Columns $columns)
    {
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
    public function addMassAction(MassActionInterface $action)
    {
        if ($this->source instanceof Source) {
            throw new \InvalidArgumentException('Mass actions have to be defined before the source.');
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
    public function addRowAction(RowActionInterface $action)
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
     * Adds template
     *
     * @param Export $template
     * @return Grid
     */
    public function setTemplate($template)
    {
        if ($template !== null) {
            $storage = $this->session->get($this->getHash());

            if ($template instanceof \Twig_Template) {
                $template = $template->getTemplateName();
            } elseif (!is_string($template) && is_null($template)) {
                throw new \Exception('Unable to load template');
            }

            $storage[self::REQUEST_QUERY_TEMPLATE] = $template;

            $this->session->set($this->getHash(), $storage);
        }

        return $this;
    }

    /**
     * Returns template
     *
     * @return Twig_Template
     */
    public function getTemplate()
    {
        return $this->getDataFromContext(self::REQUEST_QUERY_TEMPLATE, false, true);
    }

    /**
     * Adds Export
     *
     * @param Export $export
     * @return Grid
     */
    public function addExport($export)
    {
        if ($this->source instanceof Source) {
            throw new \InvalidArgumentException('Exports have to be defined before the source.');
        }

        $this->exports[] = $export;

        return $this;
    }

    /**
     * Returns Export
     *
     * @return Export[]
     */
    public function getExports()
    {
        return $this->exports;
    }

    /**
     * Sets Route Parameters
     *
     * @param string $parameter
     * @param mixed $value
     * @return Grid
     */
    public function setRouteParameter($parameter, $value)
    {
        $this->routeParameters[$parameter] = $value;

        return $this;
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
        if ($this->routeUrl == '') {
            $this->routeUrl = $this->router->generate($this->request->get('_route'), $this->getRouteParameters());
        }

        return $this->routeUrl;
    }

    public function isReadyForRedirect()
    {
        $data = $this->request->get($this->getHash());

        return !empty($data);
    }

    public function isReadyForExport()
    {
        return $this->isReadyForExport;
    }

    public function createHash()
    {
        $this->hash = 'grid_'. (empty($this->id) ? md5($this->request->get('_controller').$this->columns->getHash().$this->source->getHash()) : $this->getId());
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set default value for filters
     *
     * @param array Hash of columnName => initValue
     * @return Grid
     */
    public function setDefaultFilters(array $filters)
    {
        if ($this->source === null) {
            throw new \InvalidArgumentException('setDefaultfilters needs the grid source set beforehand');
        }

        if ($this->newSession) {
            $storage = $this->session->get($this->getHash());

            foreach ($filters as $columnId => $ColumnValue) {
                if (is_array($ColumnValue)){
                    $value = $ColumnValue;
                } else {
                    $value = array('from' => $ColumnValue);
                }

                $this->columns->getColumnById($columnId)->setData($value);

                $storage[$columnId] = $value;
            }

            $this->session->set($this->getHash(), $storage);
        }

        return $this;
    }

    /**
     * Set the default grid order
     *
     * @param array Hash of columnName => initValue
     * @return Grid
     */
    public function setDefaultOrder($columnId, $order)
    {
        if ($this->source === null) {
            throw new \InvalidArgumentException('Default order have to be define after the grid source');
        }

        if ($this->newSession) {
            $storage = $this->session->get($this->getHash());

            $this->columns->getColumnById($columnId)->setOrder($order);

            $storage[self::REQUEST_QUERY_ORDER] = "$columnId|$order";

            $this->session->set($this->getHash(), $storage);
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
     * Sets persistence
     *
     * @param $persistence
     * @return Grid
     */
    public function setPersistence($persistence)
    {
        $this->persistence = $persistence;

        return $this;
    }

    /**
     * Returns persistence
     *
     * @return boolean
     */
    public function getPersistence()
    {
        return $this->persistence;
    }


    /**
     * Sets Limits
     *
     * @param mixed $limits e.g. array(10 => '10', 1000 => '1000')
     * @return Grid
     */
    public function setLimits($limits)
    {
        if ($this->source instanceof Source) {
            throw new \InvalidArgumentException('Limits have to be define before the grid source');
        }

        if (is_array($limits)) {
            if ( (int) key($limits) === 0) {
                $this->limits = array_combine($limits, $limits);
                $this->limit = current($this->limits);
            } else {
                $this->limits = $limits;
                $this->limit = (int) key($this->limits);
            }
        } elseif (is_int($limits)) {
            $this->limits = array($limits => (string)$limits);
            $this->limit = $limits;
        } else {
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
    public function setDefaultPage($page)
    {
        $this->setPage((int)$page - 1);

        return $this;
    }

    /**
     * Sets current Page (internal)
     *
     * @param $page
     * @return Grid
     */
    public function setPage($page)
    {
        if ((int)$page >= 0) {
            $this->page = (int)$page;
        } else {
            throw new \InvalidArgumentException('Page must be a positive number');
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

    public function setMaxResults($maxResults = null)
    {
        if ((is_int($maxResults) && $maxResults < 0) && $maxResults !== null) {
           throw new \InvalidArgumentException('Max results must be a positive number.');
        }

        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * Return true if the grid is filtered
     *
     * @return boolean
     */
    public function isFiltered()
    {
        foreach ($this->columns as $column) {
            if ($column->isFiltered()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return true if if title panel is visible in template - internal helper
     *
     * @return bool
     */
    public function isTitleSectionVisible()
    {
        if ($this->showTitles == true) {
            foreach ($this->columns as $column) {
                if ($column->getTitle() != '') {
                    return true;
                }
            }
        }
    }

    /**
     * Return true if filter panel is visible in template - internal helper
     *
     * @return bool
     */
    public function isFilterSectionVisible()
    {
        if ($this->showFilters == true) {
            foreach ($this->columns as $column) {
                if ($column->isFilterable() && $column->getType() != 'massaction' && $column->getType() != 'actions') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return true if pager panel is visible in template - internal helper
     *
     * @return bool return true if pager is visible
     */
    public function isPagerSectionVisible()
    {
        $limits = sizeof($this->getLimits());

        return $limits > 1 || ($limits <= 1 && $this->getCurrentLimit() < $this->totalCount);
    }

    /**
     * Hides Filters Panel
     *
     * @return Grid
     */
    public function hideFilters()
    {
        $this->showFilters = false;

        return $this;
    }

    /**
     * Hides Titles panel
     *
     * @return Grid
     */
    public function hideTitles()
    {
        $this->showTitles = false;

        return $this;
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

        return $this;
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
     * Set the no data message
     *
     * @param $noDataMessage string
     */
    public function setNoDataMessage($noDataMessage)
    {
        $this->noDataMessage = $noDataMessage;

        return $this;
    }

    /**
     * Get the no data message
     *
     * @return string
     */
    public function getNoDataMessage()
    {
        return $this->noDataMessage;
    }

    /**
     * Set the no result message
     *
     * @param $noResultMessage string
     */
    public function setNoResultMessage($noResultMessage)
    {
        $this->noResultMessage = $noResultMessage;

        return $this;
    }

    /**
     * Get the no result message
     *
     * @return string
     */
    public function getNoResultMessage()
    {
        return $this->noResultMessage;
    }

    /**
     * Sets a list of columns to hide when the grid is output
     * @param array $columnIds
     */
    public function setHiddenColumns(array $columnIds)
    {
        if($this->source === null) {
            throw new \InvalidArgumentException('Hiddenc olumns have to be define after the grid source');
        }

        if(empty($columnIds)) {
            throw new \InvalidArgumentException('setHiddenColumns needs an array of column ids');
        }

        foreach ($columnIds as $columnId) {
            $this->columns->getColumnById($columnId)->setVisible(false);
        }

        return $this;
    }

    /**
     * Sets a list of columns to show when the grid is output
     * It acts as a mask; Other columns will be set as hidden
     * @param array $columnIds
     */
    public function setVisibleColumns(array $columnIds)
    {
        if ($this->source === null) {
            throw new \InvalidArgumentException('Visible columns have to be define after the grid source');
        }

        $columnNames = array();
        foreach ($this->columns as $column) {
            $columnNames[] = $column->getId();
        }

        $this->setHiddenColumns(array_diff($columnNames, $columnIds));

        return $this;
    }

    /**
     * Sets on the visiblilty of columns
     * @param string|array $columnIds
     */
    public function showColumns($columnIds)
    {
        if ($this->source === null) {
            throw new \InvalidArgumentException('showColumns needs the grid source set beforehand');
        }
        $columnIds = (array) $columnIds;

        foreach ($columnIds as $columnId) {
            $this->columns->getColumnById($columnId)->setVisible(true);
        }

        return $this;
    }

    /**
     * Sets off the visiblilty of columns
     * @param string|array $columnIds
     */
    public function hideColumns($columnIds)
    {
        if ($this->source === null) {
            throw new \InvalidArgumentException('hideColumns needs the grid source set beforehand');
        }

        $columnIds = (array) $columnIds;

        foreach ($columnIds as $columnId) {
            $this->columns->getColumnById($columnId)->setVisible(false);
        }

        return $this;
    }

    public function setActionsColumnSize($size)
    {
        $this->actionsColumnSize = $size;

        return $this;
    }

    public function setActionsColumnSeparator($separator)
    {
        $this->actionsColumnSeparator = $separator;

        return $this;
    }

    /**
     * Default delete action
     *
     * @param $ids
     */
    public function deleteAction($ids, $actionAllKeys)
    {
        $this->source->delete($ids, $actionAllKeys);
    }

    /**
     * Get a clone of the grid
     */
    public function __clone()
    {
        // clone all objects
        $this->columns = clone $this->columns;
    }

    /****** HELPER ******/

    /**
     * Redirects or Renders a view - helper function
     *
     * @param string|array $param1 The view name or an array of parameters to pass to the view
     * @param string|array $param1 The view name or an array of parameters to pass to the view
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function getGridResponse($param1 = null, $param2 = null, Response $response = null)
    {
        if ($this->isReadyForRedirect()) {
            if ($this->isReadyForExport()) {
                return $this->getExportResponse();
            }

            return new RedirectResponse($this->getRouteUrl());
        } else {
            if (is_array($param1) || $param1 === null) {
                $parameters = (array) $param1;
                $view = $param2;
            } else {
                $parameters = (array) $param2;
                $view = $param1;
            }

            $parameters = array_merge(array('grid' => $this), $parameters);

            if (is_null($view)) {
                return $parameters;
            } else {
                return $this->container->get('templating')->renderResponse($view, $parameters, $response);
            }
        }
    }
}

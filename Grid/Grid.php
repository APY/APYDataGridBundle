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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

use APY\DataGridBundle\Grid\Action\MassActionInterface;
use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\MassActionColumn;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Source\Source;
use APY\DataGridBundle\Grid\Export\ExportInterface;

class Grid
{
    const REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED = '__action_all_keys';
    const REQUEST_QUERY_MASS_ACTION = '__action_id';
    const REQUEST_QUERY_EXPORT = '__export_id';
    const REQUEST_QUERY_TWEAK = '__tweak_id';
    const REQUEST_QUERY_PAGE = '_page';
    const REQUEST_QUERY_LIMIT = '_limit';
    const REQUEST_QUERY_ORDER = '_order';
    const REQUEST_QUERY_TEMPLATE = '_template';
    const REQUEST_QUERY_RESET = '_reset';

    /**
     * @var \Symfony\Component\DependencyInjection\Container
     */
    protected $container;

    /**
     * @var \Symfony\Component\Routing\Router
     */
    protected $router;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session;
     */
    protected $session;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
    protected $securityContext;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $hash;

    /**
     * @var string
     */
    protected $routeUrl;

    /**
     * @var array
     */
    protected $routeParameters;

    /**
     * @var \APY\DataGridBundle\Grid\Source\Source
     */
    protected $source;

    /**
     * @var boolean
     */
     protected $prepared = false;

    /**
     * @var int
     */
    protected $totalCount;

    /**
     * @var int
     */
    protected $page = 0;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var array
     */
    protected $limits = array();

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
    protected $massActions = array();

    /**
     * @var \APY\DataGridBundle\Grid\Action\RowAction[]
     */
    protected $rowActions = array();

    /**
     * @var boolean
     */
    protected $showFilters = true;

    /**
     * @var boolean
     */
    protected $showTitles = true;

    /**
     * @var array|object request
     */
    protected $requestData;

    /**
     * @var array|object session
     */
    protected $sessionData;

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
    protected $exports = array();

    /**
     * @var boolean
     */
    protected $redirect = null;

    /**
     * @var boolean
     */
    protected $isReadyForExport = false;

    /**
     * @var Response
     */
    protected $exportResponse;

    /**
     * @var Response
     */
    protected $massActionResponse;

    /**
     * @var int
     */
    protected $maxResults;

    /**
     * @var array
     */
    protected $items = array();

    /**
     * Data junction of the grid
     *
     * @var int
     */
    protected $dataJunction = Column::DATA_CONJUNCTION;

    /**
     * Permanent filters
     *
     * @var array
     */
    protected $permanentFilters = array();

    /**
     * Default filters
     *
     * @var array
     */
    protected $defaultFilters = array();

    /**
     * Default order (e.g. my_column_id|asc)
     *
     * @var string
     */
    protected $defaultOrder;

    /**
     * Default limit
     *
     * @var integer
     */
    protected $defaultLimit;

    /**
     * Default page
     *
     * @var int
     */
    protected $defaultPage;

    /**
     * Tweaks
     *
     * @var array
     */
    protected $tweaks = array();

    /**
     * Default Tweak
     *
     * @var string
     */
    protected $defaultTweak;
    
    /**
     * Filters in session
     * @var array
     */
    protected $sessionFilters;

    // Lazy parameters
    protected $lazyAddColumn = array();
    protected $lazyHiddenColumns = array();
    protected $lazyVisibleColumns = array();
    protected $lazyHideShowColumns = array();

    // Lazy parameters for the action column
    protected $actionsColumnSize;
    protected $actionsColumnTitle;

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param string $id set if you are using more then one grid inside controller
     */
    public function __construct($container, $id = '')
    {
        $this->container = $container;

        $this->router = $container->get('router');
        $this->request = $container->get('request');
        $this->session = $this->request->getSession();
        $this->securityContext = $container->get('security.context');

        $this->id = $id;

        $this->columns = new Columns($this->securityContext);

        $this->routeParameters = $this->request->attributes->all();
        foreach (array_keys($this->routeParameters) as $key) {
            if (substr($key, 0, 1) == '_') {
                unset($this->routeParameters[$key]);
            }
        }
    }

    /**
     * Sets Source to the Grid
     *
     * @param $source
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setSource(Source $source)
    {
        if ($this->source !== null) {
            throw new \InvalidArgumentException('The source of the grid is already set.');
        }

        $this->source = $source;

        $this->source->initialise($this->container);

        // Get columns from the source
        $this->source->getColumns($this->columns);

        return $this;
    }

    public function getSource()
    {
        return $this->source;
    }

    /**
     * Handle the grid redirection, export, etc..
     */
    public function isReadyForRedirect()
    {
        if ($this->source === null) {
            throw new \Exception('The source of the grid is not set.');
        }

        if ($this->redirect !== null) {
            return $this->redirect;
        }

        $this->createHash();

        $this->requestData = (array) $this->request->get($this->hash);

        $this->processPersistence();

        $this->sessionData = (array) $this->session->get($this->hash);

        $this->processLazyParameters();

        // isReadyForRedirect ?
        if (!empty($this->requestData)) {
            $this->processRequestData();

            $this->redirect = true;
        }

        if ($this->redirect === null || ($this->request->isXmlHttpRequest() && !$this->isReadyForExport)) {
            if ($this->newSession) {
                $this->setDefaultSessionData();
            }

            $this->processPermanentFilters();

            //Configures the grid with the data read from the session.
            $this->processSessionData();

            $this->prepare();

            $this->redirect = false;
        }

        return $this->redirect;
    }

    protected function getCurrentUri()
    {
        return $this->request->getScheme().'://'.$this->request->getHttpHost().$this->request->getBaseUrl().$this->request->getPathInfo();
    }

    protected function processPersistence()
    {
        $referer = strtok($this->request->headers->get('referer'), '?');

        // Persistence or reset - kill previous session
        if ((!$this->request->isXmlHttpRequest() && !$this->persistence && $referer != $this->getCurrentUri())
         || isset($this->requestData[self::REQUEST_QUERY_RESET])) {
            $this->session->remove($this->hash);
        }

        if ($this->session->get($this->hash) === null) {
            $this->newSession = true;
        }
    }

    protected function processLazyParameters()
    {
        // Additional columns
        foreach ($this->lazyAddColumn as $column) {
            $this->columns->addColumn($column['column'], $column['position']);
        }

        // Hidden columns
        foreach ($this->lazyHiddenColumns as $columnId) {
            $this->columns->getColumnById($columnId)->setVisible(false);
        }

        // Visible columns
        if (!empty($this->lazyVisibleColumns)) {
            $columnNames = array();
            foreach ($this->columns as $column) {
                $columnNames[] = $column->getId();
            }

            foreach (array_diff($columnNames, $this->lazyVisibleColumns) as $columnId) {
                $this->columns->getColumnById($columnId)->setVisible(false);
            }
        }

        // Hide and Show columns
        foreach ($this->lazyHideShowColumns as $columnId => $visible) {
            $this->columns->getColumnById($columnId)->setVisible($visible);
        }
    }

    /**
     * Reads data from the request and write this data to the session.
     */
    protected function processRequestData()
    {
        $this->processMassActions($this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION));

        if ($this->processExports($this->getFromRequest(Grid::REQUEST_QUERY_EXPORT))
            || $this->processTweaks($this->getFromRequest(self::REQUEST_QUERY_TWEAK))) {
            return;
        }

        $filtering = $this->processRequestFilters();

        $this->processPage($this->getFromRequest(self::REQUEST_QUERY_PAGE), $filtering);

        $this->processOrder($this->getFromRequest(self::REQUEST_QUERY_ORDER));

        $this->processLimit($this->getFromRequest(self::REQUEST_QUERY_LIMIT));

        $this->saveSession();
    }

    /**
     * Process mass actions
     *
     * @param int $actionId
     *
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     */
    protected function processMassActions($actionId)
    {
        if ($actionId > -1 && '' !== $actionId) {
            if (array_key_exists($actionId, $this->massActions)) {
                $action = $this->massActions[$actionId];
                $actionAllKeys = (boolean)$this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED);
                $actionKeys = $actionAllKeys == false ? (array) $this->getFromRequest(MassActionColumn::ID) : array();

                $this->processSessionData();
                if ($actionAllKeys) {
                    $this->page = 0;
                    $this->limit = 0;
                }
                $this->prepare();

                if (is_callable($action->getCallback())) {
                    $this->massActionResponse = call_user_func($action->getCallback(), array_keys($actionKeys), $actionAllKeys, $this->session, $action->getParameters());
                } elseif (strpos($action->getCallback(), ':') !== false) {
                    $path = array_merge(
                        array(
                            'primaryKeys'    => array_keys($actionKeys),
                            'allPrimaryKeys' => $actionAllKeys,
                            '_controller'    => $action->getCallback()
                        ),
                        $action->getParameters()
                    );

                    $subRequest = $this->container->get('request')->duplicate(array(), null, $path);

                    $this->massActionResponse = $this->container->get('http_kernel')->handle($subRequest, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST);
                } else {
                    throw new \RuntimeException(sprintf('Callback %s is not callable or Controller action', $action->getCallback()));
                }
            } else {
                throw new \OutOfBoundsException(sprintf('Action %s is not defined.', $actionId));
            }
        }
    }

    /**
     * Process exports
     *
     * @param int $exportId
     *
     * @return boolean
     *
     * @throws \OutOfBoundsException
     */
    protected function processExports($exportId)
    {
        if ($exportId > -1 && '' !== $exportId) {
            if (array_key_exists($exportId, $this->exports)) {
                $this->isReadyForExport = true;

                $this->processSessionData();
                $this->page = 0;
                $this->limit = 0;
                $this->prepare();

                $export = $this->exports[$exportId];
                if ($export instanceof ContainerAwareInterface) {
                    $export->setContainer($this->container);
                }
                $export->computeData($this);

                $this->exportResponse = $export->getResponse();

                return true;
            } else {
                throw new \OutOfBoundsException(sprintf('Export %s is not defined.', $exportId));
            }
        }

        return false;
    }

    /**
     * Process tweaks
     *
     * @param int $tweakId
     *
     * @return boolean
     *
     * @throws \OutOfBoundsException
     */
    protected function processTweaks($tweakId)
    {
        if ($tweakId != null) {
            if (array_key_exists($tweakId, $this->tweaks)) {
                $tweak = $this->tweaks[$tweakId];
                $saveAsActive = false;

                if (isset($tweak['reset'])) {
                    $this->sessionData = array();
                    $this->session->remove($this->hash);
                }

                if (isset($tweak['filters'])) {
                    $this->defaultFilters = array();
                    $this->setDefaultFilters($tweak['filters']);
                    $this->processDefaultFilters();
                    $saveAsActive = true;
                }

                if (isset($tweak['order'])) {
                    $this->processOrder($tweak['order']);
                    $saveAsActive = true;
                }

                if (isset($tweak['massAction'])) {
                    $this->processMassActions($tweak['massAction']);
                }

                if (isset($tweak['page'])) {
                    $this->processPage($tweak['page']);
                    $saveAsActive = true;
                }

                if (isset($tweak['limit'])) {
                    $this->processLimit($tweak['limit']);
                    $saveAsActive = true;
                }

                if (isset($tweak['export'])) {
                    $this->processExports($tweak['export']);
                }

                if ($saveAsActive) {
                    $activeTweaks = $this->getActiveTweaks();
                    $activeTweaks[$tweak['group']] = $tweakId;
                    $this->set('tweaks', $activeTweaks);
                }

                if (isset($tweak['removeActiveTweaksGroups'])) {
                    $removeActiveTweaksGroups = (array) $tweak['removeActiveTweaksGroups'];
                    $activeTweaks = $this->getActiveTweaks();
                    foreach ($removeActiveTweaksGroups as $id) {
                        if (isset($activeTweaks[$id])) {
                            unset($activeTweaks[$id]);
                        }
                    }

                    $this->set('tweaks', $activeTweaks);
                }

                if (isset($tweak['removeActiveTweaks'])) {
                    $removeActiveTweaks = (array) $tweak['removeActiveTweaks'];
                    $activeTweaks = $this->getActiveTweaks();
                    foreach ($removeActiveTweaks as $id) {
                        if (array_key_exists($id, $this->tweaks)) {
                            if (isset($activeTweaks[$this->tweaks[$id]['group']])) {
                                unset($activeTweaks[$this->tweaks[$id]['group']]);
                            }
                        }
                    }

                    $this->set('tweaks', $activeTweaks);
                }

                if (isset($tweak['addActiveTweaks'])) {
                    $addActiveTweaks = (array) $tweak['addActiveTweaks'];
                    $activeTweaks = $this->getActiveTweaks();
                    foreach ($addActiveTweaks as $id) {
                        if (array_key_exists($id, $this->tweaks)) {
                            $activeTweaks[$this->tweaks[$id]['group']] = $id;
                        }
                    }

                    $this->set('tweaks', $activeTweaks);
                }

                $this->saveSession();

                return true;
            } else {
                throw new \OutOfBoundsException(sprintf('Tweak %s is not defined.', $tweakId));
            }
        }

        return false;
    }

    protected function processRequestFilters()
    {
        $filtering = false;
        foreach ($this->columns as $column) {
            if ($column->isFilterable()) {
                $ColumnId = $column->getId();

                // Get data from request
                $data = $this->getFromRequest($ColumnId);

                // Store in the session
                $this->set($ColumnId, $data);

                // Filtering ?
                if (!$filtering && $data !== null) {
                    $filtering = true;
                }
            }
        }

        return $filtering;
    }

    protected function processPage($page, $filtering = false)
    {
        // Set to the first page if this is a request of order, limit, mass action or filtering
        if ($this->getFromRequest(self::REQUEST_QUERY_ORDER) !== null
         || $this->getFromRequest(self::REQUEST_QUERY_LIMIT) !== null
         || $this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION) !== null
         || $filtering) {
            $this->set(self::REQUEST_QUERY_PAGE, 0);
        } else {
            $this->set(self::REQUEST_QUERY_PAGE, $page);
        }
    }

    protected function processOrder($order)
    {
        if ($order !== null) {
            list($columnId, $columnOrder) = explode('|', $order);

            $column = $this->columns->getColumnById($columnId);
            if ($column->isSortable() && in_array(strtolower($columnOrder), array('asc', 'desc'))) {
                $this->set(self::REQUEST_QUERY_ORDER, $order);
            }
        }
    }

    protected function processLimit($limit)
    {
        if (isset($this->limits[$limit])) {
            $this->set(self::REQUEST_QUERY_LIMIT, $limit);
        }
    }

    protected function setDefaultSessionData()
    {
        // Default filters
        $this->processDefaultFilters();

        // Default page
        if ($this->defaultPage !== null) {
            if ((int) $this->defaultPage >= 0) {
                $this->set(self::REQUEST_QUERY_PAGE, $this->defaultPage);
            } else {
                throw new \InvalidArgumentException('Page must be a positive number');
            }
        }

        // Default order
        if ($this->defaultOrder !== null) {
            list($columnId, $columnOrder) = explode('|', $this->defaultOrder);

            $this->columns->getColumnById($columnId);
            if (in_array(strtolower($columnOrder), array('asc', 'desc'))) {
                $this->set(self::REQUEST_QUERY_ORDER, $this->defaultOrder);
            } else {
                throw new \InvalidArgumentException($columnOrder . ' is not a valid order.');
            }
        }

        if ($this->defaultLimit !== null) {
            if ((int) $this->defaultLimit >= 0) {
                if (isset($this->limits[$this->defaultLimit])) {
                    $this->set(self::REQUEST_QUERY_LIMIT, $this->defaultLimit);
                } else {
                    throw new \InvalidArgumentException(sprintf('Limit %s is not defined in limits.', $this->defaultLimit));
                }
            } else {
                throw new \InvalidArgumentException('Limit must be a positive number');
            }
        }

        // Default tweak
        if ($this->defaultTweak !== null) {
            $this->processTweaks($this->defaultTweak);
        }
        $this->saveSession();
    }

     /**
     * Store permanent filters to the session and disable the filter capability for the column if there are permanent filters
     */
    protected function processFilters($permanent = true)
    {
        foreach (($permanent ? $this->permanentFilters : $this->defaultFilters) as $columnId => $value) {
            /* @var $column Column */
            $column = $this->columns->getColumnById($columnId);

            if ($permanent) {
                // Disable the filter capability for the column
                $column->setFilterable(false);
            }

            // Convert simple value
            if (!is_array($value) || !is_string(key($value))) {
                $value = array('from' => $value);
            }

            // Convert boolean value
            if (isset($value['from']) && is_bool($value['from'])) {
                $value['from'] = $value['from'] ? '1' : '0';
            }

            // Convert simple value with select filter
            if ($column->getFilterType() === 'select') {
                if (isset($value['from']) && !is_array($value['from'])) {
                    $value['from'] = array($value['from']);
                }

                if (isset($value['to']) && !is_array($value['to'])) {
                    $value['to'] = array($value['to']);
                }
            }

            // Store in the session
            $this->set($columnId, $value);
        }
    }

    protected function processPermanentFilters()
    {
        $this->processFilters();
        $this->saveSession();
    }

    protected function processDefaultFilters()
    {
        $this->processFilters(false);
    }

    /**
     * Configures the grid with the data read from the session.
     */
    protected function processSessionData()
    {
        // Filters
        foreach ($this->columns as $column) {
            if (($data = $this->get($column->getId())) !== null) {
                $column->setData($data);
            }
        }

        // Page
        if (($page = $this->get(self::REQUEST_QUERY_PAGE)) !== null) {
            $this->setPage($page);
        } else {
            $this->setPage(0);
        }

        // Order
        if (($order = $this->get(self::REQUEST_QUERY_ORDER)) !== null) {
            list($columnId, $columnOrder) = explode('|', $order);

            $this->columns->getColumnById($columnId)->setOrder($columnOrder);
        }

        // Limit
        if (($limit = $this->get(self::REQUEST_QUERY_LIMIT)) !== null) {
            $this->limit = $limit;
        } else {
            $this->limit = key($this->limits);
        }
    }

    /**
     * Prepare Grid for Drawing
     *
     * @return self
     *
     * @throws \Exception
     */
    protected function prepare()
    {
        if ($this->prepared) {
            return $this;
        }

        if ($this->source->isDataLoaded()) {
            $this->rows = $this->source->executeFromData($this->columns->getIterator(true), $this->page, $this->limit, $this->maxResults);
        } else {
            $this->rows = $this->source->execute($this->columns->getIterator(true), $this->page, $this->limit, $this->maxResults, $this->dataJunction);
        }

        if (!$this->rows instanceof Rows) {
            throw new \Exception('Source have to return Rows object.');
        }

        if (count($this->rows) == 0 && $this->page > 0) {
            $this->page = 0;
            $this->prepare();

            return $this;
        }

        //add row actions column
        if (count($this->rowActions) > 0) {
            foreach ($this->rowActions as $column => $rowActions) {
                if (($actionColumn = $this->columns->hasColumnById($column, true))) {
                    $actionColumn->setRowActions($rowActions);
                } else {
                    $actionColumn = new ActionsColumn($column, $this->actionsColumnTitle, $rowActions);
                    if ($this->actionsColumnSize > -1) {
                        $actionColumn->setSize($this->actionsColumnSize);
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

        if (!is_int($this->totalCount)) {
            throw new \Exception(sprintf('Source function getTotalCount need to return integer result, returned: %s', gettype($this->totalCount)));
        }

        $this->prepared = true;

        return $this;
    }

    /**
     * Reads data from the request.
     *
     * @param string $key A unique key identifying the data
     *
     * @return mixed Data associated with the key or null if the key is not found
     */
    protected function getFromRequest($key)
    {
        if (isset($this->requestData[$key])) {
            return $this->requestData[$key];
        }
    }

    /**
     * Reads data from the session.
     *
     * @param string $key A unique key identifying your data
     *
     * @return mixed Data associated with the key or null if the key is not found
     */
    protected function get($key)
    {
        if (isset($this->sessionData[$key])) {
            return $this->sessionData[$key];
        }
    }

    /**
     * Writes data to the session.
     *
     * @param string $key A unique key identifying the data
     * @param mixed $data Data associated with the key
     */
    protected function set($key, $data)
    {
        // Only the filters values are removed from the session
        if (isset($data['from']) && ((is_string($data['from']) && $data['from'] === '') || (is_array($data['from']) && $data['from'][0] === ''))) {
            if (array_key_exists($key, $this->sessionData)) {
                unset($this->sessionData[$key]);
            }
        } elseif ($data !== null) {
            $this->sessionData[$key] = $data;
        }
    }

    protected function saveSession()
    {
        if (!empty($this->sessionData)) {
            $this->session->set($this->hash, $this->sessionData);
        }
    }

    protected function createHash()
    {
        $this->hash = 'grid_'. (empty($this->id) ? md5($this->request->get('_controller').$this->columns->getHash().$this->source->getHash()) : $this->getId());
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Adds custom column to the grid
     *
     * @param $column
     * @param int $position
     *
     * @return self
     */
    public function addColumn($column, $position = 0)
    {
        $this->lazyAddColumn[] = array('column' => $column, 'position' => $position);

        return $this;
    }

    /**
     * Get a column by its identifier
     *
     * @param $columnId
     *
     * @return Column
     */
    public function getColumn($columnId)
    {
        foreach ($this->lazyAddColumn as $column) {
            if ($column['column']->getId() == $columnId) {
                return $column['column'];
            }
        }

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
     * Returns true if column exists in columns and lazyAddColumn properties
     *
     * @param $columnId
     * @return boolean
     */
    public function hasColumn($columnId)
    {
        foreach ($this->lazyAddColumn as $column) {
            if ($column['column']->getId() == $columnId) {
                return true;
            }
        }

        return $this->columns->hasColumnById($columnId);
    }

    /**
     * Sets Array of Columns to the grid
     *
     * @param $columns
     *
     * @return self
     */
    public function setColumns(Columns $columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets order of Columns passing an array of column ids
     * If the list of ids is uncomplete, the remaining columns will be
     * placed after
     *
     * @param array $columnIds
     * @param boolean $keepOtherColumns
     *
     * @return self
     */
    public function setColumnsOrder(array $columnIds, $keepOtherColumns = true)
    {
        $this->columns->setColumnsOrder($columnIds, $keepOtherColumns);

        return $this;
    }

    /**
     * Adds Mass Action
     *
     * @param Action\MassActionInterface $action
     *
     * @return self
     */
    public function addMassAction(MassActionInterface $action)
    {
        if ($action->getRole() === null || $this->securityContext->isGranted($action->getRole())) {
            $this->massActions[] = $action;
        }

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
     * Add a tweak
     * @param string title title of the tweak
     * @param array $tweak array('filters' => array, 'order' => 'colomunId|order', 'page' => integer, 'limit' => integer, 'export' => integer, 'massAction' => integer)
     * @param string id id of the tweak matching the regex ^[0-9a-zA-Z_\+-]+
     * @param string group group of the tweak
     *
     * @return self
     */
    public function addTweak($title, array $tweak, $id = null, $group = null)
    {
        if ($id !== null && !preg_match('/^[0-9a-zA-Z_\+-]+$/', $id)) {
            throw new \InvalidArgumentException(sprintf('Tweak id "%s" is malformed. The id have to match this regex ^[0-9a-zA-Z_\+-]+', $id));
        }

        $tweak = array_merge(array('id' => $id, 'title' => $title, 'group' => $group), $tweak);
        if (isset($id)) {
            $this->tweaks[$id] = $tweak;
        } else {
            $this->tweaks[] = $tweak;
        }

        return $this;
    }

    /**
     * Returns tweaks
     * Add the url of the tweak
     *
     * @return array
     */
    public function getTweaks()
    {
        $separator =  strpos($this->getRouteUrl(), '?') ? '&' : '?';
        $url = $this->getRouteUrl().$separator.$this->getHash().'['.Grid::REQUEST_QUERY_TWEAK.']=';

        foreach ($this->tweaks as $id => $tweak) {
            $this->tweaks[$id] = array_merge($tweak, array('url' => $url.$id));
        }

        return $this->tweaks;
    }

    public function getActiveTweaks()
    {
        return (array) $this->get('tweaks');
    }
    /**
     * Returns a tweak
     *
     * @return array
     */
    public function getTweak($id)
    {
        $tweaks = $this->getTweaks();
        if (isset($tweaks[$id])) {
            return $tweaks[$id];
        }

        throw new \InvalidArgumentException(sprintf('Tweak with id "%s" doesn\'t exists', $id));
    }

    /**
     * Returns tweaks with a specific group
     *
     * @return array
     */
    public function getTweaksGroup($group)
    {
        $tweaksGroup = $this->getTweaks();

        foreach ($tweaksGroup as $id => $tweak) {
            if ($tweak['group'] != $group) {
                unset($tweaksGroup[$id]);
            }
        }

        return $tweaksGroup;
    }

    public function getActiveTweakGroup($group)
    {
        $tweaks = $this->getActiveTweaks();
        return isset($tweaks[$group]) ? $tweaks[$group] : -1;
    }
    /**
     * Adds Row Action
     *
     * @param Action\RowActionInterface $action
     *
     * @return self
     */
    public function addRowAction(RowActionInterface $action)
    {
        if ($action->getRole() === null || $this->securityContext->isGranted($action->getRole())) {
            $this->rowActions[$action->getColumn()][] = $action;
        }

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
     * Sets template for export
     *
     * @param Export $template
     *
     * @return self
     *
     * @throws \Exception
     */
    public function setTemplate($template)
    {
        if ($template !== null) {
            if ($template instanceof \Twig_Template) {
                $template = '__SELF__' . $template->getTemplateName();
            } elseif (!is_string($template) && $template === null) {
                throw new \Exception('Unable to load template');
            }

            $this->set(self::REQUEST_QUERY_TEMPLATE, $template);
            $this->saveSession();
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
        return $this->get(self::REQUEST_QUERY_TEMPLATE);
    }

    /**
     * Adds Export
     *
     * @param ExportInterface $export
     *
     * @return self
     */
    public function addExport(ExportInterface $export)
    {
        if ($export->getRole() === null || $this->securityContext->isGranted($export->getRole())) {
            $this->exports[] = $export;
        }

        return $this;
    }

    /**
     * Returns exports
     *
     * @return Export[]
     */
    public function getExports()
    {
        return $this->exports;
    }

    /**
     * Returns the export response
     *
     * @return Export[]
     */
    public function getExportResponse()
    {
        return $this->exportResponse;
    }

    /**
     * Returns the mass action response
     *
     * @return Export[]
     */
    public function getMassActionResponse()
    {
        return $this->massActionResponse;
    }

    /**
     * Sets Route Parameters
     *
     * @param string $parameter
     * @param mixed $value
     *
     * @return self
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
     * Sets Route URL
     *
     * @param string routeUrl
     *
     * @return self
     */
    public function setRouteUrl($routeUrl)
    {
        $this->routeUrl = $routeUrl;

        return $this;
    }

    /**
     * Returns Route URL
     *
     * @return string
     */
    public function getRouteUrl()
    {
        if ($this->routeUrl === null) {
            $this->routeUrl = $this->router->generate($this->request->get('_route'), $this->getRouteParameters());
        }

        return $this->routeUrl;
    }

    public function isReadyForExport()
    {
        return $this->isReadyForExport;
    }

    public function isMassActionRedirect()
    {
        return $this->massActionResponse instanceof Response;
    }

    /**
     * Set value for filters
     *
     * @param array Hash of columnName => initValue
     * @param boolean permanent filters ?
     *
     * @return self
     */
    protected function setFilters(array $filters, $permanent = true)
    {
        foreach ($filters as $columnId => $value) {
            if ($permanent) {
                $this->permanentFilters[$columnId] = $value;
            } else {
                $this->defaultFilters[$columnId] = $value;
            }
        }

        return $this;
    }

    /**
     * Set permanent value for filters
     *
     * @param array Hash of columnName => initValue
     * @param boolean fixed filters ?
     *
     * @return self
     */
    public function setPermanentFilters(array $filters)
    {
        return $this->setFilters($filters);
    }

    /**
     * Set default value for filters
     *
     * @param array Hash of columnName => initValue
     *
     * @return self
     */
    public function setDefaultFilters(array $filters)
    {
        return $this->setFilters($filters, false);
    }

    /**
     * Set the default grid order.
     *
     * @param array Hash of columnName => initValue
     *
     * @return self
     */
    public function setDefaultOrder($columnId, $order)
    {
        $order = strtolower($order);
        $this->defaultOrder = "$columnId|$order";

        return $this;
    }

    /**
     * Sets unique filter identification
     *
     * @param $id
     *
     * @return self
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
     *
     * @return self
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

    public function getDataJunction()
    {
        return $this->dataJunction;
    }

    public function setDataJunction($dataJunction)
    {
        $this->dataJunction = $dataJunction;

        return $this;
    }

    /**
     * Sets Limits
     *
     * @param mixed $limits e.g. 10, array(10, 1000) or array(10 => '10', 1000 => '1000')
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setLimits($limits)
    {
        if (is_array($limits)) {
            if ((int)key($limits) === 0) {
                $this->limits = array_combine($limits, $limits);
            } else {
                $this->limits = $limits;
            }
        } elseif (is_int($limits)) {
            $this->limits = array($limits => (string)$limits);
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
     *
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets default Limit
     *
     * @param $limit
     *
     * @return self
     */
    public function setDefaultLimit($limit)
    {
        $this->defaultLimit = (int) $limit;

        return $this;
    }

    /**
     * Sets default Page
     *
     * @param $page
     *
     * @return self
     */
    public function setDefaultPage($page)
    {
        $this->defaultPage = (int) $page - 1;

        return $this;
    }

    /**
     * Sets default Tweak
     *
     * @param $tweakId
     *
     * @return self
     */
    public function setDefaultTweak($tweakId)
    {
        $this->defaultTweak = $tweakId;

        return $this;
    }

    /**
     * Sets current Page (internal)
     *
     * @param $page
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
    public function setPage($page)
    {
        if ((int)$page >= 0) {
            $this->page = (int) $page;
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
        return ceil($this->getTotalCount() / $this->getLimit());
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
     * Sets the max results of the grid
     *
     * @param int $maxResults
     *
     * @return self
     *
     * @throws \InvalidArgumentException
     */
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
        // true when totalCount rows exceed the minimum pager limit
        return (min(array_keys($this->getLimits())) <= $this->totalCount);
    }

    /**
     * Hides Filters Panel
     *
     * @return self
     */
    public function hideFilters()
    {
        $this->showFilters = false;

        return $this;
    }

    /**
     * Hides Titles panel
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @return self
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
     *
     * @param array $columnIds
     *
     * @return self
     */
    public function setHiddenColumns($columnIds)
    {
        $this->lazyHiddenColumns = (array) $columnIds;

        return $this;
    }

    /**
     * Sets a list of columns to show when the grid is output
     * It acts as a mask; Other columns will be set as hidden
     *
     * @param array $columnIds
     *
     * @return self
     */
    public function setVisibleColumns($columnIds)
    {
        $this->lazyVisibleColumns = (array) $columnIds;

        return $this;
    }

    /**
     * Sets on the visibility of columns
     *
     * @param string|array $columnIds
     *
     * @return self
     */
    public function showColumns($columnIds)
    {
        foreach ((array) $columnIds as $columnId) {
            $this->lazyHideShowColumns[$columnId] = true;
        }

        return $this;
    }

    /**
     * Sets off the visiblilty of columns
     *
     * @param string|array $columnIds
     *
     * @return self
     */
    public function hideColumns($columnIds)
    {
        foreach ((array) $columnIds as $columnId) {
            $this->lazyHideShowColumns[$columnId] = false;
        }

        return $this;
    }

    /**
     * Sets the size of the default action column
     *
     * @param integer $size
     *
     * @return self
     */
    public function setActionsColumnSize($size)
    {
        $this->actionsColumnSize = $size;

        return $this;
    }

    /**
     * Sets the title of the default action column
     *
     * @param string $title
     *
     * @return self
     */
    public function setActionsColumnTitle($title)
    {
        $this->actionsColumnTitle = (string) $title;

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
     * @param string|array $param2 The view name or an array of parameters to pass to the view
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function getGridResponse($param1 = null, $param2 = null, Response $response = null)
    {
        $isReadyForRedirect = $this->isReadyForRedirect();

        if ($this->isReadyForExport()) {
            return $this->getExportResponse();
        }

        if ($this->isMassActionRedirect()) {
            return $this->getMassActionResponse();
        }

        if ($isReadyForRedirect) {
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

            if ($view === null) {
                return $parameters;
            } else {
                return $this->container->get('templating')->renderResponse($view, $parameters, $response);
            }
        }
    }

    /**
     * Extract raw data of columns
     *
     * @param string|array $columnNames The name of the extract columns. If null, all the columns are return.
     * @param boolean $namedIndexes If sets to true, named indexes will be used
     *
     * @return array Raw data of columns
     */
    public function getRawData($columnNames = null, $namedIndexes = true)
    {
        if ($columnNames === null) {
            foreach ($this->getColumns() as $column) {
                $columnNames[] = $column->getId();
            }
        }

        $columnNames = (array) $columnNames;
        $result = array();
        foreach ($this->rows as $row) {
            $resultRow = array();
            foreach ($columnNames as $columnName) {
                if ($namedIndexes) {
                    $resultRow[$columnName] = $row->getField($columnName);
                } else {
                    $resultRow[] = $row->getField($columnName);
                }
            }

            $result[] = $resultRow;
        }

        return $result;
    }
    
    /**
     * Returns an array of the active filters of the grid stored in session
     *
     * @return Filter[]
     * @throws \Exception
     */
    public function getFilters()
    {
        if ($this->hash === null) {
            throw new \Exception('getFilters method is only available in the manipulate callback function or after the call of the method isRedirected of the grid.');
        }
    
        if ($this->sessionFilters === null) {
            $this->sessionFilters = array();
            $session = $this->sessionData;
    
            $requestQueries = array(
                self::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED,
                self::REQUEST_QUERY_MASS_ACTION,
                self::REQUEST_QUERY_EXPORT,
                self::REQUEST_QUERY_PAGE,
                self::REQUEST_QUERY_LIMIT,
                self::REQUEST_QUERY_ORDER,
                self::REQUEST_QUERY_TEMPLATE,
                self::REQUEST_QUERY_RESET
            );
    
            foreach ($requestQueries as $request_query) {
                unset($session[$request_query]);
            }
    
            foreach ($session as $columnId => $sessionFilter) {
                if (isset($sessionFilter['operator'])) {
                    $operator = $sessionFilter['operator'];
                    unset($sessionFilter['operator']);
                } else {
                    $operator = $this->getColumn($columnId)->getDefaultOperator();
                }
    
                if (! isset($sessionFilter['to'])) {
                    $sessionFilter = $sessionFilter['from'];
                }
    
                $this->sessionFilters[$columnId] = new Filter($operator, $sessionFilter);
            }
        }
    
        return $this->sessionFilters;
    }
    
    /**
     * Returns the filter of a column stored in session
     *
     * @param string $columnId
     *            Id of the column
     * @return Filter
     * @throws \Exception
     */
    public function getFilter($columnId)
    {
        if ($this->hash === null) {
            throw new \Exception('getFilters method is only available in the manipulate callback function or after the call of the method isRedirected of the grid.');
        }
    
        $sessionFilters = $this->getFilters();
    
        return isset($sessionFilters[$columnId]) ? $sessionFilters[$columnId] : null;
    }
    
    /**
     * A filter of the column is stored in session ?
     *
     * @param string $columnId
     *            Id of the column
     * @return boolean
     * @throws \Exception
     */
    public function hasFilter($columnId)
    {
        if ($this->hash === null) {
            throw new \Exception('hasFilters method is only available in the manipulate callback function or after the call of the method isRedirected of the grid.');
        }
    
        return getFilter($columnId) !== null;
    }
}

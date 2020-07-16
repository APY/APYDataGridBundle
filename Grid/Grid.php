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

use APY\DataGridBundle\Grid\Action\MassActionInterface;
use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Column\BooleanColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\MassActionColumn;
use APY\DataGridBundle\Grid\Column\NumberColumn;
use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\Exception\NoActionSelectedException;
use APY\DataGridBundle\Grid\Export\ExportInterface;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Source\Source;
use Nuvola\AppBundle\Helper\QueryStringHelper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Grid implements GridInterface
{
    const REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED = '__action_all_keys';
    const REQUEST_QUERY_MASS_ACTION = '__action_id';
    const REQUEST_QUERY_MASS_ACTION_SUBMIT = '__action_id_submit';
    const REQUEST_QUERY_EXPORT = '__export_id';
    const REQUEST_QUERY_EXPORT_SUBMIT = '__export_id_submit';
    const REQUEST_QUERY_TWEAK = '__tweak_id';
    const REQUEST_QUERY_PAGE = '_page';
    const REQUEST_QUERY_LIMIT = '_limit';
    const REQUEST_QUERY_ORDER = '_order';
    const REQUEST_QUERY_TEMPLATE = '_template';
    const REQUEST_QUERY_RESET = '_reset';

    /** CUSTOM COLUMNS HANDLED */
    const BOOLEAN_CUSTOM_COLUMN_TYPE = 'boolean';
    const NUMBER_CUSTOM_COLUMN_TYPE = 'number';
    const TEXT_CUSTOM_COLUMN_TYPE = 'text';

    const SOURCE_ALREADY_SETTED_EX_MSG = 'The source of the grid is already set.';
    const SOURCE_NOT_SETTED_EX_MSG = 'The source of the grid must be set.';
    const TWEAK_MALFORMED_ID_EX_MSG = 'Tweak id "%s" is malformed. The id have to match this regex ^[0-9a-zA-Z_\+-]+';
    const TWIG_TEMPLATE_LOAD_EX_MSG = 'Unable to load template';
    const NOT_VALID_LIMIT_EX_MSG = 'Limit has to be array or integer';
    const NOT_VALID_PAGE_NUMBER_EX_MSG = 'Page must be a positive number';
    const NOT_VALID_MAX_RESULT_EX_MSG = 'Max results must be a positive number.';
    const MASS_ACTION_NOT_DEFINED_EX_MSG = 'Action %s is not defined.';
    const MASS_ACTION_CALLBACK_NOT_VALID_EX_MSG = 'Callback %s is not callable or Controller action';
    const EXPORT_NOT_DEFINED_EX_MSG = 'Export %s is not defined.';
    const PAGE_NOT_VALID_EX_MSG = 'Page must be a positive number';
    const COLUMN_ORDER_NOT_VALID_EX_MSG = '%s is not a valid order.';
    const DEFAULT_LIMIT_NOT_VALID_EX_MSG = 'Limit must be a positive number';
    const LIMIT_NOT_DEFINED_EX_MSG = 'Limit %s is not defined in limits.';
    const NO_ROWS_RETURNED_EX_MSG = 'Source have to return Rows object.';
    const INVALID_TOTAL_COUNT_EX_MSG = 'Source function getTotalCount need to return integer result, returned: %s';
    const NOT_VALID_TWEAK_ID_EX_MSG = 'Tweak with id "%s" doesn\'t exists';
    const GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG = 'getFilters method is only available in the manipulate callback function or after the call of the method isRedirected of the grid.';
    const HAS_FILTER_NO_REQUEST_HANDLED_EX_MSG = 'hasFilters method is only available in the manipulate callback function or after the call of the method isRedirected of the grid.';
    const TWEAK_NOT_DEFINED_EX_MSG = 'Tweak %s is not defined.';

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
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    protected $authorizationChecker;

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
     * @var bool
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
    protected $limits = [];

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
    protected $massActions = [];

    /**
     * @var \APY\DataGridBundle\Grid\Action\RowAction[]
     */
    protected $rowActions = [];

    /**
     * @var bool
     */
    protected $showFilters = true;

    /**
     * @var bool
     */
    protected $showTitles = true;

    /**
     * @var array|object request
     */
    protected $requestData;

    /**
     * @var array|object session
     */
    protected $sessionData = [];

    /**
     * @var string
     */
    protected $prefixTitle = '';

    /**
     * @var bool
     */
    protected $persistence = false;

    /**
     * @var bool
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
    protected $exports = [];

    /**
     * @var bool
     */
    protected $redirect = null;

    /**
     * @var bool
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
    protected $items = [];

    /**
     * Data junction of the grid.
     *
     * @var int
     */
    protected $dataJunction = Column::DATA_CONJUNCTION;

    /**
     * Permanent filters.
     *
     * @var array
     */
    protected $permanentFilters = [];

    /**
     * Default filters.
     *
     * @var array
     */
    protected $defaultFilters = [];

    /**
     * Default order (e.g. my_column_id|asc).
     *
     * @var string
     */
    protected $defaultOrder;

    /**
     * Default limit.
     *
     * @var int
     */
    protected $defaultLimit;

    /**
     * Default page.
     *
     * @var int
     */
    protected $defaultPage;

    /**
     * Tweaks.
     *
     * @var array
     */
    protected $tweaks = [];

    /**
     * Default Tweak.
     *
     * @var string
     */
    protected $defaultTweak;

    /**
     * Filters in session.
     *
     * @var array
     */
    protected $sessionFilters;

    /**
     * Has pinned column.
     *
     * @var bool
     */
    protected $pinned;

    // Lazy parameters
    protected $lazyAddColumn = [];
    protected $lazyHiddenColumns = [];
    protected $lazyVisibleColumns = [];
    protected $lazyHideShowColumns = [];

    // Lazy parameters for the action column
    protected $actionsColumnSize;
    protected $actionsColumnTitle;

    /**
     * The grid configuration.
     *
     * @var GridConfigInterface
     */
    private $config;

    /**
     * Constructor.
     *
     * @param Container                $container
     * @param string                   $id        set if you are using more then one grid inside controller
     * @param GridConfigInterface|null $config    The grid configuration.
     */
    public function __construct($container, $id = '', GridConfigInterface $config = null)
    {
        // @todo: why the whole container is injected?
        $this->container = $container;
        $this->config = $config;

        $this->router = $container->get('router');
        $this->request = $container->get('request_stack')->getCurrentRequest();
        $this->session = $this->request->getSession();
        $this->authorizationChecker = $container->get('security.authorization_checker');

        $this->id = $id;

        $this->columns = new Columns($this->authorizationChecker);

        // @todo: maybe sould use ->get('_route_params') instead of ->all() and the unset cycle
        $this->routeParameters = $this->request->attributes->all();
        foreach (array_keys($this->routeParameters) as $key) {
            if (substr($key, 0, 1) == '_') {
                unset($this->routeParameters[$key]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $config = $this->config;

        if (!$config) {
            return $this;
        }

        $this->setPersistence($config->isPersisted());

        // Route parameters
        $routeParameters = $config->getRouteParameters();
        if (!empty($routeParameters)) {
            foreach ($routeParameters as $parameter => $value) {
                $this->setRouteParameter($parameter, $value);
            }
        }

        // Route
        if (null !== $config->getRoute()) {
            $this->setRouteUrl($this->router->generate($config->getRoute(), $routeParameters));
        }

        // Route
        if (null !== $config->getRoute()) {
            $this->setRouteUrl($this->router->generate($config->getRoute(), $routeParameters));
        }

        // Columns
        foreach ($this->lazyAddColumn as $columnInfo) {
            /** @var Column $column */
            $column = $columnInfo['column'];

            if (!$config->isFilterable()) {
                $column->setFilterable(false);
            }

            if (!$config->isSortable()) {
                $column->setSortable(false);
            }
        }

        // Source
        $source = $config->getSource();

        if (null !== $source) {
            $this->source = $source;

            $source->initialise($this->container);

            if ($source instanceof Entity) {
                $groupBy = $config->getGroupBy();
                if (null !== $groupBy) {
                    if (!is_array($groupBy)) {
                        $groupBy = [$groupBy];
                    }

                    // Must be set after source because initialize method reset groupBy property
                    $source->setGroupBy($groupBy);
                }
            }
        }

        // Order
        if (null !== $config->getSortBy()) {
            $this->setDefaultOrder($config->getSortBy(), $config->getOrder());
        }

        if (null !== $config->getMaxPerPage()) {
            $this->setLimits($config->getMaxPerPage());
        }

        $this
            ->setMaxResults($config->getMaxResults())
            ->setPage($config->getPage());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handleRequest(Request $request)
    {
        if (null === $this->source) {
            throw new \LogicException(self::SOURCE_NOT_SETTED_EX_MSG);
        }

        $this->request = $request;
        $this->session = $request->getSession();

        $this->createHash();

        $this->requestData = $request->get($this->hash);

        $this->processPersistence();

        $this->sessionData = (array) $this->session->get($this->hash);

        $this->processLazyParameters();

        if (!empty($this->requestData)) {
            $this->processRequestData();
        }

        if ($this->newSession) {
            $this->setDefaultSessionData();
        }

        $this->processPermanentFilters();

        $this->processSessionData();

        $this->prepare();

        return $this;
    }

    /**
     * Sets Source to the Grid.
     *
     * @param $source
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setSource(Source $source)
    {
        if ($this->source !== null) {
            throw new \InvalidArgumentException(self::SOURCE_ALREADY_SETTED_EX_MSG);
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
            throw new \Exception(self::SOURCE_NOT_SETTED_EX_MSG);
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

        /**
         * If is an ajax request invoked by a mass action, I don't need to process filters/session data and redirect
         * I need this as ajax/dialog events could lead to this operation even by mass action that in a default
         * fashion would never hit this block of code ($this->request->isXmlHttpRequest would be always false).
         */
        if ($this->redirect === null
            || ($this->request->isXmlHttpRequest() && !$this->isReadyForExport
            && !$this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION))
        ) {
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
        return $this->request->getScheme() . '://' . $this->request->getHttpHost() . $this->request->getBaseUrl() . $this->request->getPathInfo();
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
            $columnNames = [];
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

        if ($this->processExports($this->getFromRequest(self::REQUEST_QUERY_EXPORT))
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
     * Process mass actions.
     *
     * @param int $actionId
     *
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     * @throws NoActionSelectedException
     */
    protected function processMassActions($actionId)
    {
        if(empty($this->request->get(self::REQUEST_QUERY_MASS_ACTION_SUBMIT))) {
            return;
        }

        if ($actionId == -1) {
            throw new NoActionSelectedException();
        }

        if ($actionId > -1 && '' !== $actionId) {
            if (array_key_exists($actionId, $this->massActions)) {
                $action = $this->massActions[$actionId];
                $actionAllKeys = (boolean) $this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED);
                $actionKeys = $actionAllKeys === false ? array_keys((array) $this->getFromRequest(MassActionColumn::ID)) : [];

                $this->processSessionData();
                if ($actionAllKeys) {
                    $this->page = 0;
                    $this->limit = 0;
                }

                $this->prepare();

                if ($actionAllKeys === true) {
                    foreach ($this->rows as $row) {
                        $actionKeys[] = $row->getPrimaryFieldValue();
                    }
                }

                if (is_callable($action->getCallback())) {
                    $this->massActionResponse = call_user_func($action->getCallback(), $actionKeys, $actionAllKeys, $this->session, $action->getParameters());
                } elseif (strpos($action->getCallback(), ':') !== false) {
                    $path = array_merge(
                        [
                            'primaryKeys'    => $actionKeys,
                            'allPrimaryKeys' => $actionAllKeys,
                            '_controller'    => $action->getCallback(),
                        ],
                        $action->getParameters()
                    );

                    $subRequest = $this->request->duplicate([], null, $path);

                    $this->massActionResponse = $this->container->get('http_kernel')->handle($subRequest, \Symfony\Component\HttpKernel\HttpKernelInterface::SUB_REQUEST);
                } else {
                    throw new \RuntimeException(sprintf(self::MASS_ACTION_CALLBACK_NOT_VALID_EX_MSG, $action->getCallback()));
                }
            } else {
                throw new \OutOfBoundsException(sprintf(self::MASS_ACTION_NOT_DEFINED_EX_MSG, $actionId));
            }
        }
    }

    /**
     * Process exports.
     *
     * @param int $exportId
     *
     * @throws \OutOfBoundsException
     *
     * @return bool
     */
    protected function processExports($exportId)
    {
        if(empty($this->request->get(self::REQUEST_QUERY_EXPORT_SUBMIT))) {
            return false;
        }

        if ($exportId == -1) {
            throw new NoActionSelectedException("Selezionare un formato di esportazione");
        }

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
                throw new \OutOfBoundsException(sprintf(self::EXPORT_NOT_DEFINED_EX_MSG, $exportId));
            }
        }

        return false;
    }

    /**
     * Process tweaks.
     *
     * @param int $tweakId
     *
     * @throws \OutOfBoundsException
     *
     * @return bool
     */
    protected function processTweaks($tweakId)
    {
        if ($tweakId !== null) {
            if (array_key_exists($tweakId, $this->tweaks)) {
                $tweak = $this->tweaks[$tweakId];
                $saveAsActive = false;

                if (isset($tweak['reset'])) {
                    $this->sessionData = [];
                    $this->session->remove($this->hash);
                }

                if (isset($tweak['filters'])) {
                    $this->defaultFilters = [];
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
                throw new \OutOfBoundsException(sprintf(self::TWEAK_NOT_DEFINED_EX_MSG, $tweakId));
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

                //if no item is selectd in multi select filter : simulate empty first choice
                if ($column->getFilterType() == 'select'
                    && $column->getSelectMulti() === true
                    && $data === null
                    && $this->getFromRequest(self::REQUEST_QUERY_PAGE) === null
                    && $this->getFromRequest(self::REQUEST_QUERY_ORDER) === null
                    && $this->getFromRequest(self::REQUEST_QUERY_LIMIT) === null
                    && ($this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION) === null || $this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION) == '-1')) {
                    $data = ['from' => ''];
                }

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
            || $filtering
            || (int) $page < 0
        ) {
            $this->set(self::REQUEST_QUERY_PAGE, 0);
        } else {
            $this->set(self::REQUEST_QUERY_PAGE, $page);
        }
    }

    protected function processOrder($order)
    {
        if ($order !== null) {
            list($columnId, $columnOrder) = explode('|', $order);

            // Ignore invalid order column names instead of letting the exception to cause the entire request to fail
            if(!$this->columns->hasColumnById($columnId)) {
                return;
            }

            $column = $this->columns->getColumnById($columnId);
            if ($column->isSortable() && in_array(strtolower($columnOrder), ['asc', 'desc'])) {
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
                throw new \InvalidArgumentException(self::NOT_VALID_PAGE_NUMBER_EX_MSG);
            }
        }

        // Default order
        if ($this->defaultOrder !== null) {
            list($columnId, $columnOrder) = explode('|', $this->defaultOrder);

            $this->columns->getColumnById($columnId);
            if (in_array(strtolower($columnOrder), ['asc', 'desc'])) {
                $this->set(self::REQUEST_QUERY_ORDER, $this->defaultOrder);
            } else {
                throw new \InvalidArgumentException(sprintf(self::COLUMN_ORDER_NOT_VALID_EX_MSG, $columnOrder));
            }
        }

        if ($this->defaultLimit !== null) {
            if ((int) $this->defaultLimit >= 0) {
                if (isset($this->limits[$this->defaultLimit])) {
                    $this->set(self::REQUEST_QUERY_LIMIT, $this->defaultLimit);
                } else {
                    throw new \InvalidArgumentException(sprintf(self::LIMIT_NOT_DEFINED_EX_MSG, $this->defaultLimit));
                }
            } else {
                throw new \InvalidArgumentException(self::DEFAULT_LIMIT_NOT_VALID_EX_MSG);
            }
        }

        // Default tweak
        if ($this->defaultTweak !== null) {
            $this->processTweaks($this->defaultTweak);
        }
        $this->saveSession();
    }

    /**
     * Store permanent filters to the session and disable the filter capability for the column if there are permanent filters.
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
                $value = ['from' => $value];
            }

            // Convert boolean value
            if (isset($value['from']) && is_bool($value['from'])) {
                $value['from'] = $value['from'] ? '1' : '0';
            }

            // Convert simple value with select filter
            if ($column->getFilterType() === 'select') {
                if (isset($value['from']) && !is_array($value['from'])) {
                    $value['from'] = [$value['from']];
                }

                if (isset($value['to']) && !is_array($value['to'])) {
                    $value['to'] = [$value['to']];
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
        $page = $this->get(self::REQUEST_QUERY_PAGE);
        if ($this->isPageValid($page)) {
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

    private function isPageValid($page): bool
    {
        return $page !== null && (int)$page > 0;
    }

    /**
     * Prepare Grid for Drawing.
     *
     * @throws \Exception
     *
     * @return self
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
            throw new \Exception(self::NO_ROWS_RETURNED_EX_MSG);
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

        //get size
        if ($this->source->isDataLoaded()) {
            $this->source->populateSelectFiltersFromData($this->columns);
            $this->totalCount = $this->source->getTotalCountFromData($this->maxResults);
        } else {
            $this->source->populateSelectFilters($this->columns);
            $this->totalCount = $this->source->getTotalCount($this->maxResults);
        }

        if (!is_int($this->totalCount)) {
            throw new \Exception(sprintf(self::INVALID_TOTAL_COUNT_EX_MSG, gettype($this->totalCount)));
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
     * @param string $key  A unique key identifying the data
     * @param mixed  $data Data associated with the key
     */
    protected function set($key, $data)
    {
        // Only the filters values are removed from the session
        $fromIsEmpty = isset($data['from']) && ((is_string($data['from']) && $data['from'] === '') || (is_array($data['from']) && $data['from'][0] === ''));
        $toIsSet = isset($data['to']) && (is_string($data['to']) && $data['to'] !== '');
        if ($fromIsEmpty && !$toIsSet) {
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
        $this->hash = 'grid_' . (empty($this->id) ? md5($this->request->get('_controller') . $this->columns->getHash() . $this->source->getHash()) : str_ireplace('-', '_', $this->getId()));
    }

    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Adds custom column to the grid.
     *
     * @param $column
     * @param int $position
     *
     * @return self
     */
    public function addColumn($column, $position = 0)
    {
        $this->lazyAddColumn[] = ['column' => $column, 'position' => $position];

        return $this;
    }

    /**
     * Create a custom column with values taken from a $callbackMethod result
     * and add custom filter (if column is filterable) based on $callbackMethod result.
     *
     * @param string    $columnType
     * @param array     $columnAttributes
     * @param int       $columnPosition
     * @param string    $callbackMethod
     * @param array     $callbackMethodParams
     * @param bool|true $filterable
     * @param null      $entityRetrievalCallback
     *
     * @throws \InvalidArgumentException
     */
    public function addCustomColumn(
        $columnType,
        $columnAttributes,
        $columnPosition,
        $callbackMethod,
        $callbackMethodParams = [],
        $filterable = true,
        $entityRetrievalCallback = null
    ) {
        $handledCustomColumnsTypes = [
            self::BOOLEAN_CUSTOM_COLUMN_TYPE,
            self::NUMBER_CUSTOM_COLUMN_TYPE,
            self::TEXT_CUSTOM_COLUMN_TYPE,
        ];

        if (false === in_array($columnType, $handledCustomColumnsTypes)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'addCustomColumnToGrid method accepts following column types: %s. Type %s given',
                    implode(',', $handledCustomColumnsTypes),
                    $columnType
                )
            );
        }

        if (!isset($columnAttributes['id'])) {
            throw new \InvalidArgumentException('addCustomColumnToGrid method need an attribute "id" in columnAttributes array');
        }
        $columnId = $columnAttributes['id'];

        if (false == $filterable) {
            $columnAttributes = array_merge($columnAttributes, ['filterable' => false]);
        }

        switch ($columnType) {
            /* The only chance I have to filter a custom column, especially if this has some "logic", is to
             * add a custom filter that with $callbackMethod result
             */
            case self::BOOLEAN_CUSTOM_COLUMN_TYPE:
                $column = new BooleanColumn($columnAttributes);
                if ($filterable) {
                    $this->createBooleanFilterForCustomColumn($columnId, $callbackMethod, $callbackMethodParams, $entityRetrievalCallback);
                }
                break;
            case self::NUMBER_CUSTOM_COLUMN_TYPE:
                $column = new NumberColumn($columnAttributes);
                if ($filterable) {
                    $this->createNumericFilterForCustomColumn($columnId, $callbackMethod, $callbackMethodParams, $entityRetrievalCallback);
                }
                break;
            case self::TEXT_CUSTOM_COLUMN_TYPE:
                $column = new TextColumn($columnAttributes);
                if ($filterable) {
                    $column->setOperators([
                        Column::OPERATOR_LIKE,
                        Column::OPERATOR_NLIKE,
                        Column::OPERATOR_EQ,
                        Column::OPERATOR_NEQ,
                    ]);
                    $this->createTextFilterForCustomColumn($columnId, $callbackMethod, $callbackMethodParams, $entityRetrievalCallback);
                }
                break;
            default:
                //I should never enter there as I check for handled custom columns
                return;
        }

        $this->addColumn($column, $columnPosition);
        /* The only chance I have to insert a value into custom column, especially if this has some "logic", is to
         * manipulate its content with $callbackMethod result
         */
        $this->manipulateCustomColumnContent($columnId, $callbackMethod, $callbackMethodParams, $entityRetrievalCallback);
    }

    /**
     * Set row->column (cell) value based on $callbackMethod result.
     *
     * @param int      $columnId
     * @param string   $callbackMethod
     * @param array    $callbackMethodParams
     * @param \Closure $entityRetrievalCallback
     */
    protected function manipulateCustomColumnContent(
        $columnId,
        $callbackMethod,
        $callbackMethodParams,
        $entityRetrievalCallback
    ) {
        /*
         * Not all entities bounded to $row are representative for grid logic we are showing.
         * Cell content could be manipulated by a more complex logic than "getEntity" (i.e.: I have an entity Foo bounded
         * to current column, but I want to rely on $foo->getBar()->getFooBar()->count() return value to show correct info.
         * So, if $entityRetrievalCallback has a value, we need to invoke the closure to retrieve entity and, then, apply
         * the $callbackMethod to retrieve correct value (count() in our example) for that cell.
         */
        $this->getColumn($columnId)->manipulateRenderCell(
            function ($value, $row, $router) use ($entityRetrievalCallback, $callbackMethod, $callbackMethodParams) {
                /* @var Row $row */
                if ($entityRetrievalCallback) {
                    $entity = $entityRetrievalCallback($value, $row, $router);
                } else {
                    $entity = $row->getEntity();
                }

                return call_user_func_array([$entity, $callbackMethod], $callbackMethodParams);
            }
        );
    }

    /**
     * Create the filtering function for custom boolean columns. Row is included/excluded by $callbackMethod result.
     *
     * @param int      $columnId
     * @param string   $callbackMethod
     * @param array    $callbackMethodParams
     * @param \Closure $entityRetrievalCallback
     */
    protected function createBooleanFilterForCustomColumn($columnId, $callbackMethod, $callbackMethodParams, $entityRetrievalCallback)
    {
        /*
         * As manipulateRow accept a callback and store it inside a protected member of Source class and as we're doing filtering
         * operation onto a row (so, single row, multiple custom columns available for filtering operations), we simply cannot store
         * more than one callBack inside a Source (row) [we could store an array of that calls but we wuold be forced to change
         * all bundle implementation].
         * The idea behind this is to retrive (if stored before) the Closure callback stored before this and, if this is stored,
         * store a new Closure callback composed by this and the previous one.
         * We need to check/call previous callback as first operations because:
         *  - if no callback is stored, we can go on with this filter logic
         *  - if a callback is stored, we need to check its return value: if row isn't returned back, it's useless to keep going
         *    with filtering operations.
         */
        $source = $this->getSource();
        $previousCallback = $source->getRowCallback();

        $this->getSource()->manipulateRow(
            function (Row $row) use ($columnId, $callbackMethod, $callbackMethodParams, $previousCallback, $entityRetrievalCallback) {

                if (null !== $previousCallback) {
                    $previousCallbackResult = $previousCallback($row);
                    if (!$previousCallbackResult) {
                        return;
                    }
                }

                $filter = $this->getFilter($columnId);

                if (null === $filter) {
                    return $row;
                }

                $filterValue = $filter->getValue()[0];

                if ($entityRetrievalCallback) {
                    $entity = $entityRetrievalCallback(null, $row, null);
                } else {
                    $entity = $row->getEntity();
                }

                if (!method_exists($entity, $callbackMethod)) {
                    return $row;
                }
                $methodValue = call_user_func_array([$entity, $callbackMethod], $callbackMethodParams);

                if ($filterValue && !$methodValue) {
                    return;
                }
                if (!$filterValue && $methodValue) {
                    return;
                }

                return $row;
            });
    }

    /**
     * Create the filtering function for custom numeric columns. Row is included/excluded by $callbackMethod result.
     *
     * @param int      $columnId
     * @param string   $callbackMethod
     * @param string   $callbackMethodParams
     * @param \Closure $entityRetrievalCallback
     */
    protected function createNumericFilterForCustomColumn($columnId, $callbackMethod, $callbackMethodParams, $entityRetrievalCallback)
    {
        /*
         * As manipulateRow accept a callback and store it inside a protected member of Source class and as we're doing filtering
         * operation onto a row (so, single row, multiple custom columns available for filtering operations), we simply cannot store
         * more than one callBack inside a Source (row) [we could store an array of that calls but we wuold be forced to change
         * all bundle implementation].
         * The idea behind this is to retrive (if stored before) the Closure callback stored before this and, if this is stored,
         * store a new Closure callback composed by this and the previous one.
         * We need to check/call previous callback as first operations because:
         *  - if no callback is stored, we can go on with this filter logic
         *  - if a callback is stored, we need to check its return value: if row isn't returned back, it's useless to keep going
         *    with filtering operations.
         */
        $source = $this->getSource();
        $previousCallback = $source->getRowCallback();

        $this->getSource()->manipulateRow(
            function (Row $row) use ($columnId, $callbackMethod, $callbackMethodParams, $previousCallback, $entityRetrievalCallback) {

                if (null !== $previousCallback) {
                    $previousCallbackResult = $previousCallback($row);
                    if (!$previousCallbackResult) {
                        return;
                    }
                }

                $filter = $this->getFilter($columnId);

                if (null === $filter) {
                    return $row;
                }

                if ($entityRetrievalCallback) {
                    $entity = $entityRetrievalCallback(null, $row, null);
                } else {
                    $entity = $row->getEntity();
                }

                $methodValue = call_user_func_array([$entity, $callbackMethod], $callbackMethodParams);
                $filterValue = $filter->getValue();

                $operator = $filter->getOperator();
                switch ($operator) {
                    case Column::OPERATOR_EQ:
                        if ($methodValue != $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_NEQ:
                        if ($methodValue == $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_LT:
                        if ($methodValue >= $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_LTE:
                        if ($methodValue > $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_GT:
                        if ($methodValue <= $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_GTE:
                        if ($methodValue < $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_BTW:
                        $lowerBound = $filterValue['from'];
                        $upperBound = $filterValue['to'];
                        if ($methodValue < $lowerBound || $methodValue > $upperBound) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_BTWE:
                        $lowerBound = $filterValue['from'];
                        $upperBound = $filterValue['to'];
                        if ($methodValue <= $lowerBound || $methodValue >= $upperBound) {
                            return;
                        }
                        break;
                    default:
                        break;
                }

                return $row;
            }
        );
    }

    /**
     * Create the filtering function for custom text columns. Row is included/excluded by $callbackMethod result.
     *
     * @param int      $columnId
     * @param string   $callbackMethod
     * @param string   $callbackMethodParams
     * @param \Closure $entityRetrievalCallback
     */
    protected function createTextFilterForCustomColumn($columnId, $callbackMethod, $callbackMethodParams, $entityRetrievalCallback)
    {
        /*
         * As manipulateRow accept a callback and store it inside a protected member of Source class and as we're doing filtering
         * operation onto a row (so, single row, multiple custom columns available for filtering operations), we simply cannot store
         * more than one callBack inside a Source (row) [we could store an array of that calls but we would be forced to change
         * all bundle implementation].
         * The idea behind this is to retrieve (if stored before) the Closure callback stored before this and, if this is stored,
         * store a new Closure callback composed by this and the previous one.
         * We need to check/call previous callback as first operations because:
         *  - if no callback is stored, we can go on with this filter logic
         *  - if a callback is stored, we need to check its return value: if row isn't returned back, it's useless to keep going
         *    with filtering operations.
         */
        $source = $this->getSource();
        $previousCallback = $source->getRowCallback();

        $this->getSource()->manipulateRow(
            function (Row $row) use ($columnId, $callbackMethod, $callbackMethodParams, $previousCallback, $entityRetrievalCallback) {

                if (null !== $previousCallback) {
                    $previousCallbackResult = $previousCallback($row);
                    if (!$previousCallbackResult) {
                        return;
                    }
                }

                $filter = $this->getFilter($columnId);

                if (null === $filter) {
                    return $row;
                }

                if ($entityRetrievalCallback) {
                    $entity = $entityRetrievalCallback(null, $row, null);
                } else {
                    $entity = $row->getEntity();
                }

                $methodValue = call_user_func_array([$entity, $callbackMethod], $callbackMethodParams);
                $filterValue = $filter->getValue();

                $operator = $filter->getOperator();
                switch ($operator) {
                    case Column::OPERATOR_EQ:
                        if ($methodValue != $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_NEQ:
                        if ($methodValue == $filterValue) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_LIKE:
                        if (false === stripos($methodValue, $filterValue)) {
                            return;
                        }
                        break;
                    case Column::OPERATOR_NLIKE:
                        if (false !== stripos($methodValue, $filterValue)) {
                            return;
                        }
                        break;
                    default:
                        break;
                }

                return $row;
            }
        );
    }

    /**
     * Get a column by its identifier.
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
     * Returns Grid Columns.
     *
     * @return Column[]|Columns
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Returns true if column exists in columns and lazyAddColumn properties.
     *
     * @param $columnId
     *
     * @return bool
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
     * Sets Array of Columns to the grid.
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
     * placed after.
     *
     * @param array $columnIds
     * @param bool  $keepOtherColumns
     *
     * @return self
     */
    public function setColumnsOrder(array $columnIds, $keepOtherColumns = true)
    {
        $this->columns->setColumnsOrder($columnIds, $keepOtherColumns);

        return $this;
    }

    /**
     * Adds Mass Action.
     *
     * @param Action\MassActionInterface $action
     *
     * @return self
     */
    public function addMassAction(MassActionInterface $action)
    {
        if ($action->getRole() === null || $this->authorizationChecker->isGranted($action->getRole())) {
            $this->massActions[] = $action;
        }

        return $this;
    }

    /**
     * Returns Mass Actions.
     *
     * @return Action\MassAction[]
     */
    public function getMassActions()
    {
        return $this->massActions;
    }

    /**
     * Add a tweak.
     *
     * @param string $title title of the tweak
     * @param array  $tweak array('filters' => array, 'order' => 'colomunId|order', 'page' => integer, 'limit' => integer, 'export' => integer, 'massAction' => integer)
     * @param string $id    id of the tweak matching the regex ^[0-9a-zA-Z_\+-]+
     * @param string $group group of the tweak
     *
     * @return self
     */
    public function addTweak($title, array $tweak, $id = null, $group = null)
    {
        if ($id !== null && !preg_match('/^[0-9a-zA-Z_\+-]+$/', $id)) {
            throw new \InvalidArgumentException(sprintf(self::TWEAK_MALFORMED_ID_EX_MSG, $id));
        }

        $tweak = array_merge(['id' => $id, 'title' => $title, 'group' => $group], $tweak);
        if (isset($id)) {
            $this->tweaks[$id] = $tweak;
        } else {
            $this->tweaks[] = $tweak;
        }

        return $this;
    }

    /**
     * Returns tweaks
     * Add the url of the tweak.
     *
     * @return array
     */
    public function getTweaks()
    {
        $separator = strpos($this->getRouteUrl(), '?') ? '&' : '?';
        $url = $this->getRouteUrl() . $separator . $this->getHash() . '[' . self::REQUEST_QUERY_TWEAK . ']=';

        foreach ($this->tweaks as $id => $tweak) {
            $this->tweaks[$id] = array_merge($tweak, ['url' => $url . $id]);
        }

        return $this->tweaks;
    }

    public function getActiveTweaks()
    {
        return (array) $this->get('tweaks');
    }

    /**
     * Returns a tweak.
     *
     * @return array
     */
    public function getTweak($id)
    {
        $tweaks = $this->getTweaks();
        if (isset($tweaks[$id])) {
            return $tweaks[$id];
        }

        throw new \InvalidArgumentException(sprintf(self::NOT_VALID_TWEAK_ID_EX_MSG, $id));
    }

    /**
     * Returns tweaks with a specific group.
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
     * Adds Row Action.
     *
     * @param Action\RowActionInterface $action
     *
     * @return self
     */
    public function addRowAction(RowActionInterface $action)
    {
        if ($action->getRole() === null || $this->authorizationChecker->isGranted($action->getRole())) {
            $this->rowActions[$action->getColumn()][] = $action;
        }

        return $this;
    }

    /**
     * Returns Row Actions.
     *
     * @return Action\RowAction[]
     */
    public function getRowActions()
    {
        return $this->rowActions;
    }

    /**
     * Sets template for export.
     *
     * @param \Twig_Template|string $template
     *
     * @throws \Exception
     *
     * @return self
     */
    public function setTemplate($template)
    {
        if ($template !== null) {
            if ($template instanceof \Twig_Template) {
                $template = '__SELF__' . $template->getTemplateName();
            } elseif (!is_string($template)) {
                throw new \Exception(self::TWIG_TEMPLATE_LOAD_EX_MSG);
            }

            $this->set(self::REQUEST_QUERY_TEMPLATE, $template);
            $this->saveSession();
        }

        return $this;
    }

    /**
     * Returns template.
     *
     * @return \Twig_Template|string
     */
    public function getTemplate()
    {
        return $this->get(self::REQUEST_QUERY_TEMPLATE);
    }

    /**
     * Adds Export.
     *
     * @param ExportInterface $export
     *
     * @return self
     */
    public function addExport(ExportInterface $export)
    {
        if ($export->getRole() === null || $this->authorizationChecker->isGranted($export->getRole())) {
            $this->exports[] = $export;
        }

        return $this;
    }

    /**
     * Returns exports.
     *
     * @return ExportInterface[]
     */
    public function getExports()
    {
        return $this->exports;
    }

    /**
     * Returns the export response.
     *
     * @return Export[]
     */
    public function getExportResponse()
    {
        return $this->exportResponse;
    }

    /**
     * Returns the mass action response.
     *
     * @return Export[]
     */
    public function getMassActionResponse()
    {
        return $this->massActionResponse;
    }

    /**
     * Sets Route Parameters.
     *
     * @param string $parameter
     * @param mixed  $value
     *
     * @return self
     */
    public function setRouteParameter($parameter, $value)
    {
        $this->routeParameters[$parameter] = $value;

        return $this;
    }

    /**
     * Returns Route Parameters.
     *
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Sets Route URL.
     *
     * @param string $routeUrl
     *
     * @return self
     */
    public function setRouteUrl($routeUrl)
    {
        $this->routeUrl = $routeUrl;

        return $this;
    }

    /**
     * Returns Route URL.
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
     * Set value for filters.
     *
     * @param array $filters   Hash of columnName => initValue
     * @param bool  $permanent filters ?
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
     * Set permanent value for filters.
     *
     * @param array $filters Hash of columnName => initValue
     *
     * @return self
     */
    public function setPermanentFilters(array $filters)
    {
        return $this->setFilters($filters);
    }

    /**
     * Set default value for filters.
     *
     * @param array $filters Hash of columnName => initValue
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
     * @param $columnId
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
     * Sets unique filter identification.
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
     * Returns unique filter identifier.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets persistence.
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
     * Returns persistence.
     *
     * @return bool
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
     * Sets Limits.
     *
     * @param mixed $limits e.g. 10, array(10, 1000) or array(10 => '10', 1000 => '1000')
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setLimits($limits)
    {
        if (is_array($limits)) {
            if ((int) key($limits) === 0) {
                $this->limits = array_combine($limits, $limits);
            } else {
                $this->limits = $limits;
            }
        } elseif (is_int($limits)) {
            $this->limits = [$limits => (string) $limits];
        } else {
            throw new \InvalidArgumentException(self::NOT_VALID_LIMIT_EX_MSG);
        }

        return $this;
    }

    /**
     * Returns limits.
     *
     * @return array
     */
    public function getLimits()
    {
        return $this->limits;
    }

    /**
     * Returns selected Limit (Rows Per Page).
     *
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Sets default Limit.
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
     * Sets default Page.
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
     * Sets default Tweak.
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
     * Sets current Page (internal).
     *
     * @param $page
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setPage($page)
    {
        if ((int) $page >= 0) {
            $this->page = (int) $page;
        } else {
            throw new \InvalidArgumentException(self::PAGE_NOT_VALID_EX_MSG);
        }

        return $this;
    }

    /**
     * Returns current page.
     *
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Returnd grid display data as rows - internal helper for templates.
     *
     * @return mixed
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Return count of available pages.
     *
     * @return float
     */
    public function getPageCount()
    {
        $pageCount = 1;
        if ($this->getLimit() > 0) {
            $pageCount = ceil($this->getTotalCount() / $this->getLimit());
        }

        // @todo why this should be a float?
        return $pageCount;
    }

    /**
     * Returns count of filtred rows(items) from source.
     *
     * @return mixed
     */
    public function getTotalCount()
    {
        return $this->totalCount;
    }

    /**
     * Sets the max results of the grid.
     *
     * @param int $maxResults
     *
     * @throws \InvalidArgumentException
     *
     * @return self
     */
    public function setMaxResults($maxResults = null)
    {
        if ((is_int($maxResults) && $maxResults < 0) && $maxResults !== null) {
            throw new \InvalidArgumentException(self::NOT_VALID_MAX_RESULT_EX_MSG);
        }

        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * Return true if the grid is filtered.
     *
     * @return bool
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
     * Return true if if title panel is visible in template - internal helper.
     *
     * @return bool
     */
    public function isTitleSectionVisible()
    {
        if ($this->showTitles === true) {
            foreach ($this->columns as $column) {
                if ($column->getTitle() != '') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return true if filter panel is visible in template - internal helper.
     *
     * @return bool
     */
    public function isFilterSectionVisible()
    {
        if ($this->showFilters === true) {
            foreach ($this->columns as $column) {
                if ($column->isFilterable() && $column->getType() != 'massaction' && $column->getType() != 'actions') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return true if pager panel is visible in template - internal helper.
     *
     * @return bool return true if pager is visible
     */
    public function isPagerSectionVisible()
    {
        $limits = $this->getLimits();

        if (empty($limits)) {
            return false;
        }

        // true when totalCount rows exceed the minimum pager limit
        return min(array_keys($limits)) < $this->totalCount;
    }

    /**
     * Return true if pinned property has marked to true.
     *
     * @return bool return true if table has columns pinned
     */
    public function isPinnable()
    {
        return $this->pinned === true;
    }

    /**
     * Set the pinned table.
     *
     * @param bool $pin
     *
     * @return self
     */
    public function setPinned($pin)
    {
        $this->pinned = $pin;

        return $this;
    }

    /**
     * Hides Filters Panel.
     *
     * @return self
     */
    public function hideFilters()
    {
        $this->showFilters = false;

        return $this;
    }

    /**
     * Hides Titles panel.
     *
     * @return self
     */
    public function hideTitles()
    {
        $this->showTitles = false;

        return $this;
    }

    /**
     * Adds Column Extension - internal helper.
     *
     * @param Column $extension
     *
     * @return self
     */
    public function addColumnExtension($extension)
    {
        $this->columns->addExtension($extension);

        return $this;
    }

    /**
     * Set a prefix title.
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
     * Get the prefix title.
     *
     * @return string
     */
    public function getPrefixTitle()
    {
        return $this->prefixTitle;
    }

    /**
     * Set the no data message.
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
     * Get the no data message.
     *
     * @return string
     */
    public function getNoDataMessage()
    {
        return $this->noDataMessage;
    }

    /**
     * Set the no result message.
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
     * Get the no result message.
     *
     * @return string
     */
    public function getNoResultMessage()
    {
        return $this->noResultMessage;
    }

    /**
     * Sets a list of columns to hide when the grid is output.
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
     * It acts as a mask; Other columns will be set as hidden.
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
     * Sets on the visibility of columns.
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
     * Sets off the visiblilty of columns.
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
     * Sets the size of the default action column.
     *
     * @param int $size
     *
     * @return self
     */
    public function setActionsColumnSize($size)
    {
        $this->actionsColumnSize = $size;

        return $this;
    }

    /**
     * Sets the title of the default action column.
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
     * Default delete action.
     *
     * @param array $ids
     */
    public function deleteAction(array $ids)
    {
        $this->source->delete($ids);
    }

    /**
     * Get a clone of the grid.
     */
    public function __clone()
    {
        // clone all objects
        $this->columns = clone $this->columns;
    }

    /****** HELPER ******/

    /**
     * Redirects or Renders a view - helper function.
     *
     * @param string|array $param1   The view name or an array of parameters to pass to the view
     * @param string|array $param2   The view name or an array of parameters to pass to the view
     * @param Response     $response A response instance
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
            /*
             * Redirect should be handled properly after ajax/dialog feature we introduced.
             * In this case I need to append ajaxAction and followRedirect=0 if ajaxAction is in query string
             * as we're doing operation inside the dialog box and we want to stay inside the dialog
             */
            if ($ajaxAction = $this->request->get('ajaxAction')) {
                $redirectUrl = QueryStringHelper::appendParametersToQueryString(
                    $this->getRouteUrl(),
                    [
                        'ajaxAction' => $ajaxAction,
                        'followRedirect' => 0,
                    ]
                );
            } else {
                $redirectUrl = $this->getRouteUrl();
            }

            return new RedirectResponse($redirectUrl);
        } else {
            if (is_array($param1) || $param1 === null) {
                $parameters = (array) $param1;
                $view = $param2;
            } else {
                $parameters = (array) $param2;
                $view = $param1;
            }

            $parameters = array_merge(['grid' => $this], $parameters);

            if ($view === null) {
                return $parameters;
            } else {
                return $this->container->get('templating')->renderResponse($view, $parameters, $response);
            }
        }
    }

    /**
     * Extract raw data of columns.
     *
     * @param string|array $columnNames  The name of the extract columns. If null, all the columns are return.
     * @param bool         $namedIndexes If sets to true, named indexes will be used
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
        $result = [];
        foreach ($this->rows as $row) {
            $resultRow = [];
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
     * Returns an array of the active filters of the grid stored in session.
     *
     * @throws \Exception
     *
     * @return Filter[]
     */
    public function getFilters()
    {
        if ($this->hash === null) {
            throw new \Exception(self::GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG);
        }

        if ($this->sessionFilters === null) {
            $this->sessionFilters = [];
            $session = $this->sessionData;

            $requestQueries = [
                self::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED,
                self::REQUEST_QUERY_MASS_ACTION,
                self::REQUEST_QUERY_EXPORT,
                self::REQUEST_QUERY_PAGE,
                self::REQUEST_QUERY_LIMIT,
                self::REQUEST_QUERY_ORDER,
                self::REQUEST_QUERY_TEMPLATE,
                self::REQUEST_QUERY_RESET,
                MassActionColumn::ID,
            ];

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

                if (!isset($sessionFilter['to']) && isset($sessionFilter['from'])) {
                    $sessionFilter = $sessionFilter['from'];
                }

                $this->sessionFilters[$columnId] = new Filter($operator, $sessionFilter);
            }
        }

        return $this->sessionFilters;
    }

    /**
     * Returns the filter of a column stored in session.
     *
     * @param string $columnId Id of the column
     *
     * @throws \Exception
     *
     * @return Filter
     */
    public function getFilter($columnId)
    {
        if ($this->hash === null) {
            throw new \Exception(self::GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG);
        }

        $sessionFilters = $this->getFilters();

        return isset($sessionFilters[$columnId]) ? $sessionFilters[$columnId] : null;
    }

    /**
     * A filter of the column is stored in session ?
     *
     * @param string $columnId Id of the column
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function hasFilter($columnId)
    {
        if ($this->hash === null) {
            throw new \Exception(self::HAS_FILTER_NO_REQUEST_HANDLED_EX_MSG);
        }

        return $this->getFilter($columnId) !== null;
    }
}

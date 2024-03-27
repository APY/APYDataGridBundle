<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Action\MassAction;
use APY\DataGridBundle\Grid\Action\MassActionInterface;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\MassActionColumn;
use APY\DataGridBundle\Grid\Export\Export;
use APY\DataGridBundle\Grid\Export\ExportInterface;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Source\Source;
use Doctrine\Persistence\ManagerRegistry;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Template;

class Grid implements GridInterface
{
    public const REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED = '__action_all_keys';
    public const REQUEST_QUERY_MASS_ACTION = '__action_id';
    public const REQUEST_QUERY_EXPORT = '__export_id';
    public const REQUEST_QUERY_TWEAK = '__tweak_id';
    public const REQUEST_QUERY_PAGE = '_page';
    public const REQUEST_QUERY_LIMIT = '_limit';
    public const REQUEST_QUERY_ORDER = '_order';
    public const REQUEST_QUERY_TEMPLATE = '_template';
    public const REQUEST_QUERY_RESET = '_reset';

    public const SOURCE_ALREADY_SETTED_EX_MSG = 'The source of the grid is already set.';
    public const SOURCE_NOT_SETTED_EX_MSG = 'The source of the grid must be set.';
    public const TWEAK_MALFORMED_ID_EX_MSG = 'Tweak id "%s" is malformed. The id have to match this regex ^[0-9a-zA-Z_\+-]+';
    public const TWIG_TEMPLATE_LOAD_EX_MSG = 'Unable to load template';
    public const NOT_VALID_LIMIT_EX_MSG = 'Limit has to be array or integer';
    public const NOT_VALID_PAGE_NUMBER_EX_MSG = 'Page must be a positive number';
    public const NOT_VALID_MAX_RESULT_EX_MSG = 'Max results must be a positive number.';
    public const MASS_ACTION_NOT_DEFINED_EX_MSG = 'Action %s is not defined.';
    public const MASS_ACTION_CALLBACK_NOT_VALID_EX_MSG = 'Callback %s is not callable or Controller action';
    public const EXPORT_NOT_DEFINED_EX_MSG = 'Export %s is not defined.';
    public const PAGE_NOT_VALID_EX_MSG = 'Page must be a positive number';
    public const COLUMN_ORDER_NOT_VALID_EX_MSG = '%s is not a valid order.';
    public const DEFAULT_LIMIT_NOT_VALID_EX_MSG = 'Limit must be a positive number';
    public const LIMIT_NOT_DEFINED_EX_MSG = 'Limit %s is not defined in limits.';
    public const NO_ROWS_RETURNED_EX_MSG = 'Source have to return Rows object.';
    public const NOT_VALID_TWEAK_ID_EX_MSG = 'Tweak with id "%s" doesn\'t exists';
    public const GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG = 'getFilters method is only available in the manipulate callback function or after the call of the method isRedirected of the grid.';
    public const HAS_FILTER_NO_REQUEST_HANDLED_EX_MSG = 'hasFilters method is only available in the manipulate callback function or after the call of the method isRedirected of the grid.';
    public const TWEAK_NOT_DEFINED_EX_MSG = 'Tweak %s is not defined.';

    #[Deprecated]
    protected ?Container $container;

    protected ?SessionInterface $session = null;

    protected ?Request $request = null;

    protected ?string $id = null;

    protected ?string $hash = null;

    protected ?string $routeUrl = null;

    protected ?array $routeParameters = null;

    protected ?Source $source = null;

    protected bool $prepared = false;

    protected ?int $totalCount = null;

    protected int $page = 0;

    protected ?int $limit = null;

    protected array $limits = [];

    /**
     * @var Columns|Column[]
     */
    protected Columns|array $columns;

    protected Rows|array|null $rows = null;

    /**
     * @var MassAction[]
     */
    protected array $massActions = [];

    /**
     * @var RowAction[]
     */
    protected array $rowActions = [];

    protected bool $showFilters = true;

    protected bool $showTitles = true;

    protected mixed $requestData;

    protected mixed $sessionData = [];

    protected string $prefixTitle = '';

    protected bool $persistence = false;

    protected bool $newSession = false;

    protected ?string $noDataMessage = null;

    protected ?string $noResultMessage = null;

    /**
     * @var Export[]
     */
    protected array $exports = [];

    protected ?bool $redirect = null;

    protected bool $isReadyForExport = false;

    protected ?Response $exportResponse = null;

    protected ?Response $massActionResponse = null;

    protected ?int $maxResults = null;

    protected array $items = [];

    /**
     * Data junction of the grid.
     */
    protected int $dataJunction = Column::DATA_CONJUNCTION;

    /**
     * Permanent filters.
     */
    protected array $permanentFilters = [];

    /**
     * Default filters.
     */
    protected array $defaultFilters = [];

    /**
     * Default order (e.g. my_column_id|asc).
     */
    protected ?string $defaultOrder = null;

    /**
     * Default limit.
     */
    protected ?int $defaultLimit = null;

    /**
     * Default page.
     */
    protected ?int $defaultPage = null;

    /**
     * Tweaks.
     */
    protected array $tweaks = [];

    /**
     * Default Tweak.
     */
    protected ?string $defaultTweak = null;

    /**
     * Filters in session.
     */
    protected ?array $sessionFilters = null;

    // Lazy parameters
    protected array $lazyAddColumn = [];
    protected array $lazyHiddenColumns = [];
    protected array $lazyVisibleColumns = [];
    protected array $lazyHideShowColumns = [];

    // Lazy parameters for the action column
    protected mixed $actionsColumnSize;
    protected mixed $actionsColumnTitle;

    protected ?TranslatorInterface $translator = null;
    protected ?string $charset = null;

    /**
     * @param string                   $id     set if you are using more then one grid inside controller
     * @param GridConfigInterface|null $config The grid configuration.
     */
    public function __construct(
        protected RouterInterface $router,
        protected AuthorizationCheckerInterface $securityContext,
        protected ManagerRegistry $doctrine,
        protected Manager $manager,
        protected HttpKernelInterface $kernel,
        protected Environment $twig,
        RequestStack $requestStack,
        ?string $id = '',
        protected ?GridConfigInterface $config = null
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->session = $this->request->getSession();
        $this->id = $id;

        $this->columns = new Columns($this->securityContext);

        $this->routeParameters = $this->request->attributes->all();
        foreach (\array_keys($this->routeParameters) as $key) {
            if (\str_starts_with($key, '_')) {
                unset($this->routeParameters[$key]);
            }
        }
    }

    public function initialize(): static
    {
        $config = $this->config;

        if (!$config) {
            return $this;
        }

        $this->setPersistence($config->isPersisted());

        // Route parameters
        $routeParameters = $config->getRouteParameters() ?? [];
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

            $source->initialise($this->doctrine, $this->manager);

            if ($source instanceof Entity) {
                $groupBy = $config->getGroupBy();
                if (null !== $groupBy) {
                    if (!\is_array($groupBy)) {
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

    public function handleRequest(Request $request): static
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
     * @throws \InvalidArgumentException
     */
    public function setSource(Source $source): static
    {
        if (null !== $this->source) {
            throw new \InvalidArgumentException(self::SOURCE_ALREADY_SETTED_EX_MSG);
        }

        $this->source = $source;

        $this->source->initialise($this->doctrine, $this->manager);

        // Get columns from the source
        $this->source->getColumns($this->columns);

        return $this;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    /**
     * Handle the grid redirection, export, etc..
     */
    public function isReadyForRedirect(): bool
    {
        if (null === $this->source) {
            throw new \RuntimeException(self::SOURCE_NOT_SETTED_EX_MSG);
        }

        if (null !== $this->redirect) {
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

        if (null === $this->redirect || ($this->request->isXmlHttpRequest() && !$this->isReadyForExport)) {
            if ($this->newSession) {
                $this->setDefaultSessionData();
            }

            $this->processPermanentFilters();

            // Configures the grid with the data read from the session.
            $this->processSessionData();

            $this->prepare();

            $this->redirect = false;
        }

        return $this->redirect;
    }

    protected function getCurrentUri(): string
    {
        return $this->request->getScheme().'://'.$this->request->getHttpHost().$this->request->getBaseUrl().$this->request->getPathInfo();
    }

    protected function processPersistence(): void
    {
        $referer = \strtok($this->request->headers->get('referer') ?? '', '?');

        // Persistence or reset - kill previous session
        if (isset($this->requestData[self::REQUEST_QUERY_RESET])
            || (!$this->request->isXmlHttpRequest() && !$this->persistence && $referer !== $this->getCurrentUri())) {
            $this->session->remove($this->hash);
        }

        if (null === $this->session->get($this->hash)) {
            $this->newSession = true;
        }
    }

    protected function processLazyParameters(): void
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

            foreach (\array_diff($columnNames, $this->lazyVisibleColumns) as $columnId) {
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
    protected function processRequestData(): void
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
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     */
    protected function processMassActions(?int $actionId): void
    {
        if ($actionId > -1) {
            if (\array_key_exists($actionId, $this->massActions)) {
                $action = $this->massActions[$actionId];
                $actionAllKeys = (bool) $this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED);
                $actionKeys = false === $actionAllKeys ? \array_keys((array) $this->getFromRequest(MassActionColumn::ID)) : [];

                $this->processSessionData();
                if ($actionAllKeys) {
                    $this->page = 0;
                    $this->limit = 0;
                }

                $this->prepare();

                if (true === $actionAllKeys) {
                    foreach ($this->rows as $row) {
                        $actionKeys[] = $row->getPrimaryFieldValue();
                    }
                }

                if (\is_callable($action->getCallback())) {
                    $this->massActionResponse = \call_user_func($action->getCallback(), $actionKeys, $actionAllKeys, $this->session, $action->getParameters());
                } elseif (\str_contains($action->getCallback(), ':')) {
                    $path = \array_merge(
                        [
                            'primaryKeys' => $actionKeys,
                            'allPrimaryKeys' => $actionAllKeys,
                            '_controller' => $action->getCallback(),
                        ],
                        $action->getParameters()
                    );

                    $subRequest = $this->request->duplicate([], null, $path);

                    $this->massActionResponse = $this->kernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                } else {
                    throw new \RuntimeException(\sprintf(self::MASS_ACTION_CALLBACK_NOT_VALID_EX_MSG, $action->getCallback()));
                }
            } else {
                throw new \OutOfBoundsException(\sprintf(self::MASS_ACTION_NOT_DEFINED_EX_MSG, $actionId));
            }
        }
    }

    /**
     * Process exports.
     *
     * @throws \OutOfBoundsException
     */
    protected function processExports(?int $exportId): bool
    {
        if ($exportId > -1) {
            if (\array_key_exists($exportId, $this->exports)) {
                $this->isReadyForExport = true;

                $this->processSessionData();
                $this->page = 0;
                $this->limit = 0;
                $this->prepare();

                $export = $this->exports[$exportId];
                $export->setTwig($this->twig)
                    ->setRouter($this->router)
                    ->setTranslator($this->getTranslator());
                $export->computeData($this);

                $this->exportResponse = $export->getResponse();

                return true;
            }
            throw new \OutOfBoundsException(\sprintf(self::EXPORT_NOT_DEFINED_EX_MSG, $exportId));
        }

        return false;
    }

    /**
     * Process tweaks.
     *
     * @throws \OutOfBoundsException
     */
    protected function processTweaks(?string $tweakId): bool
    {
        if (null !== $tweakId) {
            if (\array_key_exists($tweakId, $this->tweaks)) {
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
                        if (\array_key_exists($id, $this->tweaks) && isset($activeTweaks[$this->tweaks[$id]['group']])) {
                            unset($activeTweaks[$this->tweaks[$id]['group']]);
                        }
                    }

                    $this->set('tweaks', $activeTweaks);
                }

                if (isset($tweak['addActiveTweaks'])) {
                    $addActiveTweaks = (array) $tweak['addActiveTweaks'];
                    $activeTweaks = $this->getActiveTweaks();
                    foreach ($addActiveTweaks as $id) {
                        if (\array_key_exists($id, $this->tweaks)) {
                            $activeTweaks[$this->tweaks[$id]['group']] = $id;
                        }
                    }

                    $this->set('tweaks', $activeTweaks);
                }

                $this->saveSession();

                return true;
            }
            throw new \OutOfBoundsException(\sprintf(self::TWEAK_NOT_DEFINED_EX_MSG, $tweakId));
        }

        return false;
    }

    protected function processRequestFilters(): bool
    {
        $filtering = false;
        foreach ($this->columns as $column) {
            if ($column->isFilterable()) {
                $ColumnId = $column->getId();

                // Get data from request
                $data = $this->getFromRequest($ColumnId);

                // if no item is selectd in multi select filter : simulate empty first choice
                if (null === $data
                    && 'select' === $column->getFilterType()
                    && true === $column->getSelectMulti()
                    && null === $this->getFromRequest(self::REQUEST_QUERY_PAGE)
                    && null === $this->getFromRequest(self::REQUEST_QUERY_ORDER)
                    && null === $this->getFromRequest(self::REQUEST_QUERY_LIMIT)
                    && (null === $this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION) || '-1' === $this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION))) {
                    $data = ['from' => ''];
                }

                // Store in the session
                $this->set($ColumnId, $data);

                // Filtering ?
                if (!$filtering && null !== $data) {
                    $filtering = true;
                }
            }
        }

        return $filtering;
    }

    protected function processPage(?int $page, bool $filtering = false): void
    {
        // Set to the first page if this is a request of order, limit, mass action or filtering
        if ($filtering
            || null !== $this->getFromRequest(self::REQUEST_QUERY_ORDER)
            || null !== $this->getFromRequest(self::REQUEST_QUERY_LIMIT)
            || null !== $this->getFromRequest(self::REQUEST_QUERY_MASS_ACTION)) {
            $this->set(self::REQUEST_QUERY_PAGE, 0);
        } else {
            $this->set(self::REQUEST_QUERY_PAGE, $page);
        }
    }

    protected function processOrder($order): void
    {
        if (null !== $order) {
            [$columnId, $columnOrder] = \explode('|', $order);

            $column = $this->columns->getColumnById($columnId);
            if ($column->isSortable() && \in_array(\strtolower($columnOrder), ['asc', 'desc'])) {
                $this->set(self::REQUEST_QUERY_ORDER, $order);
            }
        }
    }

    protected function processLimit($limit): void
    {
        if (isset($this->limits[$limit])) {
            $this->set(self::REQUEST_QUERY_LIMIT, $limit);
        }
    }

    protected function setDefaultSessionData(): void
    {
        // Default filters
        $this->processDefaultFilters();

        // Default page
        if (null !== $this->defaultPage) {
            if ((int) $this->defaultPage >= 0) {
                $this->set(self::REQUEST_QUERY_PAGE, $this->defaultPage);
            } else {
                throw new \InvalidArgumentException(self::NOT_VALID_PAGE_NUMBER_EX_MSG);
            }
        }

        // Default order
        if (null !== $this->defaultOrder) {
            [$columnId, $columnOrder] = \explode('|', $this->defaultOrder);

            $this->columns->getColumnById($columnId);
            if (\in_array(\strtolower($columnOrder), ['asc', 'desc'])) {
                $this->set(self::REQUEST_QUERY_ORDER, $this->defaultOrder);
            } else {
                throw new \InvalidArgumentException(\sprintf(self::COLUMN_ORDER_NOT_VALID_EX_MSG, $columnOrder));
            }
        }

        if (null !== $this->defaultLimit) {
            if ((int) $this->defaultLimit >= 0) {
                if (isset($this->limits[$this->defaultLimit])) {
                    $this->set(self::REQUEST_QUERY_LIMIT, $this->defaultLimit);
                } else {
                    throw new \InvalidArgumentException(\sprintf(self::LIMIT_NOT_DEFINED_EX_MSG, $this->defaultLimit));
                }
            } else {
                throw new \InvalidArgumentException(self::DEFAULT_LIMIT_NOT_VALID_EX_MSG);
            }
        }

        // Default tweak
        if (null !== $this->defaultTweak) {
            $this->processTweaks($this->defaultTweak);
        }
        $this->saveSession();
    }

    /**
     * Store permanent filters to the session and disable the filter capability for the column if there are permanent filters.
     */
    protected function processFilters(bool $permanent = true): void
    {
        foreach (($permanent ? $this->permanentFilters : $this->defaultFilters) as $columnId => $value) {
            // @var $column Column
            $column = $this->columns->getColumnById($columnId);

            if ($permanent) {
                // Disable the filter capability for the column
                $column->setFilterable(false);
            }

            // Convert simple value
            if (!\is_array($value) || !\is_string(\key($value))) {
                $value = ['from' => $value];
            }

            // Convert boolean value
            if (isset($value['from']) && \is_bool($value['from'])) {
                $value['from'] = $value['from'] ? '1' : '0';
            }

            // Convert simple value with select filter
            if ('select' === $column->getFilterType()) {
                if (isset($value['from']) && !\is_array($value['from'])) {
                    $value['from'] = [$value['from']];
                }

                if (isset($value['to']) && !\is_array($value['to'])) {
                    $value['to'] = [$value['to']];
                }
            }

            // Store in the session
            $this->set($columnId, $value);
        }
    }

    protected function processPermanentFilters(): void
    {
        $this->processFilters();
        $this->saveSession();
    }

    protected function processDefaultFilters(): void
    {
        $this->processFilters(false);
    }

    /**
     * Configures the grid with the data read from the session.
     */
    protected function processSessionData(): void
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
            [$columnId, $columnOrder] = \explode('|', $order);

            $this->columns->getColumnById($columnId)->setOrder($columnOrder);
        }

        // Limit
        if (($limit = $this->get(self::REQUEST_QUERY_LIMIT)) !== null) {
            $this->limit = $limit;
        } else {
            $this->limit = \key($this->limits);
        }
    }

    /**
     * Prepare Grid for Drawing.
     *
     * @throws \RuntimeException
     */
    protected function prepare(): static
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
            throw new \RuntimeException(self::NO_ROWS_RETURNED_EX_MSG);
        }

        if ($this->page > 0 && 0 === \count($this->rows)) {
            $this->page = 0;
            $this->prepare();

            return $this;
        }

        // add row actions column
        if (\count($this->rowActions) > 0) {
            foreach ($this->rowActions as $column => $rowActions) {
                if ($actionColumn = $this->columns->hasColumnById($column, true)) {
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

        // add mass actions column
        if (\count($this->massActions) > 0) {
            $this->columns->addColumn(new MassActionColumn(), 1);
        }

        $primaryColumnId = $this->columns->getPrimaryColumn()->getId();

        foreach ($this->rows as $row) {
            $row->setPrimaryField($primaryColumnId);
        }

        // get size
        if ($this->source->isDataLoaded()) {
            $this->source->populateSelectFiltersFromData($this->columns);
            $this->totalCount = $this->source->getTotalCountFromData($this->maxResults);
        } else {
            $this->source->populateSelectFilters($this->columns);
            $this->totalCount = $this->source->getTotalCount($this->maxResults);
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
    protected function getFromRequest(string $key): mixed
    {
        return $this->requestData[$key] ?? null;
    }

    /**
     * Reads data from the session.
     *
     * @param string $key A unique key identifying your data
     *
     * @return mixed Data associated with the key or null if the key is not found
     */
    protected function get(?string $key): mixed
    {
        return $this->sessionData[$key] ?? null;
    }

    /**
     * Writes data to the session.
     *
     * @param string $key  A unique key identifying the data
     * @param mixed  $data Data associated with the key
     */
    protected function set(string $key, mixed $data): void
    {
        // Only the filters values are removed from the session
        $fromIsEmpty = isset($data['from']) && ('' === $data['from'] || (\is_array($data['from']) && '' === $data['from'][0]));
        $toIsSet = isset($data['to']) && (\is_string($data['to']) && '' !== $data['to']);
        if ($fromIsEmpty && !$toIsSet) {
            if (\array_key_exists($key, $this->sessionData)) {
                unset($this->sessionData[$key]);
            }
        } elseif (null !== $data) {
            $this->sessionData[$key] = $data;
        }
    }

    protected function saveSession(): void
    {
        if (!empty($this->sessionData)) {
            $this->session->set($this->hash ?? '', $this->sessionData);
        }
    }

    protected function createHash(): void
    {
        $this->hash = 'grid_'.(empty($this->id) ? \md5($this->request->get('_controller').$this->columns->getHash().$this->source->getHash()) : $this->getId());
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    /**
     * Adds custom column to the grid.
     */
    public function addColumn(Column $column, int $position = 0): static
    {
        $this->lazyAddColumn[] = ['column' => $column, 'position' => $position];

        return $this;
    }

    /**
     * Get a column by its identifier.
     */
    public function getColumn(string $columnId): Column
    {
        foreach ($this->lazyAddColumn as $column) {
            if ($column['column']->getId() === $columnId) {
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
    public function getColumns(): array|Columns
    {
        return $this->columns;
    }

    /**
     * Returns true if column exists in columns and lazyAddColumn properties.
     */
    public function hasColumn(string $columnId): bool
    {
        foreach ($this->lazyAddColumn as $column) {
            if ($column['column']->getId() === $columnId) {
                return true;
            }
        }

        return $this->columns->hasColumnById($columnId);
    }

    /**
     * Sets Array of Columns to the grid.
     */
    public function setColumns(Columns $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Sets order of Columns passing an array of column ids
     * If the list of ids is uncomplete, the remaining columns will be
     * placed after.
     */
    public function setColumnsOrder(array $columnIds, bool $keepOtherColumns = true): static
    {
        $this->columns->setColumnsOrder($columnIds, $keepOtherColumns);

        return $this;
    }

    public function addMassAction(MassActionInterface $action): static
    {
        if (null === $action->getRole() || $this->securityContext->isGranted($action->getRole())) {
            $this->massActions[] = $action;
        }

        return $this;
    }

    /**
     * Returns Mass Actions.
     *
     * @return Action\MassAction[]
     */
    public function getMassActions(): Response|array
    {
        return $this->massActions;
    }

    /**
     * Add a tweak.
     *
     * @param string $title title of the tweak
     * @param array  $tweak array('filters' => array, 'order' => 'colomunId|order', 'page' => integer, 'limit' => integer, 'export' => integer, 'massAction' => integer)
     * @param string $id    id of the tweak matching the regex ^[0-9a-zA-Z_+-]+
     * @param string $group group of the tweak
     */
    public function addTweak(string $title, array $tweak, string $id = null, string $group = null): static
    {
        if (null !== $id && !\preg_match('/^[0-9a-zA-Z_+-]+$/', $id)) {
            throw new \InvalidArgumentException(\sprintf(self::TWEAK_MALFORMED_ID_EX_MSG, $id));
        }

        $tweak = \array_merge(['id' => $id, 'title' => $title, 'group' => $group], $tweak);
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
     */
    public function getTweaks(): array
    {
        $separator = \strpos($this->getRouteUrl(), '?') ? '&' : '?';
        $url = $this->getRouteUrl().$separator.$this->getHash().'['.self::REQUEST_QUERY_TWEAK.']=';

        foreach ($this->tweaks as $id => $tweak) {
            $this->tweaks[$id] = \array_merge($tweak, ['url' => $url.$id]);
        }

        return $this->tweaks;
    }

    public function getActiveTweaks(): array
    {
        return (array) $this->get('tweaks');
    }

    /**
     * Returns a tweak.
     */
    public function getTweak(string $id): array
    {
        $tweaks = $this->getTweaks();
        if (isset($tweaks[$id])) {
            return $tweaks[$id];
        }

        throw new \InvalidArgumentException(\sprintf(self::NOT_VALID_TWEAK_ID_EX_MSG, $id));
    }

    /**
     * Returns tweaks with a specific group.
     */
    public function getTweaksGroup($group): array
    {
        $tweaksGroup = $this->getTweaks();

        foreach ($tweaksGroup as $id => $tweak) {
            if ($tweak['group'] !== $group) {
                unset($tweaksGroup[$id]);
            }
        }

        return $tweaksGroup;
    }

    public function getActiveTweakGroup(string $group): int|string
    {
        $tweaks = $this->getActiveTweaks();

        return $tweaks[$group] ?? -1;
    }

    /**
     * Adds Row Action.
     */
    public function addRowAction(RowActionInterface $action): static
    {
        if (null === $action->getRole() || $this->securityContext->isGranted($action->getRole())) {
            $this->rowActions[$action->getColumn()][] = $action;
        }

        return $this;
    }

    /**
     * Returns Row Actions.
     *
     * @return Action\RowAction[]
     */
    public function getRowActions(): array
    {
        return $this->rowActions;
    }

    /**
     * Sets template for export.
     *
     * @throws \RuntimeException
     */
    public function setTemplate(Template|string|null $template): static
    {
        if (null !== $template) {
            if ($template instanceof Template) {
                $template = '__SELF__'.$template->getTemplateName();
            }

            $this->set(self::REQUEST_QUERY_TEMPLATE, $template);
            $this->saveSession();
        }

        return $this;
    }

    /**
     * Returns template.
     */
    public function getTemplate(): Template|string
    {
        return $this->get(self::REQUEST_QUERY_TEMPLATE);
    }

    /**
     * Adds Export.
     */
    public function addExport(ExportInterface $export): static
    {
        if (null === $export->getRole() || $this->securityContext->isGranted($export->getRole())) {
            $this->exports[] = $export;
        }

        return $this;
    }

    /**
     * Returns exports.
     *
     * @return ExportInterface[]
     */
    public function getExports(): array
    {
        return $this->exports;
    }

    /**
     * Returns the export response.
     *
     * @return Export[]
     */
    public function getExportResponse(): Response|array|null
    {
        return $this->exportResponse;
    }

    /**
     * Returns the mass action response.
     *
     * @return Export[]
     */
    public function getMassActionResponse(): Response|array|null
    {
        return $this->massActionResponse;
    }

    /**
     * Sets Route Parameters.
     */
    public function setRouteParameter(string $parameter, mixed $value): static
    {
        $this->routeParameters[$parameter] = $value;

        return $this;
    }

    /**
     * Returns Route Parameters.
     */
    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function setRouteUrl(string $routeUrl): static
    {
        $this->routeUrl = $routeUrl;

        return $this;
    }

    public function getRouteUrl(): ?string
    {
        if (null === $this->routeUrl) {
            $this->routeUrl = $this->router->generate($this->request->get('_route') ?? '', $this->getRouteParameters());
        }

        return $this->routeUrl;
    }

    public function isReadyForExport(): bool
    {
        return $this->isReadyForExport;
    }

    public function isMassActionRedirect(): bool
    {
        return $this->massActionResponse instanceof Response;
    }

    /**
     * Set value for filters.
     *
     * @param array $filters   Hash of columnName => initValue
     * @param bool  $permanent filters ?
     */
    protected function setFilters(array $filters, bool $permanent = true): static
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
     */
    public function setPermanentFilters(array $filters): static
    {
        return $this->setFilters($filters);
    }

    /**
     * Set default value for filters.
     *
     * @param array $filters Hash of columnName => initValue
     */
    public function setDefaultFilters(array $filters): static
    {
        return $this->setFilters($filters, false);
    }

    /**
     * Set the default grid order.
     */
    public function setDefaultOrder(string $columnId, ?string $order): static
    {
        $order = \strtolower($order ?? '');
        $this->defaultOrder = "$columnId|$order";

        return $this;
    }

    /**
     * Sets unique filter identification.
     */
    public function setId(string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns unique filter identifier.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setPersistence(bool $persistence): static
    {
        $this->persistence = $persistence;

        return $this;
    }

    public function getPersistence(): bool
    {
        return $this->persistence;
    }

    public function getDataJunction(): int
    {
        return $this->dataJunction;
    }

    public function setDataJunction(int $dataJunction): static
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
     */
    public function setLimits(mixed $limits): static
    {
        if (\is_array($limits)) {
            if (0 === (int) \key($limits)) {
                $this->limits = \array_combine($limits, $limits);
            } else {
                $this->limits = $limits;
            }
        } elseif (\is_int($limits)) {
            $this->limits = [$limits => (string) $limits];
        } else {
            throw new \InvalidArgumentException(self::NOT_VALID_LIMIT_EX_MSG);
        }

        return $this;
    }

    /**
     * Returns limits.
     */
    public function getLimits(): array
    {
        return $this->limits;
    }

    /**
     * Returns selected Limit (Rows Per Page).
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Sets default Limit.
     */
    public function setDefaultLimit(int $limit): static
    {
        $this->defaultLimit = $limit;

        return $this;
    }

    /**
     * Sets default Page.
     */
    public function setDefaultPage(int $page): static
    {
        $this->defaultPage = $page - 1;

        return $this;
    }

    /**
     * Sets default Tweak.
     */
    public function setDefaultTweak(string $tweakId): static
    {
        $this->defaultTweak = $tweakId;

        return $this;
    }

    /**
     * Sets current Page (internal).
     *
     * @throws \InvalidArgumentException
     */
    public function setPage(int $page): static
    {
        if ($page >= 0) {
            $this->page = $page;
        } else {
            throw new \InvalidArgumentException(self::PAGE_NOT_VALID_EX_MSG);
        }

        return $this;
    }

    /**
     * Returns current page.
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Returnd grid display data as rows - internal helper for templates.
     */
    public function getRows(): ?Rows
    {
        return $this->rows;
    }

    /**
     * Return count of available pages.
     */
    public function getPageCount(): int
    {
        $pageCount = 1;
        if ($this->getLimit() > 0) {
            $pageCount = (int) \ceil($this->getTotalCount() / $this->getLimit());
        }

        return $pageCount;
    }

    /**
     * Returns count of filtred rows(items) from source.
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    /**
     * Sets the max results of the grid.
     *
     * @throws \InvalidArgumentException
     */
    public function setMaxResults(int $maxResults = null): static
    {
        if ($maxResults < 0 && null !== $maxResults) {
            throw new \InvalidArgumentException(self::NOT_VALID_MAX_RESULT_EX_MSG);
        }

        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * Return true if the grid is filtered.
     */
    public function isFiltered(): bool
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
     */
    public function isTitleSectionVisible(): bool
    {
        if (true === $this->showTitles) {
            foreach ($this->columns as $column) {
                if ($column->getTitle()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return true if filter panel is visible in template - internal helper.
     */
    public function isFilterSectionVisible(): bool
    {
        if (true === $this->showFilters) {
            foreach ($this->columns as $column) {
                if ($column->isFilterable() && 'massaction' !== $column->getType() && 'actions' !== $column->getType()) {
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
    public function isPagerSectionVisible(): bool
    {
        $limits = $this->getLimits();

        if (empty($limits)) {
            return false;
        }

        // true when totalCount rows exceed the minimum pager limit
        return \min(\array_keys($limits)) < $this->totalCount;
    }

    /**
     * Hides Filters Panel.
     */
    public function hideFilters(): static
    {
        $this->showFilters = false;

        return $this;
    }

    /**
     * Hides Titles panel.
     */
    public function hideTitles(): static
    {
        $this->showTitles = false;

        return $this;
    }

    /**
     * Adds Column Extension - internal helper.
     */
    public function addColumnExtension(Column $extension): static
    {
        $this->columns->addExtension($extension);

        return $this;
    }

    /**
     * Set a prefix title.
     */
    public function setPrefixTitle(string $prefixTitle): static
    {
        $this->prefixTitle = $prefixTitle;

        return $this;
    }

    /**
     * Get the prefix title.
     */
    public function getPrefixTitle(): string
    {
        return $this->prefixTitle;
    }

    /**
     * Set the no data message.
     */
    public function setNoDataMessage(string $noDataMessage): static
    {
        $this->noDataMessage = $noDataMessage;

        return $this;
    }

    /**
     * Get the no data message.
     */
    public function getNoDataMessage(): ?string
    {
        return $this->noDataMessage;
    }

    /**
     * Set the no result message.
     */
    public function setNoResultMessage(string $noResultMessage): static
    {
        $this->noResultMessage = $noResultMessage;

        return $this;
    }

    /**
     * Get the no result message.
     */
    public function getNoResultMessage(): ?string
    {
        return $this->noResultMessage;
    }

    /**
     * Sets a list of columns to hide when the grid is output.
     */
    public function setHiddenColumns(array|int $columnIds): static
    {
        $this->lazyHiddenColumns = \is_int($columnIds) ? [$columnIds] : $columnIds;

        return $this;
    }

    /**
     * Sets a list of columns to show when the grid is output
     * It acts as a mask; Other columns will be set as hidden.
     */
    public function setVisibleColumns(array|int $columnIds): static
    {
        $this->lazyVisibleColumns = \is_int($columnIds) ? [$columnIds] : $columnIds;

        return $this;
    }

    /**
     * Sets on the visibility of columns.
     */
    public function showColumns(string|array $columnIds): static
    {
        foreach ((array) $columnIds as $columnId) {
            $this->lazyHideShowColumns[$columnId] = true;
        }

        return $this;
    }

    /**
     * Sets off the visiblilty of columns.
     */
    public function hideColumns(string|array $columnIds): static
    {
        foreach ((array) $columnIds as $columnId) {
            $this->lazyHideShowColumns[$columnId] = false;
        }

        return $this;
    }

    /**
     * Sets the size of the default action column.
     */
    public function setActionsColumnSize(int $size): static
    {
        $this->actionsColumnSize = $size;

        return $this;
    }

    /**
     * Sets the title of the default action column.
     */
    public function setActionsColumnTitle(string $title): static
    {
        $this->actionsColumnTitle = $title;

        return $this;
    }

    /**
     * Default delete action.
     */
    public function deleteAction(array $ids): void
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

    // HELPER

    /**
     * Redirects or Renders a view - helper function.
     *
     * @param string|array|null $param1 The view name or an array of parameters to pass to the view
     * @param string|array|null $param2 The view name or an array of parameters to pass to the view
     */
    public function getGridResponse(string|array $param1 = null, string|array $param2 = null): Response|array
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
        }

        if (\is_array($param1) || null === $param1) {
            $parameters = (array) $param1;
            $view = $param2;
        } else {
            $parameters = (array) $param2;
            $view = $param1;
        }

        $parameters = \array_merge(['grid' => $this], $parameters);

        if (null === $view) {
            return $parameters;
        }

        $content = $this->twig->render($view, $parameters);

        return new Response($content);
    }

    /**
     * Extract raw data of columns.
     *
     * @param string|array $columnNames  The name of the extract columns. If null, all the columns are return.
     * @param bool         $namedIndexes If sets to true, named indexes will be used
     *
     * @return array Raw data of columns
     */
    public function getRawData(string|array $columnNames = null, bool $namedIndexes = true): array
    {
        if (null === $columnNames) {
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
     * @throws \RuntimeException
     *
     * @return Filter[]
     */
    public function getFilters(): array
    {
        if (null === $this->hash) {
            throw new \RuntimeException(self::GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG);
        }

        if (null === $this->sessionFilters) {
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
     * @throws \RuntimeException
     */
    public function getFilter(string $columnId): ?Filter
    {
        if (null === $this->hash) {
            throw new \RuntimeException(self::GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG);
        }

        $sessionFilters = $this->getFilters();

        return $sessionFilters[$columnId] ?? null;
    }

    /**
     * A filter of the column is stored in session ?
     *
     * @param string $columnId Id of the column
     *
     * @throws \RuntimeException
     */
    public function hasFilter(string $columnId): bool
    {
        if (null === $this->hash) {
            throw new \RuntimeException(self::HAS_FILTER_NO_REQUEST_HANDLED_EX_MSG);
        }

        return null !== $this->getFilter($columnId);
    }

    public function getDefaultOrder(): ?string
    {
        return $this->defaultOrder;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function getLazyAddColumn(): ?array
    {
        return $this->lazyAddColumn;
    }

    public function getLazyHiddenColumns(): ?array
    {
        return $this->lazyHiddenColumns;
    }

    public function getLazyVisibleColumns(): ?array
    {
        return $this->lazyVisibleColumns;
    }

    public function getLazyHideShowColumns(): ?array
    {
        return $this->lazyHideShowColumns;
    }

    public function getDefaultTweak(): ?string
    {
        return $this->defaultTweak;
    }

    public function isShowFilters(): ?bool
    {
        return $this->showFilters;
    }

    public function isShowTitles(): ?bool
    {
        return $this->showTitles;
    }

    public function getActionsColumnSize(): ?int
    {
        return $this->actionsColumnSize;
    }

    public function getActionsColumnTitle(): ?string
    {
        return $this->actionsColumnTitle;
    }

    public function getPermanentFilters(): ?array
    {
        return $this->permanentFilters;
    }

    public function getDefaultFilters(): ?array
    {
        return $this->defaultFilters;
    }

    public function isNewSession(): ?bool
    {
        return $this->newSession;
    }

    public function getTranslator(): ?TranslatorInterface
    {
        return $this->translator;
    }

    public function setTranslator(?TranslatorInterface $translator): self
    {
        $this->translator = $translator;

        return $this;
    }

    public function getCharset(): ?string
    {
        return $this->charset;
    }

    public function setCharset(?string $charset): self
    {
        $this->charset = $charset;

        return $this;
    }
}

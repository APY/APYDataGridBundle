<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\Source\Source;

/**
 * A basic grid configuration.
 *
 * @author  Quentin Ferrer
 */
class GridConfigBuilder implements GridConfigBuilderInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var GridTypeInterface
     */
    protected $type;

    /**
     * @var Source
     */
    protected $source;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var array
     */
    protected $routeParameters = [];

    /**
     * @var bool
     */
    protected $persistence;

    /**
     * @var int
     */
    protected $page = 0;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var int
     */
    protected $maxResults;

    /**
     * @var bool
     */
    protected $filterable = true;

    /**
     * @var bool
     */
    protected $sortable = true;

    /**
     * @var string
     */
    protected $sortBy;

    /**
     * @var string
     */
    protected $order = 'asc';

    /**
     * @var string|array
     */
    protected $groupBy;

    /**
     * @var array
     */
    protected $actions;

    /**
     * @var array
     */
    protected $options;

    /**
     * Constructor.
     *
     * @param string $name    The grid name
     * @param array  $options The grid options
     */
    public function __construct($name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set Source.
     *
     * @param Source $source
     *
     * @return $this
     */
    public function setSource(Source $source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set Type.
     *
     * @param GridTypeInterface $type
     *
     * @return $this
     */
    public function setType(GridTypeInterface $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set Route.
     *
     * @param mixed $route
     *
     * @return $this
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Set RouteParameters.
     *
     * @param array $routeParameters
     *
     * @return $this
     */
    public function setRouteParameters(array $routeParameters)
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isPersisted()
    {
        return $this->persistence;
    }

    /**
     * Set Persistence.
     *
     * @param mixed $persistence
     *
     * @return $this
     */
    public function setPersistence($persistence)
    {
        $this->persistence = $persistence;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set Page.
     *
     * @param int $page
     *
     * @return $this
     */
    public function setPage($page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->options);
    }

    /**
     * {@inheritdoc}
     */
    public function getOption($name, $default = null)
    {
        return array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxPerPage()
    {
        return $this->limit;
    }

    /**
     * Set Limit.
     *
     * @param int $limit
     *
     * @return $this
     */
    public function setMaxPerPage($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Get MaxResults.
     *
     * @return int
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * Set MaxResults.
     *
     * @param int $maxResults
     *
     * @return $this
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * Set Sortable.
     *
     * @param bool $sortable
     *
     * @return $this
     */
    public function setSortable($sortable)
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * Set Filterable.
     *
     * @param bool $filterable
     *
     * @return $this
     */
    public function setFilterable($filterable)
    {
        $this->filterable = $filterable;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set Order.
     *
     * @param string $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set SortBy.
     *
     * @param string $sortBy
     *
     * @return $this
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupBy()
    {
        return $this->groupBy;
    }

    /**
     * Set GroupBy.
     *
     * @param array|string $groupBy
     *
     * @return $this
     */
    public function setGroupBy($groupBy)
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    /**
     * @param RowActionInterface $action
     *
     * @return $this
     */
    public function addAction(RowActionInterface $action)
    {
        $this->actions[$action->getColumn()][] = $action;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridConfig()
    {
        $config = clone $this;

        return $config;
    }
}

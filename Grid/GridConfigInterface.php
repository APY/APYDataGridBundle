<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Source\Source;

/**
 * The configuration of a {@link Grid} object.
 *
 * @author  Quentin Ferrer
 */
interface GridConfigInterface
{
    /**
     * Returns the name of the grid.
     *
     * @return string The grid name
     */
    public function getName();

    /**
     * Returns the source of the grid.
     *
     * @return Source The source of the grid.
     */
    public function getSource();

    /**
     * Returns the grid type used to construct the grid.
     *
     * @return GridTypeInterface The grid's type.
     */
    public function getType();

    /**
     * Returns the route of the grid.
     *
     * @return string The route of the grid.
     */
    public function getRoute();

    /**
     * Returns the route parameters of the grid.
     *
     * @return array The route parameters.
     */
    public function getRouteParameters();

    /**
     * Returns whether the grid is persisted.
     *
     * @return bool Whether the grid is persisted.
     */
    public function isPersisted();

    /**
     * Returns the default page.
     *
     * @return int The default page.
     */
    public function getPage();

    /**
     * Returns all options passed during the construction of grid.
     *
     * @return array
     */
    public function getOptions();

    /**
     * Returns whether a specific option exists.
     *
     * @param string $name The option name.
     *
     * @return bool
     */
    public function hasOption($name);

    /**
     * Returns the value of a specific option.
     *
     * @param string $name    The option name.
     * @param mixed  $default The value returned if the option does not exist.
     *
     * @return mixed The option value
     */
    public function getOption($name, $default = null);

    /**
     * Returns whether the grid is filterable.
     *
     * @return bool Whether the grid is filterable.
     */
    public function isFilterable();

    /**
     * Returns whether the grid is sortable.
     *
     * @return bool Whether the grid is sortable.
     */
    public function isSortable();

    /**
     * Returns the maximum number of results of the grid.
     *
     * @return int The maximum number of results of the grid.
     */
    public function getMaxResults();

    /**
     * Returns the maximum number of items per page.
     *
     * @return int The maximum number of items per page.
     */
    public function getMaxPerPage();

    /**
     * Returns the default order.
     *
     * @return string The default order.
     */
    public function getOrder();

    /**
     * Returns the default sort field.
     *
     * @return string The default sort field.
     */
    public function getSortBy();

    /**
     * Returns the default group field.
     *
     * @return string|array
     */
    public function getGroupBy();
}

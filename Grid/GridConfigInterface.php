<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Source\Source;

interface GridConfigInterface
{
    /**
     * Returns the name of the grid.
     */
    public function getName(): string;

    /**
     * Returns the source of the grid.
     */
    public function getSource(): ?Source;

    /**
     * Returns the grid type used to construct the grid.
     */
    public function getType(): ?GridTypeInterface;

    /**
     * Returns the route of the grid.
     */
    public function getRoute(): ?string;

    /**
     * Returns the route parameters of the grid.
     */
    public function getRouteParameters(): array;

    /**
     * Returns whether the grid is persisted.
     */
    public function isPersisted(): bool;

    /**
     * Returns the default page.
     */
    public function getPage(): int;

    /**
     * Returns all options passed during the construction of grid.
     */
    public function getOptions(): array;

    /**
     * Returns whether a specific option exists.
     */
    public function hasOption(string $name): bool;

    /**
     * Returns the value of a specific option.
     *
     * @param string     $name    The option name.
     * @param mixed|null $default The value returned if the option does not exist.
     *
     * @return mixed The option value
     */
    public function getOption(string $name, mixed $default = null): mixed;

    /**
     * Returns whether the grid is filterable.
     */
    public function isFilterable(): bool;

    /**
     * Returns whether the grid is sortable.
     */
    public function isSortable(): bool;

    /**
     * Returns the maximum number of results of the grid.
     */
    public function getMaxResults(): ?int;

    /**
     * Returns the maximum number of items per page.
     */
    public function getMaxPerPage(): ?int;

    /**
     * Returns the default order.
     */
    public function getOrder(): ?string;

    /**
     * Returns the default sort field.
     */
    public function getSortBy(): ?string;

    /**
     * Returns the default group field.
     */
    public function getGroupBy(): array|string|null;
}

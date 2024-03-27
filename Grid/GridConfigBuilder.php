<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\Source\Source;

class GridConfigBuilder implements GridConfigBuilderInterface
{
    protected string $name;

    protected ?GridTypeInterface $type = null;

    protected ?Source $source = null;

    protected ?string $route = null;

    protected array $routeParameters = [];

    protected bool $persistence = false;

    protected int $page = 0;

    protected ?int $limit = null;

    protected ?int $maxResults = null;

    protected bool $filterable = true;

    protected bool $sortable = true;

    protected ?string $sortBy = null;

    protected string $order = 'asc';

    protected string|array|null $groupBy = null;

    protected ?array $actions = null;

    protected array $options;

    public function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSource(): ?Source
    {
        return $this->source;
    }

    public function setSource(Source $source): static
    {
        $this->source = $source;

        return $this;
    }

    public function getType(): ?GridTypeInterface
    {
        return $this->type;
    }

    public function setType(GridTypeInterface $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(mixed $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function setRouteParameters(array $routeParameters): static
    {
        $this->routeParameters = $routeParameters;

        return $this;
    }

    public function isPersisted(): bool
    {
        return $this->persistence;
    }

    public function setPersistence(mixed $persistence): static
    {
        $this->persistence = $persistence;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function hasOption(string $name): bool
    {
        return \array_key_exists($name, $this->options);
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return \array_key_exists($name, $this->options) ? $this->options[$name] : $default;
    }

    public function getMaxPerPage(): ?int
    {
        return $this->limit;
    }

    public function setMaxPerPage(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    public function getMaxResults(): ?int
    {
        return $this->maxResults;
    }

    public function setMaxResults(?int $maxResults): static
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function setSortable(bool $sortable): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function setFilterable(bool $filterable): static
    {
        $this->filterable = $filterable;

        return $this;
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): static
    {
        $this->order = $order;

        return $this;
    }

    public function getSortBy(): ?string
    {
        return $this->sortBy;
    }

    public function setSortBy(?string $sortBy): static
    {
        $this->sortBy = $sortBy;

        return $this;
    }

    public function getGroupBy(): array|string|null
    {
        return $this->groupBy;
    }

    public function setGroupBy(array|string|null $groupBy): static
    {
        $this->groupBy = $groupBy;

        return $this;
    }

    public function addAction(RowActionInterface $action): static
    {
        $this->actions[$action->getColumn()][] = $action;

        return $this;
    }

    public function getGridConfig(): self|GridConfigInterface
    {
        return clone $this;
    }
}

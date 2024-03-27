<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Source\Source;

class JoinColumn extends TextColumn
{
    protected array $joinColumns = [];

    protected int $dataJunction = self::DATA_DISJUNCTION;

    public function __initialize(array $params): void
    {
        parent::__initialize($params);

        $this->setJoinColumns($this->getParam('columns', []));
        $this->setSeparator($this->getParam('separator', '&nbsp;'));

        $this->setVisibleForSource(true);
        $this->setIsManualField(true);
    }

    public function setJoinColumns(array $columns): static
    {
        $this->joinColumns = $columns;

        return $this;
    }

    public function getJoinColumns(): array
    {
        return $this->joinColumns;
    }

    public function getFilters(Source|string $source): array
    {
        $filters = [];

        // Apply same filters on each column
        foreach ($this->joinColumns as $columnName) {
            $tempFilters = parent::getFilters($source);

            foreach ($tempFilters as $filter) {
                $filter->setColumnName($columnName);
            }

            $filters = \array_merge($filters, $tempFilters);
        }

        return $filters;
    }

    public function getType(): string
    {
        return 'join';
    }
}

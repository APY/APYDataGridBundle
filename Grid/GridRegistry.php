<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\ColumnAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\ColumnNotFoundException;
use APY\DataGridBundle\Grid\Exception\TypeAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\TypeNotFoundException;

class GridRegistry implements GridRegistryInterface
{
    /**
     * @var GridTypeInterface[]
     */
    private array $types = [];

    /**
     * @var Column[]
     */
    private array $columns = [];

    public function addType(GridTypeInterface $type): static
    {
        $name = $type->getName();

        if ($this->hasType($name)) {
            throw new TypeAlreadyExistsException($name);
        }

        $this->types[$name] = $type;

        return $this;
    }

    public function getType(string $name): GridTypeInterface
    {
        if (!$this->hasType($name)) {
            throw new TypeNotFoundException($name);
        }

        return $this->types[$name];
    }

    public function hasType(string $name): bool
    {
        if (isset($this->types[$name])) {
            return true;
        }

        return false;
    }

    public function addColumn(Column $column): static
    {
        $type = $column->getType();

        if ($this->hasColumn($type)) {
            throw new ColumnAlreadyExistsException($type);
        }

        $this->columns[$type] = $column;

        return $this;
    }

    public function getColumn(string $type): Column
    {
        if (!$this->hasColumn($type)) {
            throw new ColumnNotFoundException($type);
        }

        return $this->columns[$type];
    }

    public function hasColumn(string $type): bool
    {
        if (isset($this->columns[$type])) {
            return true;
        }

        return false;
    }
}

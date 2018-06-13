<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\ColumnAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\ColumnNotFoundException;
use APY\DataGridBundle\Grid\Exception\TypeAlreadyExistsException;
use APY\DataGridBundle\Grid\Exception\TypeNotFoundException;

/**
 * The central registry of the Grid component.
 *
 * @author  Quentin Ferrer
 */
class GridRegistry implements GridRegistryInterface
{
    /**
     * List of types.
     *
     * @var GridTypeInterface[]
     */
    private $types = [];

    /**
     * List of columns.
     *
     * @var Column[]
     */
    private $columns = [];

    /**
     * Add a grid type.
     *
     * @param GridTypeInterface $type
     *
     * @return $this
     */
    public function addType(GridTypeInterface $type)
    {
        $name = $type->getName();

        if ($this->hasType($name)) {
            throw new TypeAlreadyExistsException($name);
        }

        $this->types[$name] = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType($name)
    {
        if (!$this->hasType($name)) {
            throw new TypeNotFoundException($name);
        }

        $type = $this->types[$name];

        return $type;
    }

    /**
     * {@inheritdoc}
     */
    public function hasType($name)
    {
        if (isset($this->types[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Add a column type.
     *
     * @param Column $column
     *
     * @return $this
     */
    public function addColumn(Column $column)
    {
        $type = $column->getType();

        if ($this->hasColumn($type)) {
            throw new ColumnAlreadyExistsException($type);
        }

        $this->columns[$type] = $column;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($type)
    {
        if (!$this->hasColumn($type)) {
            throw new ColumnNotFoundException($type);
        }

        $column = $this->columns[$type];

        return $column;
    }

    /**
     * {@inheritdoc}
     */
    public function hasColumn($type)
    {
        if (isset($this->columns[$type])) {
            return true;
        }

        return false;
    }
}

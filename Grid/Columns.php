<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Helper\ColumnsIterator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Columns implements \IteratorAggregate, \Countable
{
    protected array $columns = [];
    protected array $extensions = [];

    public const MISSING_COLUMN_EX_MSG = 'Column with id "%s" doesn\'t exists';

    protected AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getIterator(bool $showOnlySourceColumns = false): \Traversable
    {
        return new ColumnsIterator(new \ArrayIterator($this->columns), $showOnlySourceColumns);
    }

    public function addColumn(Column $column, int $position = 0): static
    {
        $column->setAuthorizationChecker($this->authorizationChecker);

        if (0 === $position) {
            $this->columns[] = $column;
        } else {
            if ($position > 0) {
                --$position;
            } else {
                $position = \max(0, \count($this->columns) + $position);
            }

            $head = \array_slice($this->columns, 0, $position);
            $tail = \array_slice($this->columns, $position);
            $this->columns = \array_merge($head, [$column], $tail);
        }

        return $this;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getColumnById(string $columnId): Column|ActionsColumn|bool
    {
        if (($column = $this->hasColumnById($columnId, true)) === false) {
            throw new \InvalidArgumentException(\sprintf(self::MISSING_COLUMN_EX_MSG, $columnId));
        }

        return $column;
    }

    public function hasColumnById($columnId, bool $returnColumn = false): Column|ActionsColumn|bool
    {
        foreach ($this->columns as $column) {
            if ($column->getId() === $columnId) {
                return $returnColumn ? $column : true;
            }
        }

        return false;
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getPrimaryColumn(): Column
    {
        /** @var Column $column */
        foreach ($this->columns as $column) {
            if ($column->isPrimary()) {
                return $column;
            }
        }

        throw new \InvalidArgumentException('Primary column doesn\'t exists');
    }

    public function count(): int
    {
        return \count($this->columns);
    }

    public function addExtension(Column $extension): static
    {
        $this->extensions[\strtolower($extension->getType())] = $extension;

        return $this;
    }

    public function hasExtensionForColumnType(string $type): bool
    {
        return isset($this->extensions[$type]);
    }

    public function getExtensionForColumnType(string $type): mixed
    {
        // @todo: should not index be checked?
        return $this->extensions[$type];
    }

    public function getHash(): string
    {
        $hash = '';
        foreach ($this->columns as $column) {
            $hash .= $column->getId();
        }

        return $hash;
    }

    /**
     * Sets order of Columns passing an array of column ids
     * If the list of ids is uncomplete, the remaining columns will be
     * placed after if keepOtherColumns is true.
     */
    public function setColumnsOrder(array $columnIds, bool $keepOtherColumns = true): static
    {
        $reorderedColumns = [];
        $columnsIndexedByIds = [];

        foreach ($this->columns as $column) {
            $columnsIndexedByIds[$column->getId()] = $column;
        }

        foreach ($columnIds as $columnId) {
            if (isset($columnsIndexedByIds[$columnId])) {
                $reorderedColumns[] = $columnsIndexedByIds[$columnId];
                unset($columnsIndexedByIds[$columnId]);
            }
        }

        if ($keepOtherColumns) {
            $this->columns = \array_merge($reorderedColumns, \array_values($columnsIndexedByIds));
        } else {
            $this->columns = $reorderedColumns;
        }

        return $this;
    }
}

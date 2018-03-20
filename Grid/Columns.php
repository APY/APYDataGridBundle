<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Helper\ColumnsIterator;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class Columns implements \IteratorAggregate, \Countable
{
    protected $columns = [];
    protected $extensions = [];

    const MISSING_COLUMN_EX_MSG = 'Column with id "%s" doesn\'t exists';

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param bool $showOnlySourceColumns
     *
     * @return ColumnsIterator
     */
    public function getIterator($showOnlySourceColumns = false)
    {
        return new ColumnsIterator(new \ArrayIterator($this->columns), $showOnlySourceColumns);
    }

    /**
     * Add column.
     *
     * @param Column $column
     * @param int    $position
     *
     * @return Columns
     */
    public function addColumn(Column $column, $position = 0)
    {
        $column->setAuthorizationChecker($this->authorizationChecker);

        if ($position == 0) {
            $this->columns[] = $column;
        } else {
            if ($position > 0) {
                --$position;
            } else {
                $position = max(0, count($this->columns) + $position);
            }

            $head = array_slice($this->columns, 0, $position);
            $tail = array_slice($this->columns, $position);
            $this->columns = array_merge($head, [$column], $tail);
        }

        return $this;
    }

    /**
     * @param $columnId
     *
     * @throws \InvalidArgumentException
     *
     * @return Column
     */
    public function getColumnById($columnId)
    {
        if (($column = $this->hasColumnById($columnId, true)) === false) {
            throw new \InvalidArgumentException(sprintf(self::MISSING_COLUMN_EX_MSG, $columnId));
        }

        return $column;
    }

    /**
     * @param $columnId
     * @param bool $returnColumn
     *
     * @return bool|Column|ActionsColumn
     */
    public function hasColumnById($columnId, $returnColumn = false)
    {
        foreach ($this->columns as $column) {
            if ($column->getId() == $columnId) {
                return $returnColumn ? $column : true;
            }
        }

        return false;
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return Column
     */
    public function getPrimaryColumn()
    {
        foreach ($this->columns as $column) {
            if ($column->isPrimary()) {
                return $column;
            }
        }

        throw new \InvalidArgumentException('Primary column doesn\'t exists');
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->columns);
    }

    /**
     * @param $extension
     *
     * @return Columns
     */
    public function addExtension($extension)
    {
        $this->extensions[strtolower($extension->getType())] = $extension;

        return $this;
    }

    /**
     * @param $type
     *
     * @return bool
     */
    public function hasExtensionForColumnType($type)
    {
        return isset($this->extensions[$type]);
    }

    /**
     * @param $type
     *
     * @return mixed
     */
    public function getExtensionForColumnType($type)
    {
        // @todo: should not index be checked?
        return $this->extensions[$type];
    }

    /**
     * @return string
     */
    public function getHash()
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
     *
     * @param array $columnIds
     * @param bool  $keepOtherColumns
     *
     * @return Columns
     */
    public function setColumnsOrder(array $columnIds, $keepOtherColumns = true)
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
            $this->columns = array_merge($reorderedColumns, array_values($columnsIndexedByIds));
        } else {
            $this->columns = $reorderedColumns;
        }

        return $this;
    }
}

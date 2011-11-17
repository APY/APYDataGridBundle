<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid;

use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Helper\ColumnsIterator;
use Symfony\Component\Security\Core\SecurityContextInterface;

class Columns implements \IteratorAggregate, \Countable
{
    /**
     * @var \Sorien\DataGridBundle\Grid\Column\Column[]
     */
    private $columns;
    private $extensions;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->columns = array();
        $this->securityContext = $securityContext;
    }

    public function getIterator($showOnlySourceColumns = false)
    {
        return new ColumnsIterator(new \ArrayIterator($this->columns), $showOnlySourceColumns);
    }

    /**
     * Add column, column object have to extend Column
     * @param Column $column
     * @param int $position
     * @return Grid
     */
    public function addColumn($column, $position = 0)
    {
        if (!$column instanceof Column)
        {
            throw new \InvalidArgumentException('Your column needs to extend class Column.');
        }

        $column->setSecurityContext($this->securityContext);

        if ($position > 0)
        {
            $position--;
            $head = array_slice($this->columns, 0, $position);
            $tail = array_slice($this->columns, $position);
            $this->columns = array_merge($head, array($column), $tail);
        }
        else
        {
            $this->columns[] = $column;
        }

        return $this;
    }

    public function getColumnById($columnId)
    {
        $column = $this->hasColumnById($columnId, true);
        
        if ($column === false) {
            throw new \InvalidArgumentException(sprintf('Column with id "%s" doesn\'t exists', $columnId));
        }

        return $column;
    }
    
    public function hasColumnById($columnId, $returnColumn = false)
    {
        foreach ($this->columns as $column)
        {
            if ($column->getId() == $columnId)
            {
                return $returnColumn ? $column : true;
            }
        }

        return false;
    }

    public function getPrimaryColumn()
    {
        foreach ($this->columns as $column)
        {
            if ($column->isPrimary())
            {
                return $column;
            }
        }

        throw new \InvalidArgumentException('Primary column doesn\'t exists');
    }

    public function count()
    {
       return count($this->columns);
    }

    public function addExtension($extension)
    {
        $this->extensions[strtolower($extension->getType())] = $extension;
    }

    public function hasExtensionForColumnType($type)
    {
        return isset($this->extensions[$type]);
    }

    public function getExtensionForColumnType($type)
    {
        return $this->extensions[$type];
    }

    /**
     * Internal function
     * @return string
     */
    public function getHash()
    {
        $hash = '';
        foreach ($this->columns as $column)
        {
            $hash .= $column->getId();
        }
        return $hash;
    }
}
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

class Rows implements \IteratorAggregate, \Countable
{
    /** @var \SplObjectStorage */
    protected $rows;

    /**
     * @param array $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = new \SplObjectStorage();

        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    /**
     * (non-PHPdoc).
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator()
    {
        return $this->rows;
    }

    /**
     * Add row.
     *
     * @param Row $row
     *
     * @return Rows
     */
    public function addRow(Row $row)
    {
        $this->rows->attach($row);

        return $this;
    }

    /**
     * (non-PHPdoc).
     *
     * @see Countable::count()
     */
    public function count()
    {
        return $this->rows->count();
    }

    /**
     * Returns the iterator as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return iterator_to_array($this->getIterator(), true);
    }
}

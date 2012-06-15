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
    protected $rows;

    public function __construct(array $rows = array())
    {
        $this->rows = new \SplObjectStorage();

        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    public function getIterator()
    {
        return $this->rows;
    }

    /**
     * Add row
     *
     * @param Row $row
     * @return Rows
     */
    function addRow(Row $row)
    {
        $this->rows->attach($row);

        return $this;
    }

    public function count()
    {
       return $this->rows->count();
    }
}

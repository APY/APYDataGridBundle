<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle;

class Columns implements \IteratorAggregate, \Countable {

    /**
     * @var Column[]
     */
    private $columns;

    public function __construct()
    {
        $this->columns = new \SplObjectStorage();
    }

    public function getIterator()
    {
        return $this->columns;
    }

    /**
     * Add column, column object have to extend Column
     * @param $column Column
     * @return Grid
     *
     */
    function addColumn($column)
    {
        if (!$column instanceof Column)
        {
            throw new \InvalidArgumentException('Your column needs to extend class Column.');
        }

        $this->columns->attach($column);
        return $this;
    }

    /**
     * @todo
     * @return bool
     */
    function showFilters()
    {
        return true;
    }

    /**
     * @todo
     * @return bool
     */
    function showTitles()
    {
        return true;
    }

    public function count()
    {
       return $this->columns->count();
    }
}

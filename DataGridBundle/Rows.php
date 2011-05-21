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

class Rows implements \IteratorAggregate {

    /**
     * @var Rows[]
     */
    private $rows;
    private $countTotal;

    public function __construct()
    {
        $this->rows = new \SplObjectStorage();
    }

    public function getIterator()
    {
        return $this->rows;
    }

    /**
     * Add column, column object have to extend Column
     * @param $column Column
     * @return Grid
     *
     */
    function addRow($row)
    {
        if (!$row instanceof Row)
        {
            throw new \InvalidArgumentException('Your column needs to extend class Column.');
        }

        $this->rows->attach($row);
        return $this;
    }

    public function setCountTotal($countTotal)
    {
        $this->countTotal = $countTotal;
    }

    public function getCountTotal()
    {
        return $this->countTotal;
    }
}

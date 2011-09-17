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

class Rows implements \IteratorAggregate, \Countable
{
    /**
     * @var Rows[]
     */
    private $rows;

    public function __construct($array = array())
    {
        $this->rows = new \SplObjectStorage();

        foreach ($array as $rows)
        {
            $this->addRow(new Row($rows));
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
    function addRow($row)
    {
        if (!$row instanceof Row)
        {
            throw new \InvalidArgumentException('Your column needs to extend class Column.');
        }
        
        $this->rows->attach($row);

        return $this;
    }

    public function count()
    {
       return $this->rows->count();
    }
}

<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Source;

use Sorien\DataGridBundle\Column;

abstract class Source
{
    private $count;
    private $totalCount;
    private $page;

    /**
     * @abstract
     * @param $columns \Sorien\DataGridBundle\DataGrid\Columns
     * @param $actions
     * @return null
     */
    abstract public function prepare($columns, $actions);

    /**
     * @abstract
     * @param $columns \Sorien\DataGridBundle\Column\Column[]
     * @param $page int
     * @param $limit
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    abstract public function execute($columns, $page, $limit);

    public function initialize($container)
    {
    }

    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setPage($page)
    {
        $this->page = $page;
        return $this;
    }

    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param $columns \Sorien\DataGridBundle\Column\Columns
     * @return int
     */
    public function getTotalCount($columns)
    {
        return $this->totalCount;
    }
}
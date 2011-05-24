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

    public function __construct()
    {
    }

    /**
     * @abstract
     * @param $grid Columns
     * @return null
     */
    abstract public function prepare($columns, $actions);

    /**
     * @abstract
     * @param $columns Column[]
     * @param $page int
     * @return Row[] Traversable object or array @todo probably will be better to create Rows Class
     */
    abstract public function execute($columns, $page);

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

    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
        return $this;
    }

    public function getTotalCount()
    {
        return $this->totalCount;
    }
}
<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\DataGrid;

class Actions implements \IteratorAggregate, \Countable {

    private $actions;

    public function __construct()
    {
        $this->actions = array();
    }

    public function getIterator()
    {
        return $this->actions;
    }

    function addMassAction($title, $callback)
    {
        $this->actions[] = array('title' => $title, 'callback' => $callback);
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
       return count($this->actions);
    }
}

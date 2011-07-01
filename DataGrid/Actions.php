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
        return new \ArrayIterator($this->actions);
    }

    function addAction($title, $callback, $confirm = true)
    {
        $this->actions[] = array('title' => $title, 'callback' => $callback, 'confirm' => $confirm);
        return $this;
    }

    function getAction($id)
    {
        return $this->actions[$id];
    }

    public function count()
    {
       return count($this->actions);
    }
}

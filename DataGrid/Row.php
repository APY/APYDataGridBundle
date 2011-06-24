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

class Row
{
    private $fields;
    private $color;
    private $legend;

    public function __construct($array = array())
    {
        $this->fields = $array;
        $this->color = '';
    }

    public function setField($rowId, $value)
    {
        $this->fields[$rowId] = $value;
    }

    public function getField($rowId)
    {
        return isset($this->fields[$rowId]) ? $this->fields[$rowId] : '';
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setLegend($legend)
    {
        $this->legend = $legend;
    }

    public function getLegend()
    {
        return $this->legend;
    }


}

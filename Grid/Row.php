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

class Row
{
    private $fields;
    private $color;
    private $legend;
    private $primaryField;

    public function __construct()
    {
        $this->fields = array();
        $this->color = '';
    }

    public function setField($rowId, $value)
    {
        $this->fields[$rowId] = $value;

        return $this;
    }

    public function getField($rowId)
    {
        return isset($this->fields[$rowId]) ? $this->fields[$rowId] : '';
    }

    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setLegend($legend)
    {
        $this->legend = $legend;

        return $this;
    }

    public function getLegend()
    {
        return $this->legend;
    }

    public function setPrimaryField($primaryField)
    {
        $this->primaryField = $primaryField;

        return $this;
    }

    public function getPrimaryField()
    {
        return $this->primaryField;
    }

    public function getPrimaryFieldValue()
    {
        return $this->fields[$this->primaryField];
    }
}

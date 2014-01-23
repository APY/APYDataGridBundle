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

class Row
{
    protected $fields;
    protected $class;
    protected $color;
    protected $legend;
    protected $primaryField;
    protected $entity;
    protected $repository;

    public function __construct()
    {
        $this->fields = array();
        $this->color = '';
    }

    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    public function getEntity()
    {
        $primaryKeyValue = current($this->getPrimaryKeyValue());

        return $this->repository->find($primaryKeyValue);
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

    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    public function getClass()
    {
        return $this->class;
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
        if (null === $this->primaryField) {
            throw new \InvalidArgumentException('Primary column must be defined');
        }

        if (is_array($this->primaryField)) {
            return array_intersect_key($this->fields, array_flip($this->primaryField));
        }

        return $this->fields[$this->primaryField];
    }

    public function getPrimaryKeyValue()
    {
        $primaryField = $this->getPrimaryFieldValue();

        if (is_array($primaryField)) {
            return $primaryField;
        }

        return array('id' => $primaryField);
    }
}

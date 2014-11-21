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

class Filter
{
    protected $value;
    protected $operator;
    protected $columnName;

    public function __construct($operator, $value = null, $columnName = null)
    {
        $this->value = $value;
        $this->operator = $operator;
        $this->columnName = $columnName;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function hasColumnName()
    {
        return $this->columnName !== null;
    }

    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }
}

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
    protected $id;

    public function __construct($operator, $value = null, $id = null)
    {
        $this->value = $value;
        $this->operator = $operator;
        $this->id = $id;
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

    public function hasId()
    {
        return $this->id !== null;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }
}

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

class Filter {

    private $value;
    private $operator;

    public function __construct($operator, $value)
    {
        $this->value = $value;
        $this->operator = $operator;
    }

    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return $this->value;
    }
}

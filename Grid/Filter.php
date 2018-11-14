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

    /**
     * @param string      $operator
     * @param mixed|null  $value
     * @param string|null $columnName
     */
    public function __construct($operator, $value = null, $columnName = null)
    {
        $this->value = $value;
        $this->operator = $operator;
        $this->columnName = $columnName;
    }

    /**
     * @param string $operator
     *
     * @return Filter
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed $value
     *
     * @return Filter
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function hasColumnName()
    {
        return $this->columnName !== null;
    }

    /**
     * @param string $columnName
     *
     * @return Filter
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getColumnName()
    {
        return $this->columnName;
    }
}

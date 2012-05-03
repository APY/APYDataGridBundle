<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Source;

use Sorien\DataGridBundle\Grid\Column;
use Sorien\DataGridBundle\Grid\Mapping\Driver\DriverInterface;

abstract class Source implements DriverInterface
{
    const EVENT_PREPARE = 0;
    const EVENT_PREPARE_QUERY = 1;
    const EVENT_PREPARE_ROW = 2;

    private $callbacks;

    /**
     * @param \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder
     */
    public function prepareQuery($queryBuilder)
    {
        if (isset($this->callbacks[$this::EVENT_PREPARE_QUERY]) && is_callable($this->callbacks[$this::EVENT_PREPARE_QUERY]))
        {
            call_user_func($this->callbacks[$this::EVENT_PREPARE_QUERY], $queryBuilder);
        }
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Row $row
     * @return \Sorien\DataGridBundle\Grid\Row|null
     */
    public function prepareRow($row)
    {
        if (isset($this->callbacks[$this::EVENT_PREPARE_ROW]) && is_callable($this->callbacks[$this::EVENT_PREPARE_ROW]))
        {
            return call_user_func($this->callbacks[$this::EVENT_PREPARE_ROW], $row);
        }

        return $row;
    }

    /**
     * @param int $type Source::EVENT_PREPARE*
     * @param \Closure $callback
     */
    public function setCallback($type, $callback)
    {
        $this->callbacks[$type] = $callback;

        return $this;
    }

    /**
     * Find data for current page
     *
     * @abstract
     * @param \Sorien\DataGridBundle\Grid\Column\Column[] $columns
     * @param int $page
     * @param int $limit
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    abstract public function execute($columns, $page = 0, $limit = 0);

    /**
     * Get Total count of data items
     *
     * @param \Sorien\DataGridBundle\Grid\Column\Columns $columns
     * @return int
     */
    abstract function getTotalCount($columns);

    /**
     * Set container
     *
     * @abstract
     * @param  $container
     * @return void
     */
    abstract public function initialise($container);

    /**
     * @abstract
     * @param $columns
     */
    abstract public function getColumns($columns);

    /**
     * @param $class
     * @return array
     */
    public function getClassColumns($class)
    {
        return array();
    }

    public function getFieldsMetadata($class)
    {
        return array();
    }

    public function getGroupBy($class)
    {
        return array();
    }

    /**
    * Return source hash string
    * @abstract
    */
    abstract function getHash();

    /**
     * Delete one or more objects
     *
     * @abstract
     * @param array $ids
     * @return void
     */
    abstract public function delete(array $ids);
}

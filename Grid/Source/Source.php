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
    private $prepareCallback;
    /**
     * Prepare all Columns and Actions
     *
     * @abstract
     * @param $columns \Sorien\DataGridBundle\Grid\Columns
     * @param $actions \Sorien\DataGridBundle\Grid\Actions
     * @return null
     */
    public function prepare($columns, $actions)
    {
    }

    final public function _prepare($columns, $actions)
    {
        if (is_callable($this->prepareCallback))
        {
            $this->prepare($columns, $actions);

            call_user_func($this->prepareCallback, $columns, $actions);
        }
        else
        {
            $this->prepare($columns, $actions);
        }
    }

    public function setPrepareCallback($callback)
    {
        $this->prepareCallback = $callback;
    }

    /**
     * Find data for current page
     *
     * @abstract
     * @param $columns \Sorien\DataGridBundle\Grid\Column\Column[]
     * @param $page int
     * @param $limit
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    abstract public function execute($columns, $page, $limit);

    /**
     * Get Total count of data items
     *
     * @param $columns \Sorien\DataGridBundle\Grid\Column\Columns
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
}
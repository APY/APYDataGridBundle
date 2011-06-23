<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Source;

use Sorien\DataGridBundle\Column;

abstract class Source
{
    /**
     * Prepare source when service is needed
     *
     * @param $container
     */
    public function initialize($container)
    {
    }

    /**
     * Prepare all Columns and Actions
     *
     * @abstract
     * @param $columns \Sorien\DataGridBundle\DataGrid\Columns
     * @param $actions \Sorien\DataGridBundle\DataGrid\Actions
     * @return null
     */
    abstract public function prepare($columns, $actions);

    /**
     * Find data for current page
     *
     * @abstract
     * @param $columns \Sorien\DataGridBundle\Column\Column[]
     * @param $page int
     * @param $limit
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    abstract public function execute($columns, $page, $limit);

    /**
     * Get Total count of data items
     *
     * @param $columns \Sorien\DataGridBundle\Column\Columns
     * @return int
     */
    abstract public function getTotalCount($columns);
}
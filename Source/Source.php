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

interface Source
{
    /**
     * Prepare all Columns and Actions
     *
     * @abstract
     * @param $columns \Sorien\DataGridBundle\DataGrid\Columns
     * @param $actions \Sorien\DataGridBundle\DataGrid\Actions
     * @return null
     */
    public function prepare($columns, $actions);

    /**
     * Find data for current page
     *
     * @abstract
     * @param $columns \Sorien\DataGridBundle\Column\Column[]
     * @param $page int
     * @param $limit
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    public function execute($columns, $page, $limit);

    /**
     * Get Total count of data items
     *
     * @param $columns \Sorien\DataGridBundle\Column\Columns
     * @return int
     */
    public function getTotalCount($columns);
}
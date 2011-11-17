<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Action;

interface RowActionInterface
{
    /**
     * get action title
     *
     * @return string
     */
    public function getTitle();

    /**
     * get action route
     *
     * @return string
     */
    public function getRoute();

    /**
     * get action confirm
     *
     * @return boolean
     */
    public function getConfirm();

    /**
     * get action confirmMessage
     *
     * @return boolean
     */
    public function getConfirmMessage();    

    /**
     * get action target
     *
     * @return boolean
     */
    public function getTarget();

    /**
     * get action column
     *
     * @return boolean
     */
    public function getColumn();
}

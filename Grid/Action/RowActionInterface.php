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

namespace APY\DataGridBundle\Grid\Action;

// @todo: implementation seems to be more specific than interface. It obviously be the case but I've noticed that
// only one method of this interface is used in our code. So I wonder if this interface is "updated" and is the mimimum
// API methods that should be provided as a contract or not.
interface RowActionInterface
{
    /**
     * get action title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * get action route.
     *
     * @return string
     */
    public function getRoute();

    /**
     * get action confirm.
     *
     * @return bool
     */
    public function getConfirm();

    /**
     * get action confirmMessage.
     *
     * @return string
     */
    public function getConfirmMessage();

    /**
     * get action target.
     *
     * @return string
     */
    public function getTarget();

    /**
     * get the action column id.
     *
     * @return string
     */
    public function getColumn();

    /**
     * get route parameters.
     *
     * @return array
     */
    public function getRouteParameters();

    /**
     * get attributes of the link.
     *
     * @return array
     */
    public function getAttributes();

    /**
     * get action enabled.
     *
     * @return bool
     */
    public function getEnabled();
}

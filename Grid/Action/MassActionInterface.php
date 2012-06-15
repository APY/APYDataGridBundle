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

interface MassActionInterface
{
    /**
     * get action title
     *
     * @return string
     */
    public function getTitle();

    /**
     * get action callback
     *
     * @return string
     */
    public function getCallback();

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
     * get additional parameters
     *
     * @return array
     */
    public function getParameters();
}

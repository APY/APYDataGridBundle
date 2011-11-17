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
}

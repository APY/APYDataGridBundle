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

class DeleteMassAction extends MassAction
{
    /**
     * Default DeleteMassAction constructor
     *
     * @param boolean $confirm Show confirm message if true
     */
    public function __construct($confirm = false)
    {
        parent::__construct('Delete', 'static::deleteAction', $confirm);
    }
}

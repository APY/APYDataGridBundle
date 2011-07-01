<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Column;

class Action extends Column
{
    private $gridHash;

    public function __construct($gridHash)
    {
        $this->gridHash = $gridHash;
        parent::__construct('__action', '', 10, false, true);
    }

    public function renderFilter($gridHash)
    {
        return '<input type="checkbox"/>';
    }

    public function renderCell($value, $row, $router, $primaryColumnValue)
    {
        return '<input type="checkbox" class="action" value="1" name="'.$this->gridHash.'[__action]['.$primaryColumnValue.']"/>';
    }

    public function isVisibleForSource()
    {
        return false;
    }
}

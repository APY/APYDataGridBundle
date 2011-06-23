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

class MassAction extends Column
{
    private $massActionColumnId;

    public function __construct()
    {
        parent::__construct('mass', '', 10, false, true);
    }

    public function renderFilter($gridId)
    {
        return '<input type="checkbox"/>';
    }

    public function renderCell($value, $row, $router, $primaryColumnValue)
    {
        return '<input type="checkbox" style="width:100%" value="'.$this->data.'" name="[mass]['.$primaryColumnValue.']"/>';
    }

    public function setMassActionColumnId($massActionColumnId)
    {
        $this->massActionColumnId = $massActionColumnId;
    }

    public function getMassActionColumnId()
    {
        return $this->massActionColumnId;
    }

    public function isSpecial()
    {
        return true;
    }

}

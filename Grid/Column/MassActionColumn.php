<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Column;

class MassActionColumn extends Column
{
    private $gridHash;

    public function __construct($gridHash)
    {
        $this->gridHash = $gridHash;
        parent::__construct(array('id' => '__action', 'title' => '', 'size' => 15, 'sortable' => false, 'source' => false));
    }

    public function renderFilter($gridHash)
    {
        return '<input type="checkbox"/>';
    }

    public function renderCell($value, $row, $router)
    {
        return '<input type="checkbox" class="action" value="1" name="'.$this->gridHash.'[__action]['.$row->getPrimaryFieldValue().']"/>';
    }
}

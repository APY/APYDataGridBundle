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
    const ID = '__action';

    private $gridHash;

    public function __construct($gridHash)
    {
        $this->gridHash = $gridHash;
        parent::__construct(array('id' => self::ID, 'title' => '', 'size' => 15, 'sortable' => false, 'source' => false, 'align' => 'center'));
    }

    public function renderFilter($gridHash)
    {
        return '<input type="checkbox" class="grid-mass-selector" onclick="'.$gridHash.'_mark_visible(this.checked); return true;"/>';
    }

    public function renderCell($value, $row, $router)
    {
        return '<input type="checkbox" class="action" value="1" name="'.$this->gridHash.'['.self::ID.']['.$row->getPrimaryFieldValue().']"/>';
    }
	    
    public function getType()
    {
        return 'massaction';
    }
}

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

use Sorien\DataGridBundle\DataGrid\Filter;

class Text extends Column
{
    public function renderFilter($gridId)
    {
        return '<input type="text" style="width:100%" value="'.$this->data.'" name="'.$gridId.'['.$this->getId().']" onKeyPress="if (event.which == 13){this.form.submit();}"/>';
    }

    public function getDataFilters()
    {
        return array(new Filter(self::OPERATOR_LIKE, '\'%'.$this->data.'%\''));
    }
}

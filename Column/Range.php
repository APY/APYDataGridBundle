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

class Range extends Column
{
    private $values;

    public function renderFilter($gridId)
    {
        $result = '<div class="range-column-filter">';
        $result .= '<input class="first-filter" placeholder="From:" type="text" style="width:100%" value="'.$this->data['from'].'" name="'.$gridId.'['.$this->getId().'][from]" onkeypress="if (event.which == 13){this.form.submit();}"/><br/>';
        $result .= '<input class="second-filter" placeholder="To:" type="text" style="width:100%" value="'.$this->data['to'].'" name="'.$gridId.'['.$this->getId().'][to]" onkeypress="if (event.which == 13){this.form.submit();}"/><br/>';
        $result .= '</div>';
        return $result;
    }

    public function getDataFilters()
    {
        $result = array();

        if (isset($this->data['from']) && $this->data['from'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_GTE, $this->data['from']);
        }

        if (isset($this->data['to']) && $this->data['to'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_LTE, $this->data['to']);
        }

        return $result;
    }

}

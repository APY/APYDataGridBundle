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
        $data = $this->getFilterData();
        
        $result = '<div class="range-column-filter">';
        $result .= '<input class="first-filter" placeholder="From:" type="text" style="width:100%" value="'.$data['from'].'" name="'.$gridId.'['.$this->getId().'][filter][from]" onKeyPress="if (event.which == 13){this.form.submit();}"/><br/>';
        $result .= '<input class="second-filter" placeholder="To:" type="text" style="width:100%" value="'.$data['to'].'" name="'.$gridId.'['.$this->getId().'][filter][to]" onKeyPress="if (event.which == 13){this.form.submit();}"/><br/>';
        $result .= '</div>';
        return $result;
    }

    public function getDataFilters()
    {
        $result = array();
        $data = $this->getFilterData();

        if (isset($data['from']) && $data['from'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_GTE, $data['from']);
        }

        if (isset($data['to']) && $data['to'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_LTE, $data['to']);
        }

        return $result;
    }

}

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

use Sorien\DataGridBundle\Grid\Filter;

class Range extends Column
{
    public function __types()
    {
        return array('integer', 'smallint', 'bigint', 'integer', 'float', 'range');
    }

    public function renderFilter($gridHash)
    {
        $result = '<div class="range-column-filter">';
        $result .= '<input class="first-filter" placeholder="From:" type="text" style="width:100%" value="'.$this->data['from'].'" name="'.$gridHash.'['.$this->getId().'][from]" onkeypress="if (event.which == 13){this.form.submit();}"/><br/>';
        $result .= '<input class="second-filter" placeholder="To:" type="text" style="width:100%" value="'.$this->data['to'].'" name="'.$gridHash.'['.$this->getId().'][to]" onkeypress="if (event.which == 13){this.form.submit();}"/><br/>';
        $result .= '</div>';
        return $result;
    }

    public function getFilters()
    {
        $result = array();

        if ($this->data['from'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_GTE, $this->data['from']);
        }

        if ($this->data['to'] != '')
        {
           $result[] =  new Filter(self::OPERATOR_LTE, $this->data['to']);
        }

        return $result;
    }

    public function setData($data)
    {
        $this->data = array('from' => '', 'to' => '');

        if (is_array($data))
        {
            if (isset($data['from']) && is_string($data['from']))
            {
                $this->data['from'] = $data['from'];
            }

            if (isset($data['to']) && is_string($data['to']))
            {
               $this->data['to'] = $data['to'];
            }
        }

        return $this;
    }

    public function getData()
    {
        $result = array();

        if ($this->data['from'] != '')
        {
           $result['from'] =  $this->data['from'];
        }

        if ($this->data['to'] != '')
        {
           $result['to'] =  $this->data['to'];
        }

        return $result;
    }


    public function isFiltered()
    {
        return $this->data['from'] != '' || $this->data['to'] != '';
    }

}

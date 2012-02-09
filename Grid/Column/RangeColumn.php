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

class RangeColumn extends Column
{
    private $inputType;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->setInputType($this->getParam('inputType', 'text'));
    }

    public function getInputType()
    {
        return $this->inputType;
    }

    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
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

    public function getType()
    {
        return 'range';
    }
}

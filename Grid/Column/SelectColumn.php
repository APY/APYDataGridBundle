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

class SelectColumn extends Column
{
    const BLANK = '_default';

    protected $values;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->values = $this->getParam('values', array());
    }

    public function setData($data)
    {
        if ((is_string($data) || is_integer($data)) && $data != $this::BLANK && key_exists($data, $this->values))
        {
            $this->data = $data;
        }

        return $this;
    }

    public function getFilters()
    {
        return array(new Filter(self::OPERATOR_EQ, $this->data));
    }

    public function getValues()
    {
        return $this->values;
    }

    public function renderCell($value, $row, $router)
    {
        if (key_exists((string)$value, $this->values))
        {
            $value = $this->values[$value];
        }
        return parent::renderCell($value, $row, $router);
    }

    public function getType()
    {
        return 'select';
    }
}

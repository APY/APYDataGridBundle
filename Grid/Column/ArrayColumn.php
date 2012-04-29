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

class ArrayColumn extends Column
{
    protected $separator;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->separator = $this->getParam('separator', "<br />");
    }

    public function getFilters()
    {
        return array(new Filter(self::OPERATOR_REGEXP, '/.*"'.$this->data.'".*/i'));
    }

    public function setData($data)
    {
        if (is_string($data))
        {
            $this->data = $data;
        }

        return $this;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function getType()
    {
        return 'array';
    }
}

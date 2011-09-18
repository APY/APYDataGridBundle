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

class Date extends Text
{
    private $format;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->format = $this->getParam('format', 'Y-m-d H:i:s');
    }

    public function __types()
    {
        return array('datetime', 'date', 'time');
    }

    public function renderFilter($gridHash)
    {
        return '';
    }

    public function renderCell($value, $row, $router)
    {
        if ($value != '')
        {
            $date = new \DateTime($value);
            return parent::renderCell($date->format($this->format), $row, $router);
        }
        else
        {
            return '';
        }

    }
}

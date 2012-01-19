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

class DateColumn extends TextColumn
{
    private $format;

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        $this->format = $this->getParam('format', 'Y-m-d H:i:s');
    }

    public function renderFilter($gridHash)
    {
        return '';
    }

    public function renderCell($value, $row, $router)
    {
        if ($value != null)
        {
            if (is_string($value))
            {
                $value = new \DateTime($value);
            }

            if ($value instanceof \DateTime)
            {
                return parent::renderCell($value->format($this->format), $row, $router);
            }

            throw \InvalidArgumentException('Date Column value have to be DataTime object');
        }
        else
        {
            return '';
        }
    }
    
    public function getType()
    {
        return 'date';
    }
}

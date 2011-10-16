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

class BlankColumn extends Column
{
    public function __initialize(array $params)
    {
        parent::__initialize(array_merge(array('sortable' => false, 'filterable' => false, 'source' => false), $params));
    }

    public function getType()
    {
        return 'blank';
    }
}

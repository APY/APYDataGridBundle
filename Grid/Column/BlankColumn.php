<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Column;

class BlankColumn extends Column
{
    public function __initialize(array $params)
    {
        $params['filterable'] = false;
        $params['sortable'] = false;
        $params['source'] = false;

        parent::__initialize($params);
    }

    public function getType()
    {
        return 'blank';
    }
}

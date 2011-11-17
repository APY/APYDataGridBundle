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

class BooleanColumn extends SelectColumn
{
    public function __initialize(array $params)
    {
        $params['values'] = array('1'=>'true','0'=>'false');
        parent::__initialize($params);
    }

    public function getType()
    {
        return 'boolean';
    }
}

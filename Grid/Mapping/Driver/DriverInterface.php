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

namespace APY\DataGridBundle\Grid\Mapping\Driver;

interface DriverInterface
{
    public function getClassColumns($class, $group = 'default');

    public function getFieldsMetadata($class, $group = 'default');

    public function getGroupBy($class, $group = 'default');
}

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

class TimeColumn extends DateTimeColumn
{
    protected $dateFormat = \IntlDateFormatter::NONE;

    protected $fallbackFormat = 'H:i:s';

    public function getType()
    {
        return 'time';
    }
}

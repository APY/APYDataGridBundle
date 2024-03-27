<?php

namespace APY\DataGridBundle\Grid\Column;

class TimeColumn extends DateTimeColumn
{
    protected int $dateFormat = \IntlDateFormatter::NONE;

    protected string $fallbackFormat = 'H:i:s';

    public function getType(): string
    {
        return 'time';
    }
}

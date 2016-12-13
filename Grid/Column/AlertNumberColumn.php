<?php

namespace APY\DataGridBundle\Grid\Column;

class AlertNumberColumn extends NumberColumn
{
    public function getType()
    {
        return 'alert_number';
    }
}

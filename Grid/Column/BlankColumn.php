<?php

namespace APY\DataGridBundle\Grid\Column;

class BlankColumn extends Column
{
    public function __initialize(array $params): void
    {
        $params['filterable'] = false;
        $params['sortable'] = false;
        $params['source'] = false;

        parent::__initialize($params);
    }

    public function getType(): string
    {
        return 'blank';
    }
}

<?php

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\GridInterface;

class JSONExport extends Export
{
    protected ?string $fileExtension = 'json';

    public function computeData(GridInterface $grid): void
    {
        $this->content = \json_encode($this->getGridData($grid));
    }
}

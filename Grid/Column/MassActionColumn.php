<?php

namespace APY\DataGridBundle\Grid\Column;

class MassActionColumn extends Column
{
    public const ID = '__action';

    public function __construct()
    {
        parent::__construct([
            'id' => self::ID,
            'title' => '',
            'size' => 15,
            'filterable' => true,
            'sortable' => false,
            'source' => false,
            'align' => Column::ALIGN_CENTER,
        ]);
    }

    public function isVisible(bool $isExported = false): bool
    {
        if ($isExported) {
            return false;
        }

        return parent::isVisible();
    }

    public function getFilterType(): string
    {
        return $this->getType();
    }

    public function getType(): string
    {
        return 'massaction';
    }
}

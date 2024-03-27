<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Row;
use Symfony\Component\Routing\RouterInterface;

class RankColumn extends BlankColumn
{
    protected int $rank = 1;

    public function __initialize(array $params): void
    {
        parent::__initialize($params);

        $this->setId($this->getParam('id', 'rank'));
        $this->setTitle($this->getParam('title', 'rank'));
        $this->setSize($this->getParam('size', '30'));
        $this->setAlign($this->getParam('align', 'center'));
    }

    public function renderCell(mixed $value, Row $row, RouterInterface $router): int
    {
        return $this->rank++;
    }

    public function getType(): string
    {
        return 'rank';
    }
}

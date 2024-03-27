<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Source\Source;

interface GridFactoryInterface
{
    public function create(GridTypeInterface|string $type = null, Source $source = null, array $options = []): Grid;

    public function createBuilder(GridTypeInterface|string $type = null, Source $source = null, array $options = []): GridBuilder;

    public function createColumn(string $name, string $type, array $options = []): Column;
}

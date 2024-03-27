<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;

interface GridRegistryInterface
{
    public function getType(string $name): GridTypeInterface;

    public function hasType(string $name): bool;

    public function getColumn(string $type): Column;

    public function hasColumn(string $type): bool;
}

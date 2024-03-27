<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;

interface GridBuilderInterface
{
    public function add(string $name, Column|string $type, array $options = []): self;

    public function get(string $name): Column;

    public function remove(string $name): self;

    public function has(string $name): bool;

    public function getGrid(): Grid;
}

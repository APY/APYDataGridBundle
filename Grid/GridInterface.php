<?php

namespace APY\DataGridBundle\Grid;

use Symfony\Component\HttpFoundation\Request;

interface GridInterface
{
    public function initialize(): static;

    public function handleRequest(Request $request): static;
}

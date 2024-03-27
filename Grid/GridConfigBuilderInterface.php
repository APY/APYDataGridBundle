<?php

namespace APY\DataGridBundle\Grid;

interface GridConfigBuilderInterface extends GridConfigInterface
{
    /**
     * Builds and returns the grid configuration.
     */
    public function getGridConfig(): GridConfigInterface;
}

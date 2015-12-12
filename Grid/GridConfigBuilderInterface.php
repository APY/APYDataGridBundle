<?php
namespace APY\DataGridBundle\Grid;

/**
 * Interface GridConfigBuilderInterface
 *
 * @package APY\DataGridBundle\Grid
 * @author  Quentin Ferrer
 */
interface GridConfigBuilderInterface extends GridConfigInterface
{
    /**
     * Builds and returns the grid configuration.
     *
     * @return GridConfigInterface
     */
    public function getGridConfig();
}

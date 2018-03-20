<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;

/**
 * Interface GridBuilderInterface.
 *
 * @author  Quentin Ferrer
 */
interface GridBuilderInterface
{
    /**
     * Adds a column.
     *
     * @param string        $name
     * @param string|Column $type
     * @param array         $options
     *
     * @return GridBuilderInterface
     */
    public function add($name, $type, array $options = []);

    /**
     * Returns a column.
     *
     * @param string $name The name of column
     *
     * @return Column
     */
    public function get($name);

    /**
     * Removes the column with the given name.
     *
     * @param string $name The name of column
     *
     * @return GridBuilderInterface
     */
    public function remove($name);

    /**
     * Returns whether a column with the given name exists.
     *
     * @param string $name The name of column
     *
     * @return bool
     */
    public function has($name);

    /**
     * Creates the grid.
     *
     * @return Grid The grid
     */
    public function getGrid();
}

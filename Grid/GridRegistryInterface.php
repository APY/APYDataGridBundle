<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;

/**
 * The central registry of the Grid component.
 *
 * @author  Quentin Ferrer
 */
interface GridRegistryInterface
{
    /**
     * Returns a grid type by name.
     *
     * @param string $name The name of type
     *
     * @return GridTypeInterface The type
     */
    public function getType($name);

    /**
     * Returns whether the given grid type is supported.
     *
     * @param string $name The name of type
     *
     * @return bool Whether the type is supported.
     */
    public function hasType($name);

    /**
     * Returns a column by type.
     *
     * @param string $type The type of column
     *
     * @return Column The column
     */
    public function getColumn($type);

    /**
     * Returns whether the given column type is supported.
     *
     * @param string $type The type of column
     *
     * @return bool
     */
    public function hasColumn($type);
}

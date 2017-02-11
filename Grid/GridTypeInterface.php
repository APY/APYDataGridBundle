<?php

namespace APY\DataGridBundle\Grid;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface GridTypeInterface.
 *
 * @author  Quentin Ferrer
 */
interface GridTypeInterface
{
    /**
     * Builds the grid.
     *
     * @param GridBuilder $builder The grid builder
     * @param array       $options The options
     */
    public function buildGrid(GridBuilder $builder, array $options = []);

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type.
     */
    public function getName();
}

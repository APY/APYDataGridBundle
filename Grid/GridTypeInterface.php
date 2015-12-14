<?php
namespace APY\DataGridBundle\Grid;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface GridTypeInterface
 *
 * @package APY\DataGridBundle
 * @author  Quentin Ferrer
 */
interface GridTypeInterface
{
    /**
     * Builds the grid.
     *
     * @param GridBuilder $builder The grid builder
     * @param array       $options The options
     *
     * @return void
     */
    public function buildGrid(GridBuilder $builder, array $options = array());

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type.
     */
    public function getName();
}

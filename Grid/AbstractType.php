<?php

namespace APY\DataGridBundle\Grid;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractType.
 *
 * @author  Quentin Ferrer
 */
abstract class AbstractType implements GridTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildGrid(GridBuilder $builder, array $options = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    abstract public function getName();
}

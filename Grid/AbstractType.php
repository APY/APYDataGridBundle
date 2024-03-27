<?php

namespace APY\DataGridBundle\Grid;

use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractType implements GridTypeInterface
{
    public function buildGrid(GridBuilder $builder, array $options = []): void
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
    }

    abstract public function getName(): string;
}

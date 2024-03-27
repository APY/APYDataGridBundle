<?php

namespace APY\DataGridBundle\Grid;

use Symfony\Component\OptionsResolver\OptionsResolver;

interface GridTypeInterface
{
    public function buildGrid(GridBuilder $builder, array $options = []);

    public function configureOptions(OptionsResolver $resolver);

    public function getName(): string;
}

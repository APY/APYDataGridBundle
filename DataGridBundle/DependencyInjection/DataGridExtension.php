<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DataGridExtension extends Extension {

    public function load(array $config, ContainerBuilder $container)
    {
        $definition = new Definition('Sorien\DatagridBundle\Extension\DataGrid');
        $definition->addTag('twig.extension');
        $container->setDefinition('datagrid_twig_extension', $definition);
    }
}
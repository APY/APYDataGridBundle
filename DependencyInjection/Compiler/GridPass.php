<?php

namespace APY\DataGridBundle\DependencyInjection\Compiler;

use APY\DataGridBundle\Grid\GridRegistryInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class GridPass.
 *
 * @author  Quentin Ferrer
 */
class GridPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(GridRegistryInterface::class)) {
            return;
        }

        $definition = $container->getDefinition(GridRegistryInterface::class);

        $types = $container->findTaggedServiceIds('apy_grid.type');
        foreach ($types as $id => $tag) {
            $definition->addMethodCall('addType', [new Reference($id)]);
        }

        $columns = $container->findTaggedServiceIds('apy_grid.column');
        foreach ($columns as $id => $tag) {
            $definition->addMethodCall('addColumn', [new Reference($id)]);
        }
    }
}

<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class GridExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('grid')) {
            return;
        }

        $definition = $container->getDefinition('grid');

        // Extensions must always be registered before everything else.
        // For instance, global variable definitions must be registered
        // afterward. If not, the globals from the extensions will never
        // be registered.
        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls(array());

        foreach ($container->findTaggedServiceIds('grid.column.extension') as $id => $attributes) {
            $definition->addMethodCall('addColumnExtension', array(new Reference($id)));
        }

        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}

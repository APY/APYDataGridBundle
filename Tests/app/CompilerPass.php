<?php
namespace APY\DataGridBundle\Tests\app;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class CompilerPass implements CompilerPassInterface
{

    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('grid.twig_extension')->setPublic(true);
    }
}
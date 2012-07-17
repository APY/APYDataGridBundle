<?php

namespace APY\DataGridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('apy_data_grid');

        $rootNode
            ->children()
                ->arrayNode('limits')
                    ->beforeNormalization()
                        ->ifTrue(function($v) { return !is_array($v); })
                        ->then(function($v) { return array($v); })
                    ->end()
                    ->defaultValue(array(20 => '20', 50 => '50', 100 => '100'))
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('persistence')->defaultFalse()->end()
                ->scalarNode('no_data_message')->defaultValue('No data')->end()
                ->scalarNode('no_result_message')->defaultValue('No result')->end()
            ->end()
        ;
        return $treeBuilder;
    }
}

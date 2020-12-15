<?php

namespace APY\DataGridBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('apy_data_grid');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('limits')
                    ->performNoDeepMerging()
                    ->beforeNormalization()
                        ->ifTrue(function ($v) { return !is_array($v); })
                        ->then(function ($v) { return [$v]; })
                    ->end()
                    ->defaultValue([20 => '20', 50 => '50', 100 => '100'])
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('persistence')->defaultFalse()->end()
                ->scalarNode('theme')->defaultValue('APYDataGridBundle::blocks.html.twig')->end()
                ->scalarNode('no_data_message')->defaultValue('No data')->end()
                ->scalarNode('no_result_message')->defaultValue('No result')->end()
                ->scalarNode('actions_columns_size')->defaultValue(-1)->end()
                ->scalarNode('actions_columns_title')->defaultValue('Actions')->end()
                ->scalarNode('actions_columns_separator')->defaultValue('<br />')->end() // deprecated
                ->arrayNode('pagerfanta')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable')->defaultFalse()->end()
                        ->scalarNode('view_class')->defaultValue('Pagerfanta\View\DefaultView')->end()
                        ->arrayNode('options')
                            ->defaultValue(['prev_message' => '«', 'next_message' => '»'])
                            ->useAttributeAsKey('options')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
             ->end();

        return $treeBuilder;
    }
}

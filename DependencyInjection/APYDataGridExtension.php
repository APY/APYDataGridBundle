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

namespace APY\DataGridBundle\DependencyInjection;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\GridTypeInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class APYDataGridExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');
        $loader->load('grid.xml');
        $loader->load('columns.xml');
        $loader->load('legacy_aliases.xml');

        /**
         * Equivalent of _instanceof in yaml
         * Automatically add tag to classes inheriting from APY\DataGridBundle\Grid\Column\Column
         * or APY\DataGridBundle\Grid\GridTypeInterface
         * Only available for Symfony DI 3.3+
         */
        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration(Column::class)->addTag('apy_grid.column');
            $container->registerForAutoconfiguration(GridTypeInterface::class)->addTag('apy_grid.type');
        }

        $container->setParameter('apy_data_grid.limits', $config['limits']);
        $container->setParameter('apy_data_grid.theme', $config['theme']);
        $container->setParameter('apy_data_grid.persistence', $config['persistence']);
        $container->setParameter('apy_data_grid.no_data_message', $config['no_data_message']);
        $container->setParameter('apy_data_grid.no_result_message', $config['no_result_message']);
        $container->setParameter('apy_data_grid.actions_columns_size', $config['actions_columns_size']);
        $container->setParameter('apy_data_grid.actions_columns_title', $config['actions_columns_title']);
        $container->setParameter('apy_data_grid.pagerfanta', $config['pagerfanta']);
    }
}

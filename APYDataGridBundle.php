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

namespace APY\DataGridBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Console\Application;
use Symfony\Bundle\FrameworkBundle\Console\Application as SymfonyConsoleApplication;
use APY\DataGridBundle\DependencyInjection\Compiler\GridExtensionPass;

class APYDataGridBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GridExtensionPass());
    }

    /**
     * Register bundle commands.
     *
     * Register commands from Command directory only if SensioGeneratorBundle
     * is registered in Kernel.
     *
     * Whenever Application if not an instance of SymfonyConsoleApplication we
     * just stick to the default behavior.
     *
     * @param Application $application
     */
    public function registerCommands(Application $application)
    {
        if (!$application instanceof SymfonyConsoleApplication) {
            parent::registerCommands($application);

            return;
        }

        try {
            $application->getKernel()->getBundle('SensioGeneratorBundle');
            parent::registerCommands($application);
        } catch (\InvalidArgumentException $e) {
            // $kernel->getBundle() throws \InvalidArgumentException whenever
            // bundle specified is not registered. We are only providing
            // commands based on SensioGenerator functionality, so whenever
            // there are not SensioGeneratorBundle available we MUST not
            // register our commands aswell.
        }
    }
}

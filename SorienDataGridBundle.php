<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sorien\DataGridBundle\DependencyInjection\Compiler\GridExtensionPass;

class SorienDataGridBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new GridExtensionPass());
    }
}

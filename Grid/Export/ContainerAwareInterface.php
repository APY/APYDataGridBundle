<?php

namespace APY\DataGridBundle\Grid\Export;

use Symfony\Component\DependencyInjection\ContainerAwareInterface as SymfonyContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

if (interface_exists(SymfonyContainerAwareInterface::class)) {
    interface ContainerAwareInterface extends SymfonyContainerAwareInterface
    {
    }
} else {
    interface ContainerAwareInterface
    {
        public function setContainer(?ContainerInterface $container);
    }
}

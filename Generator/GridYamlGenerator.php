<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Nicolas Potier <nicolas.potier@acseo-conseil.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Generator;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Sensio\Bundle\GeneratorBundle\Generator\Generator;


class GridYamlGenerator extends Generator
{
    private $filesystem;

    /**
     * Constructor.
     *
     * @param Filesystem $filesystem A Filesystem instance
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function generate(BundleInterface $bundle, $entityClass, $entity, $group, $extension)
    {
        $this->setSkeletonDirs(__DIR__.'/../Resources/skeleton');
        $dir = sprintf("%s/Resources/config/grid",$bundle->getPath());
        if (!file_exists($dir)) {
            $this->filesystem->mkdir($dir, 0777);
        }

        $gridFile = sprintf("%s/%s.%s%s", $dir, $entity, $group, $extension);

        $instance = new \ReflectionClass($entityClass);

        $parameters = array(
            'columns'  => $instance->getProperties(),
            'entityClass' => $entityClass
        );

        $this->renderFile('grid/grid.yml.twig', $gridFile, $parameters);
    }

}

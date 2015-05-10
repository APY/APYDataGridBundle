<?php
/*
 * This file is part of the DataGridBundle.
 *
 * (c) Nicolas Potier <nicolas.potier@acseo-conseil.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Sensio\Bundle\GeneratorBundle\Command\Validators;
use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCommand;

use APY\DataGridBundle\Generator\GridYamlGenerator;

class GenerateGridCommand extends GenerateDoctrineCommand
{
    /**
     * File extension
     * @var string
     */
    protected $_extension = '.grid.yml';
    
    protected function configure()
    {
        $this
            ->setName('apydatagrid:generate:grid')
            ->setDescription('Generate the grid configuration for an entity')
            ->addArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)')
            ->addArgument('group', InputArgument::REQUIRED, 'The group name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {           
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        $group = $input->getArgument('group', "default");

        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        $instance = new \ReflectionClass($entityClass);
        $generator = $this->getGenerator(null);
        $generator->generate($this->getContainer()->get('kernel')->getBundle($bundle), $entityClass, $entity, $group, $this->_extension);
    }

    protected function createGenerator($bundle = null)
    {
        $generator = new GridYamlGenerator($this->getContainer()->get('filesystem'));
        return $generator;
    }
}

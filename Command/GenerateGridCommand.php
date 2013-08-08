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
            ->setDefinition(array(
                new InputOption('entity', '', InputOption::VALUE_REQUIRED, 'The entity class name to initialize (shortcut notation)'),
            	new InputOption('group', '', InputOption::VALUE_REQUIRED, 'The group name'),                
            ))
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {     
      
		$entity = Validators::validateEntityName($input->getOption('entity'));
		$group = $input->getOption('group', "default");

        list($bundle, $entity) = $this->parseShortcutNotation($entity);

		$entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        
  /*      
        echo $bundle;
        echo " ";
        echo $entity;
        echo " ";
        echo $entityClass;
		echo " ";
		echo $group;
*/
		$instance = new \ReflectionClass($entityClass);

		$generator = $this->getGenerator(null);
		$generator->generate($this->getContainer()->get('kernel')->getBundle($bundle), $entityClass, $entity, $group, $this->_extension);
		/*
		if (2 != count($parts = explode(':', $bundleEntity))) {
            throw new \InvalidArgumentException(sprintf('The "%s" entity is not a valid a:b Bundle Entity.', $bundleEntity));
        }

        list($bundle, $entity) = $parts;

        $manager = new DisconnectedMetadataFactory($this->getContainer()->get('doctrine'));

        $name = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle.'\\'.$entity);

        echo $name; die();
        /*
        if (class_exists($name)) {
            $output->writeln(sprintf('Generating entity "<info>%s</info>"', $name));
            $metadata = $manager->getClassMetadata($name, $input->getOption('path'));
        } else {
            $output->writeln(sprintf('Generating entities for namespace "<info>%s</info>"', $name));
            $metadata = $manager->getNamespaceMetadata($name, $input->getOption('path'));
        }
        */
    }

	protected function createGenerator($bundle = null)
    {
    	//return null;
        $generator = new GridYamlGenerator($this->getContainer()->get('filesystem'));
        return $generator;
    }

    /**
     * Locate a YAML File based on a directory convention
     * @param \ReflectionClass $instance an instance of the class
     * @param String $group the name of the group
     * @return String the file name
     */
    /*
    private function locateYamlFile($instance, $group) {
        $bundleName = $this->getBundleNameForClass($instance->getName());

        $fileToLocate = sprintf("@%s/Resources/config/grid/%s.%s%s", 
            $bundleName,
            basename($instance->getFileName(), ".php"),
            $group,
            $this->_extension );

        return $this->kernel->locateResource($fileToLocate);
    }
    */
}
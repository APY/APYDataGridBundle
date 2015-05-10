<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Nicolas Potier <nicolas.potier@acseo-conseil.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Mapping\Driver;

use APY\DataGridBundle\Grid\Mapping\Column as Column;
use APY\DataGridBundle\Grid\Mapping\Source as Source;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;

class Yaml implements DriverInterface
{

    protected $columns;
    protected $filterable;
    protected $fields;
    protected $loaded;
    protected $groupBy;

    protected $kernel;
    
    /**
     * File extension
     * @var string
     */
    protected $_extension = '.grid.yml';


    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }
    
    public function getClassColumns($class, $group = 'default')
    {
        $this->loadMetadataFromReader($class, $group);

        return isset($this->columns[$class][$group]) ? $this->columns[$class][$group] : array();        
    }

    public function getFieldsMetadata($class, $group = 'default')
    {
        $this->loadMetadataFromReader($class, $group);

        return isset($this->fields[$class][$group]) ? $this->fields[$class][$group] : array();
    }

    public function getGroupBy($class, $group = 'default')
    {
        return isset($this->groupBy[$class][$group]) ? $this->groupBy[$class][$group] : array();
    }

    protected function loadMetadataFromReader($className, $group = 'default')
    {
        if (isset($this->loaded[$className][$group])) return;

        $instance = new \ReflectionClass($className);
        $content = $this->getYamlContent($instance, $group);
        if (!$content) {
            return;
        }
        // TODO valider la présence de ces clés
        // Peut être en passant par un TreeBuilder ?
        $class = $instance->getName();
        if (!isset($this->columns[$class])) {
            $this->columns[$class] = array();
        }

        $columns = $content[$class]["Source"]["columns"];
        foreach ($columns as $colName => $properties) {
            if (!isset($this->columns[$class][$group])) {
                $this->columns[$class][$group] = array();
            }
            $this->columns[$class][$group][] = $colName;
        }

        $fields = $content[$class]["Columns"];
        foreach ($fields as $fieldName => $properties) {
            if (!isset($this->fields[$class][$group])) {
                $this->fields[$class][$group] = array();
            }
            $this->fields[$class][$group][$fieldName] = $properties;
        }

        $groupBys = isset($content[$class]["Source"]["groupBy"])? $content[$class]["Source"]["groupBy"] : array();
        foreach($groupBys as $group => $groupBy) {
            $this->groupBy[$class][$group] = $groupBy;
        }

        $filterables = isset($content[$class]["Source"]["filterable"]) ? $content[$class]["Source"]["filterable"] : array();
        foreach($filterables as $group => $filterable) {
            $this->filterable[$class][$group] = $filterable;
        }

        $this->loaded[$className][$group] = true;
    }

    /**
     * Get The Yaml file associated to a class and a group
     * @param \ReflectionClass $instance an instance of the class
     * @param String $group the name of the group
     * @return array the parsed YAML file
     */
    private function getYamlContent($instance, $group)
    {       
        $yamlFile = $this->locateYamlFile($instance, $group);
        $content = false;

        if ($yamlFile) {
            $yamlParser = new Parser();
            $content = $yamlParser->parse(file_get_contents($yamlFile));
        }
        
        return $content;
    }

    /**
     * Locate a YAML File based on a directory convention
     * @param \ReflectionClass $instance an instance of the class
     * @param String $group the name of the group
     * @return String the file name
     */
    private function locateYamlFile($instance, $group) {
        $bundleName = $this->getBundleNameForClass($instance->getName());

        $fileToLocate = sprintf("@%s/Resources/config/grid/%s.%s%s", 
            $bundleName,
            basename($instance->getFileName(), ".php"),
            $group,
            $this->_extension );

        try {
            return $this->kernel->locateResource($fileToLocate);
        }
        catch (\Exception $e) {
            // The exception is silent
            return false;
        }
    }

    /**
     * Get The Bundle Name of an entity class
     * @param String an entity namespace
     * @return the Bundle name of the entity
     */
    private function getBundleNameForClass($rootEntityName) {
        $bundles = $this->kernel->getBundles();
        $bundleName = null;

        foreach($bundles as $type => $bundle){
            $className = get_class($bundle);

            $entityClass = substr($rootEntityName,0,strpos($rootEntityName,'\\Entity\\'));

            if(strpos($className,$entityClass) !== FALSE){
                $bundleName = $type;
            }
        }

        if (null === $bundleName) {
            throw new \Exception(sprintf("Bundle was not found for entity %s, maybe you should declare it in AppKernel", $rootEntityName));
        }

        return $bundleName;
    }
}

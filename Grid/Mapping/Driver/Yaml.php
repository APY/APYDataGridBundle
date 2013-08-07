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

	/**
     * File extension
     * @var string
     */
    protected $_extension = '.grid.yml';

    public function getClassColumns($class, $group = 'default')
    {
		$this->loadMetadataFromReader($class, $group);

        return $this->columns[$class][$group];
    }

    public function getFieldsMetadata($class, $group = 'default')
    {
        $this->loadMetadataFromReader($class, $group);

        return $this->fields[$class][$group];
    }

    public function getGroupBy($class, $group = 'default')
    {
        return isset($this->groupBy[$class][$group]) ? $this->groupBy[$class][$group] : array();
    }

    protected function loadMetadataFromReader($className, $group = 'default')
    {
        if (isset($this->loaded[$className][$group])) return;

    	$instance = new \ReflectionClass($className);
        $content = $this->getYamlContent($instance);

        // TODO valider la présence de ces clés
        // Peut être en passant par un TreeBuilder ?
        $class = $instance->getName();
        if (!isset($this->columns[$class])) {
        	$this->columns[$class] = array();
        }

        $columns = $content[$class]["Source"]["columns"];
        foreach ($columns as $colName => $properties) {
        	if (isset($properties["group"])) {
        		$groups = explode(",", $properties["group"]);
        	}
        	else {
        		$groups = array("default");
        	}
        	
        	foreach ($groups as $group) {
        		if (!isset($this->columns[$class][$group])) {
        			$this->columns[$class][$group] = array();
        		}
        		$this->columns[$class][$group][] = $colName;
        	}
        }

        $fields = $content[$class]["Columns"];
        foreach ($fields as $fieldName => $properties) {
           if (isset($properties["group"])) {
                $groups = explode(",", $properties["group"]);
            }
            else {
                $groups = array("default");
            }
            foreach ($groups as $group) {
                if (!isset($this->fields[$class][$group])) {
                    $this->fields[$class][$group] = array();
                }
                $this->fields[$class][$group][$fieldName] = $properties;
            }
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

    private function getYamlContent($instance)
    {   	
    	$yamlFile = str_replace(".php", $this->_extension, $instance->getFileName());
    	if (!file_exists($yamlFile)) {
    		throw new \Exception("Yaml configuration file was not found for $class");
    	}
		
		$yamlParser = new Parser();
		try {
		    $content = $yamlParser->parse(file_get_contents($yamlFile));
		} catch (ParseException $e) {
		    printf("Unable to parse the YAML string: %s", $e->getMessage());
		}	

		return $content;
    }
}

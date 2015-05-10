<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @todo check for column extensions
 */

namespace APY\DataGridBundle\Grid\Mapping\Metadata;

class Manager
{
    /**
     * @var \APY\DataGridBundle\Grid\Mapping\Driver\DriverInterface[]
     */
    protected $drivers;

    protected $container;
    
    public function __construct($container)
    {
        $this->container = $container;
        $this->drivers = new DriverHeap();
    }

    public function addDriver($driver, $priority)
    {
        $this->drivers->insert($driver, $priority);
    }

    /**
     * Add drivers to the driver list
     */
    public function setDrivers($driverList)
    {
        $priority = 1;
        foreach ($driverList as $driverName) {
            switch ($driverName) {
                case "annotation" :
                    $driver = $this->container->get("grid.metadata.driver.annotation");
                    break;
                case "yml" :
                    $driver = $this->container->get("grid.metadata.driver.yaml");
                    break;
                default :
                    throw new \Exception("Driver $driverName not found");
            }
            $this->addDriver($driver, $priority);
            $priority++;    
        }
    } 

    /**
     * @todo remove this hack
     * @return \APY\DataGridBundle\Grid\Mapping\Metadata\DriverHeap
     */
    public function getDrivers()
    {
        return clone $this->drivers;
    }

    public function getMetadata($className, $group = 'default')
    {
        $metadata = new Metadata();

        $columns = $fieldsMetadata = $groupBy = array();

        foreach ($this->getDrivers() as $driver) {
            $columns = array_merge($columns, $driver->getClassColumns($className, $group));
            $fieldsMetadata[] = $driver->getFieldsMetadata($className, $group);
            $groupBy = array_merge($groupBy, $driver->getGroupBy($className, $group));
        }

        $mappings = $cols = array();

        foreach ($columns as $fieldName) {
            $map = array();

            foreach($fieldsMetadata as $field) {
                if (isset($field[$fieldName]) && (!isset($field[$fieldName]['groups']) || in_array($group, (array) $field[$fieldName]['groups']))) {
                    $map = array_merge($map, $field[$fieldName]);
                }
            }

            if (!empty($map)) {
                $mappings[$fieldName] = $map;
                $cols[] = $fieldName;
            }
        }

        if (empty($cols)) {
            throw new \Exception(sprintf("No metadata information has been found for %s (group : %s)", $className, $group));
        }

        $metadata->setFields($cols);
        $metadata->setFieldsMappings($mappings);
        $metadata->setGroupBy($groupBy);

        return $metadata;
    }
}

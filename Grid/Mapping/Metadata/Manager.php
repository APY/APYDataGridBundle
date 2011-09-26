<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @todo check for column extensions
 */

namespace Sorien\DataGridBundle\Grid\Mapping\Metadata;

class DriverHeap extends \SplPriorityQueue
{
     public function compare($priority1, $priority2)
     {
         if ($priority1 === $priority2) return 0;
         return $priority1 > $priority2 ? -1 : 1;
     }
}

class Manager
{
    /**
     * @var \Sorien\DataGridBundle\Grid\Mapping\Driver\DriverInterface[]
     */
    private $drivers;

    public function __construct()
    {
        $this->drivers = new DriverHeap();
    }

    public function addDriver($driver, $priority)
    {
        $this->drivers->insert($driver, $priority);
    }

    /**
     * @todo remove this hack
     * @return \Sorien\DataGridBundle\Grid\Mapping\Driver\DriverInterface[]|DriverHeap
     */
    public function getDrivers()
    {
        return clone $this->drivers;
    }

    public function getMetadata($className)
    {
        $metadata = new Metadata();

        $columns = $fieldsMetadata = array();

        foreach ($this->getDrivers() as $driver)
        {
            $columns = array_merge($columns, $driver->getClassColumns($className));
            $fieldsMetadata[] = $driver->getFieldsMetadata($className);
        }

        $mappings = $cols = array();

        foreach ($columns as $fieldName)
        {
            $map = array();

            foreach($fieldsMetadata as $field)
            {
                if (isset($field[$fieldName]))
                {
                    $map = array_merge($map, $field[$fieldName]);
                }
            }

            if (!empty($map))
            {
                $mappings[$fieldName] = $map;
                $cols[] = $fieldName;
            }
        }

        $metadata->setFields($cols);
        $metadata->setFieldsMappings($mappings);

        return $metadata;
    }
}

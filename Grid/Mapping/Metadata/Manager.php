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
    protected $drivers;

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

    public function getMetadata($className, $group = 'default')
    {
        $metadata = new Metadata();

        $columns = $fieldsMetadata = $groupBy = array();

        foreach ($this->getDrivers() as $driver)
        {
            $columns = array_merge($columns, $driver->getClassColumns($className, $group));
            $fieldsMetadata[] = $driver->getFieldsMetadata($className, $group);
            $groupBy = array_merge($groupBy, $driver->getGroupBy($className, $group));
        }

        $mappings = $cols = array();

        foreach ($columns as $fieldName)
        {
            $map = array();

            foreach($fieldsMetadata as $field)
            {
                if (isset($field[$fieldName])
                    && (!isset($field[$fieldName]['groups']) || in_array($group, (array) $field[$fieldName]['groups'])))
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
        $metadata->setGroupBy($groupBy);

        return $metadata;
    }
}

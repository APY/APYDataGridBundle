<?php
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

    public function getMetadata($className)
    {
        $metadata = new Metadata();

        $columns = $fieldsMetadata = array();

        foreach ($this->drivers as $driver)
        {
            $columns = array_merge($columns, $driver->getClassColumns($className));
            $fieldsMetadata[] = $driver->getFieldsMetadata($className);
        }

        $metadata->setFields($columns);
        $mappings = array();

        foreach ($columns as $fieldName)
        {
            $mappings[$fieldName] = array();

            foreach($fieldsMetadata as $field)
            {
                if (isset($field[$fieldName]))
                {
                    $mappings[$fieldName] = array_merge($mappings[$fieldName], $field[$fieldName]);
                }
            }
        }
        $metadata->setFieldsMappings($mappings);

        return $metadata;
    }
}

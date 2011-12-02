<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Mapping\Driver;

use Sorien\DataGridBundle\Grid\Mapping\Column as Column;
use Sorien\DataGridBundle\Grid\Mapping\Source as Source;

class Annotation implements DriverInterface
{
    private $columns;
    private $filterable;
    private $fields;
    private $loaded;

    private $reader;

    public function __construct($reader)
    {
        $this->reader = $reader;
        $this->columns = $this->fields = $this->loaded = array();
    }

    public function getClassColumns($class)
    {
        $this->loadMetadataFromReader($class);
        return $this->columns[$class];
    }

    public function getFieldsMetadata($class)
    {
        $this->loadMetadataFromReader($class);
        return $this->fields[$class];
    }

    private function loadMetadataFromReader($className)
    {
        if (isset($this->loaded[$className])) return;

        $reflection = new \ReflectionClass($className);
        $properties = array();

        foreach ($reflection->getProperties() as $property)
        {
            $this->fields[$className][$property->getName()] = array();

            foreach ($this->reader->getPropertyAnnotations($property) as $class)
            {
                $this->getMetadataFromClassProperty($className, $class, $property->getName());
                $properties[] = $property->getName();
            }
        }

        foreach ($this->reader->getClassAnnotations($reflection) as $class)
        {
            $this->getMetadataFromClass($className, $class);
        }

        if (empty($this->columns[$className]))
        {
            $this->columns[$className] = array_keys($this->fields[$className]);
        }

        $this->loaded[$className] = true;
    }

    protected function getMetadataFromClassProperty($className, $class, $name = null)
    {
        if ($class instanceof Column)
        {
            $metadata = $class->getMetadata();

            if (!isset($metadata['filterable']))
            {
                $metadata['filterable'] = isset($this->filterable[$className]) ? $this->filterable[$className] : true;
            }

            if (isset($metadata['id']) && $name !== null)
            {
                throw new \Exception(sprintf('Parameter `id` can\'t be used in annotations for property `%s`, please remove it from class %s', $name, $className));
            }

            if (is_null($name))
            {
                if (isset($metadata['id']))
                {
                    $this->fields[$className][$metadata['id']]['source'] = false;
                }
                else
                {
                    throw new \Exception(sprintf('Missing parameter `id` in annotations for extra column of class %s', $className));
                }

            }
            else
            {
                $metadata['id'] = $name;
            }

            if (isset($metadata['field']))
            {
                $metadata['source'] = true;
            }

            foreach ($metadata as $key => $value)
            {
                $this->fields[$className][$metadata['id']][$key] = $value;
            }
        }
    }

    protected function getMetadataFromClass($className, $class)
    {
        if ($class instanceof Source)
        {
            $this->columns[$className] = $class->getColumns();
            $this->filterable[$className] = $class->isFilterable();
        }
        else
        {
            $this->getMetadataFromClassProperty($className, $class);
        }
    }
}

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

    private function loadMetadataFromReader($className, $group = 'default')
    {
        if (isset($this->loaded[$className][$group])) return;

        $reflection = new \ReflectionClass($className);
        $properties = array();

        foreach ($reflection->getProperties() as $property)
        {
            $this->fields[$className][$group][$property->getName()] = array();

            foreach ($this->reader->getPropertyAnnotations($property) as $class)
            {
                $this->getMetadataFromClassProperty($className, $class, $property->getName(), $group);
                $properties[] = $property->getName();
            }
        }

        foreach ($this->reader->getClassAnnotations($reflection) as $class)
        {
            $this->getMetadataFromClass($className, $class);
        }

        if (empty($this->columns[$className][$group]))
        {
            $this->columns[$className][$group] = array_keys($this->fields[$className][$group]);
        }

        $this->loaded[$className][$group] = true;
    }

    protected function getMetadataFromClassProperty($className, $class, $name = null, $group = 'default')
    {
        if ($class instanceof Column)
        {
            $metadata = $class->getMetadata();

            if (!isset($metadata['filterable']))
            {
                $metadata['filterable'] = isset($this->filterable[$className][$group]) ? $this->filterable[$className][$group] : true;
            }

            if (isset($metadata['id']) && $name !== null)
            {
                throw new \Exception(sprintf('Parameter `id` can\'t be used in annotations for property `%s`, please remove it from class %s', $name, $className));
            }

            if (is_null($name))
            {
                if (isset($metadata['id']))
                {
                    $this->fields[$className][$group][$metadata['id']]['source'] = false;
                }
                else
                {
                    throw new \Exception(sprintf('Missing parameter `id` in annotations for extra column of class %s', $className));
                }

            }
            else
            {
                // Relationship handle
                if (isset($metadata['field']) && strpos($metadata['field'], '.')) {
                    $metadata['id'] = $metadata['field'];

                    // Title is not set by default like properties of the entity (see getFieldsMetadata method of a source)
                    if (!isset($metadata['title'])) {
                        $metadata['title'] = $metadata['field'];
                    }
                }
                else {
                    $metadata['id'] = $name;
                }
            }

            if (!isset($metadata['title'])) {
                $metadata['title'] = $metadata['id'];
            }

            if (isset($metadata['field']))
            {
                $metadata['source'] = true;
            }

            foreach ($metadata as $key => $value)
            {
                $this->fields[$className][$group][$metadata['id']][$key] = $value;
            }
        }
    }

    protected function getMetadataFromClass($className, $class)
    {
        if ($class instanceof Source)
        {
            foreach ($class->getGroups() as $group) {
                $this->columns[$className][$group] = $class->getColumns();
                $this->filterable[$className][$group] = $class->isFilterable();
            }
        }
        elseif ($class instanceof Column)
        {
            foreach ($class->getGroups() as $group) {
                $this->getMetadataFromClassProperty($className, $class, null, $group);
            }
        }
    }
}

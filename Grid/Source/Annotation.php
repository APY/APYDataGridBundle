<?php
namespace Sorien\DataGridBundle\Grid\Source;

abstract class Annotation extends Source
{
    protected $reader;
    protected $manager;

    private $columns;
    private $fields;
    private $filterable;

    public function initialise($container)
    {
        $this->reader = $container->get('annotation_reader');
        $this->fields = array();
    }

    /**
     * @throws \Exception
     * @param $mappingFromDoctrine
     * @param $mappingFromGrid
     * @param  \Sorien\DataGridBundle\Grid\Columns $columns
     * @return \Sorien\DataGridBundle\Grid\Column\Column
     */
    private function getColumnClassFromMappings($mappingFromDoctrine, $mappingFromGrid, $columns)
    {
        //check if we have extension based on Grid mapping
        if (isset($mappingFromGrid['type']) && $columns->hasExtensionForColumnType($mappingFromGrid['type']))
        {
            return clone $columns->getExtensionForColumnType($mappingFromGrid['type']);
        }
        //check if we have extension based on Doctrine mapping
        elseif (isset($mappingFromDoctrine['type']) && $columns->hasExtensionForColumnType($mappingFromDoctrine['type']))
        {
            return clone $columns->getExtensionForColumnType($mappingFromDoctrine['type']);
        }
        else
        {
            throw new \Exception(sprintf("No suitable Column Extension found for column type [%s, %s]", @$mappingFromGrid['type'], @$mappingFromDoctrine['type']));
        }
    }

    protected function loadMetadataFromReader($className)
    {
        $reflection = new \ReflectionClass($className);

        foreach ($reflection->getProperties() as $property)
        {
            foreach ($this->reader->getPropertyAnnotations($property) as $class)
            {
                $this->getMetadataFromClassProperty($class, $property->getName());
            }
        }

        foreach ($this->reader->getClassAnnotations($reflection) as $class)
        {
            $this->getMetadataFromClass($class);
        }

    }

    protected function getMetadataFromClassProperty($class, $name = null)
    {
        if (is_a($class, 'Sorien\DataGridBundle\Grid\Mapping\Column'))
        {
            $metadata = $class->getMetadata();

            if (!isset($metadata['filterable']))
            {
                $metadata['filterable'] = $this->filterable;
            }

            if (is_null($name))
            {
                if (isset($metadata['id']))
                {
                    $name = $metadata['id'];
                    $this->setFieldMapping($name, 'source', false);
                }
                else
                {
                    throw new \Exception('Column mapping need to have specified id'.print_r($name, true));
                }
            }
            
            foreach ($metadata as $key => $value)
            {
                $this->setFieldMapping($name, $key, $value, true);
            }
        }
    }

    protected function getMetadataFromClass($class)
    {
        if (is_a($class, 'Sorien\DataGridBundle\Grid\Mapping\Source'))
        {
            $this->columns = $class->getColumns();
            $this->filterable = $class->isFilterable();
        }
        else
        {
            $this->getMetadataFromClassProperty($class);
        }
    }

    protected function setFieldMapping($fieldName, $metaName, $value, $override = false)
    {
        if (isset($this->fields[$fieldName][$metaName]))
        {
            if ($override)
            {
                $this->fields[$fieldName][$metaName] = $value;
            }
        }
        else
        {
            $this->fields[$fieldName][$metaName] = $value;
        }
    }

    protected function getFieldMapping($fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : array();
    }

    protected function getColumns()
    {
        return !empty($this->columns) ? $this->columns : array_keys($this->fields);
    }

    protected function getColumnsFromMapping($class, $columnsExtensions)
    {
        $this->loadMetadataFromReader($class);

        $mappings = new \SplObjectStorage();

        foreach ($this->getColumns() as $value)
        {
            $params = $this->getFieldMapping($value);

            if (isset($params['type']))
            {
                if ($columnsExtensions->hasExtensionForColumnType($params['type']))
                {
                    $column = clone $columnsExtensions->getExtensionForColumnType($params['type']);
                    $column->__initialize($params);

                    $mappings->attach($column);
                }
                else
                {
                    throw new \Exception(sprintf("No suitable Column Extension found for column type: %s", $params['type']));
                }
            }
        }

        return $mappings;
    }
}

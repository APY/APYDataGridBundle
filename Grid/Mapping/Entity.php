<?php
namespace Sorien\DataGridBundle\Grid\Mapping;

/**
 * @Annotation
 */
class Entity
{
    private $columns;
    private $filterable;
    private $fields;

    public function __construct($metadata = array())
    {
        $this->columns = isset($metadata['columns']) ? explode(',', $metadata['columns']) : array();
        $this->filterable = isset($metadata['filterable']) && $metadata['filterable'] == false ? false : true;
        $this->fields = array();
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumns()
    {
        return !empty($this->columns);
    }

    public function isFilterable()
    {
        return $this->filterable;
    }

    public function loadMetadataFromReader($table, $reader)
    {
        $reflection = new \ReflectionClass($table);

        foreach ($reader->getClassAnnotations($reflection) as $constraint)
        {
            if (is_a($constraint, 'Sorien\DataGridBundle\Grid\Mapping\Entity'))
            {
                $this->columns = $constraint->getColumns();
                $this->filterable = $constraint->isFilterable();
                break;
            }
        }

        foreach ($reflection->getProperties() as $property)
        {
            foreach ($reader->getPropertyAnnotations($property) as $class)
            {
                if (is_a($class, 'Sorien\DataGridBundle\Grid\Mapping\Column'))
                {
                    $metadata = $class->getMetadata();
                    
                    if (!isset($metadata['filterable']))
                    {
                        $metadata['filterable'] = $this->filterable;
                    }

                    $this->fields[$property->getName()] = $metadata;
                }
            }
        }
    }

    public function getFieldMapping($fieldName)
    {
        return isset($this->fields[$fieldName]) ? $this->fields[$fieldName] : array();
    }
}

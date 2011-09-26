<?php
namespace Sorien\DataGridBundle\Grid\Mapping\Metadata;

class Metadata
{
    private $name;
    private $fields;
    private $fieldsMappings;
    private $classMapping;

    public function setClassMapping($classMapping)
    {
        $this->classMapping = $classMapping;
    }

    public function getClassMapping()
    {
        return $this->classMapping;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFieldsMappings($fieldsMappings)
    {
        $this->fieldsMappings = $fieldsMappings;
    }

    public function getFieldsMappings()
    {
        return $this->fieldsMappings;
    }

    public function getFieldMapping($field)
    {
        return $this->fieldsMappings[$field];
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getColumnsFromMapping($columnExtensions)
    {
        $columns = new \SplObjectStorage();

        foreach ($this->getFields() as $value)
        {
            $params = $this->getFieldMapping($value);

            if (isset($params['type']))
            {
                if ($columnExtensions->hasExtensionForColumnType($params['type']))
                {
                    $column = clone $columnExtensions->getExtensionForColumnType($params['type']);
                    $column->__initialize($params);
                    $columns->attach($column);
                }
                else
                {
                    throw new \Exception(sprintf("No suitable Column Extension found for column type: %s", $params['type']));
                }
            }
        }

        return $columns;
    }

}

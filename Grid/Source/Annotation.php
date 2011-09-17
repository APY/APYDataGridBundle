<?php
namespace Sorien\DataGridBundle\Grid\Source;

abstract class Annotation extends Source
{
    protected $reader;
    protected $manager;

    public function initialise($container)
    {
        $this->reader = $container->get('annotation_reader');
    }

    protected function getColumnTypeFromCombinedMapping($mappingFromDoctrine, $mappingFromGrid)
    {
        //todo: check for available type
        if (isset($mappingFromGrid['type']) && $mappingFromGrid['type'] !== null)
        {
            return 'Sorien\DataGridBundle\Grid\Column\\'.ucfirst($mappingFromGrid['type']);
        }
        else
        {
            //todo: normalize switch for ORM and ODM
            switch ($mappingFromDoctrine['type'])
            {
                case 'integer':
                case 'smallint':
                case 'bigint':
                case 'integer':
                case 'float':
                    return 'Sorien\DataGridBundle\Grid\Column\Range';
                case 'boolean':
                    return 'Sorien\DataGridBundle\Grid\Column\Select';
                case 'datetime':
                    return 'Sorien\DataGridBundle\Grid\Column\Date';
                default:
                    return 'Sorien\DataGridBundle\Grid\Column\Text';
            }
        }
    }

    protected function getColumnMappings($documentName, $class)
    {
        $DoctrineODMClassMetadata = $this->manager->getClassMetadata($documentName);

        $GridClassMetadata = new \Sorien\DataGridBundle\Grid\Mapping\Entity();
        $GridClassMetadata->loadMetadataFromReader($class, $this->reader);

        $columns = ($GridClassMetadata->hasColumns()) ? $GridClassMetadata->getColumns() : $DoctrineODMClassMetadata->getFieldNames();
        $mappings = array();

        foreach ($columns as $value)
        {
            $ODMFieldMetadata = $DoctrineODMClassMetadata->getFieldMapping($value);
            $params = $GridClassMetadata->getFieldMapping($value);

            $params['type'] = $this->getColumnTypeFromCombinedMapping($ODMFieldMetadata, $params);
            $params['title'] = isset($params['title']) ? $params['title'] : $ODMFieldMetadata['fieldName'];
            $params['primary'] = isset($ODMFieldMetadata['id']) && $ODMFieldMetadata['id'] === true;
            $params['id'] = isset($params['id']) ? $params['id'] : $ODMFieldMetadata['fieldName'];

            $mappings[] = $params;
        }

        return $mappings;
    }
}

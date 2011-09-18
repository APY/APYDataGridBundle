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

    /**
     * @throws \Exception
     * @param $mappingFromDoctrine
     * @param $mappingFromGrid
     * @param  \Sorien\DataGridBundle\Grid\Columns $columns
     * @return \Sorien\DataGridBundle\Grid\Column\Column
     */
    protected function getColumnClassFromMappings($mappingFromDoctrine, $mappingFromGrid, $columns)
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

    protected function getColumnsMapping($documentName, $class, $columnsExtensions)
    {
        $DoctrineDoctrineClassMetadata = $this->manager->getClassMetadata($documentName);

        $GridClassMetadata = new \Sorien\DataGridBundle\Grid\Mapping\Entity();
        $GridClassMetadata->loadMetadataFromReader($class, $this->reader);

        $columns = ($GridClassMetadata->hasColumns()) ? $GridClassMetadata->getColumns() : $DoctrineDoctrineClassMetadata->getFieldNames();
        $mappings = new \SplObjectStorage();

        foreach ($columns as $value)
        {
            $ODMFieldMetadata = $DoctrineDoctrineClassMetadata->getFieldMapping($value);
            //get parameters
            $params = $GridClassMetadata->getFieldMapping($value);
            //get suitable class
            $column = $this->getColumnClassFromMappings($ODMFieldMetadata, $params, $columnsExtensions);
            //correct parameters
            $params['title'] = isset($params['title']) ? $params['title'] : $ODMFieldMetadata['fieldName'];
            $params['primary'] = isset($ODMFieldMetadata['id']) && $ODMFieldMetadata['id'] === true;
            $params['id'] = isset($params['id']) ? $params['id'] : $ODMFieldMetadata['fieldName'];
            //init columns parameters
            $column->__initialize($params);

            $mappings->attach($column);
        }

        return $mappings;
    }
}

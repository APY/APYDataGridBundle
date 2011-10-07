<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace Sorien\DataGridBundle\Grid\Source;

use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Rows;
use Sorien\DataGridBundle\Grid\Row;

class Document extends Source
{
    /**
     * @var \Doctrine\ODM\MongoDB\Query\Builder;
     */
    private $query;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $manager;

    /**
     * e.g. Base\Cms\Document\Page
     */
    private $class;

    /**
     * @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    private $odmMetadata;

    /**
     * e.g. Cms:Page
     */
    private $documentName;

    /**
     * @var \Sorien\DataGridBundle\Grid\Mapping\Metadata\Metadata
     */
    private $metadata;

    /**
     * @param string $documentName e.g. "Cms:Page"
     */
    public function __construct($documentName)
    {
        $this->documentName = $documentName;
    }

    public function initialise($container)
    {
        $this->manager = $container->get('doctrine.odm.mongodb.document_manager');
        $this->odmMetadata = $this->manager->getClassMetadata($this->documentName);
        $this->class = $this->odmMetadata->getReflectionClass()->name;

        $mapping = $container->get('grid.mapping.manager');
        $mapping->addDriver($this, -1);
        $this->metadata = $mapping->getMetadata($this->class);
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Columns $columns
     * @param \Sorien\DataGridBundle\Grid\Actions $actions
     * @return null
     */
    public function getColumns($columns)
    {
        foreach ($this->metadata->getColumnsFromMapping($columns) as $column)
        {
            $columns->addColumn($column);
        }
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Column\Column[] $columns
     * @param int $page  Page Number
     * @param int $limit  Rows Per Page
     * @return \Sorien\DataGridBundle\Grid\Rows
     */
    public function execute($columns, $page, $limit)
    {
        $this->query = $this->manager->createQueryBuilder($this->documentName);

        foreach ($columns as $column)
        {
            $this->query->select($column->getId());

            if ($column->isSorted())
            {
                $this->query->sort($column->getId(), $column->getOrder());
            }

            if ($column->isFiltered())
            {
                if($column->getFiltersConnection() == column::DATA_CONJUNCTION)
                {
                    foreach ($column->getFilters() as $filter)
                    {
                        $operator = $filter->getOperator();
                        $this->query->field($column->getId()->$operator($filter->getValue()));
                    }
                }
                elseif($column->getFiltersConnection() == column::DATA_DISJUNCTION)
                {
                    $values = array();

                    foreach ($column->getFilters() as $filter)
                    {
                        $values[] = $filter->getValue();
                    }

                    if (!empty($values))
                    {
                       $this->query->field($column->getId())->all($values);
                    }
                }
            }
        }

        if ($page > 0)
        {
            $this->query->skip($page * $limit);
        }

        $this->query->limit($limit);

        //call overridden prepareQuery or associated closure
        $this->prepareQuery($this->query);

        //execute and get results
        $result = new Rows();

        foreach($this->query->getQuery()->execute() as $resource)
        {
            $row = new Row();
            $properties = $this->getClassProperties($resource);

            foreach ($columns as $column)
            {
                $row->setField($column->getId(), $properties[$column->getId()]);
            }

            //call overridden prepareRow or associated closure
            if (($modifiedRow = $this->prepareRow($row)) != null)
            {
                $result->addRow($modifiedRow);
            }
        }

        return $result;
    }

    public function getTotalCount($columns)
    {
        $this->query->limit(null);
        $this->query->skip(null);

        return $this->query->getQuery()->execute()->count();
    }

    private function getClassProperties($obj)
    {
        $reflect = new \ReflectionClass($obj);
        $props   = $reflect->getProperties();
        $result  = array();

        foreach ($props as $property)
        {
            $property->setAccessible(true);
            $propertyValue = $property->getValue($obj);

            if ($propertyValue instanceof \DateTime)
            {
                $result[$property->getName()] = $propertyValue->format('d.m.Y H:i:s');
            }
            else
            {
                $result[$property->getName()] = $propertyValue;
            }
        }

        return $result;
    }

    public function getFieldsMetadata($class)
    {
        $result = array();
        foreach ($this->odmMetadata->getReflectionProperties() as $property)
        {
            $name = $property->getName();
            $mapping = $this->odmMetadata->getFieldMapping($name);

            $values = array();

            $values['title'] = $name;

            if (isset($mapping['fieldName']))
            {
                $values['id'] = $mapping['fieldName'];
            }

            if (isset($mapping['id']) && $mapping['id'] == 'id')
            {
                $values['primary'] = true;
            }

            switch ($mapping['type'])
            {
                case 'id':
                case 'int':
                case 'string':
                case 'float':
                    $values['type'] = 'text';
                break;
                
                case 'boolean':
                    $values['type'] = 'boolean';
                break;

                case 'date':
                    $values['type'] = 'date';
                break;
            }

            $result[$name] = $values;
        }

        return $result;
    }
    
    public function delete(array $ids) {
        $repository = $this->manager->getRepository($this->entityName);
        
        foreach ($ids as $id) {
            $object = $repository->find($id);

            if (!$object) {
                throw $this->createNotFoundException(sprintf('No %s found for id %s', $this->entityName, $id));
            }

            $this->manager->remove($object);  
        }
        
        $this->manager->flush();
    }
}
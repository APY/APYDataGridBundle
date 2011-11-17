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
     * @var int Items count
     */
    private $count;

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
     * @return null
     */
    public function getColumns($columns)
    {
        foreach ($this->metadata->getColumnsFromMapping($columns) as $column)
        {
            $columns->addColumn($column);
        }
    }

    private function normalizeOperator($operator)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? 'equals' : $operator);
    }

    private function normalizeValue($operator, $value)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? new \MongoRegex($value) : $value);
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Column\Column[] $columns
     * @param int $page  Page Number
     * @param int $limit  Rows Per Page
     * @return \Sorien\DataGridBundle\Grid\Rows
     */
    public function execute($columns, $page = 0, $limit = 0)
    {
        $this->query = $this->manager->createQueryBuilder($this->documentName);

        foreach ($columns as $column)
        {
            $this->query->select($column->getField());

            if ($column->isSorted())
            {
                $this->query->sort($column->getField(), $column->getOrder());
            }

            if ($column->isFiltered())
            {
                if($column->getFiltersConnection() == column::DATA_CONJUNCTION)
                {
                    foreach ($column->getFilters() as $filter)
                    {
                        //normalize values
                        $operator = $this->normalizeOperator($filter->getOperator());
                        $value = $this->normalizeValue($filter->getOperator(), $filter->getValue());

                        $this->query->field($column->getField())->$operator($value);
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
                        //@todo probably value normalization needed
                        $this->query->field($column->getField())->all($values);
                    }
                }
            }
        }

        if ($page > 0)
        {
            $this->query->skip($page * $limit);
        }

        if ($limit > 0)
        {
            $this->query->limit($limit);
        }

        //call overridden prepareQuery or associated closure
        $this->prepareQuery($this->query);

        //execute and get results
        $result = new Rows();

        $cursor = $this->query->getQuery()->execute();
        $this->count = $cursor->count();

        foreach($cursor as $resource)
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
        return $this->count;
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
            $values = array('title' => $name, 'source' => true);

            if (isset($mapping['fieldName']))
            {
                $values['field'] = $mapping['fieldName'];
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
                case 'many':
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

    public function getHash()
    {
        return $this->documentName;
    }

    public function delete(array $ids)
    {
        $repository = $this->manager->getRepository($this->documentName);

        foreach ($ids as $id) {
            $object = $repository->find($id);

            if (!$object) {
                throw new \Exception(sprintf('No %s found for id %s', $this->documentName, $id));
            }

            $this->manager->remove($object);
        }

        $this->manager->flush();
    }
}

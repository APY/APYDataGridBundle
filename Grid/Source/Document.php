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

use Sorien\DataGridBundle\Grid\Mapping\Entity as GridClassMetadata;
use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Rows;
use Sorien\DataGridBundle\Grid\Row;
//use Doctrine\ORM\Query\Expr\Orx;
//use Doctrine\ORM\Mapping\ClassMetadata;

class Document extends Annotation
{
    private $columnMappings;
    private $table;

    /**
     * @var \Doctrine\ODM\MongoDB\Query\Builder;
     */
    private $query;
    private $reader;
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    private $manager;

    /**
     * e.g. Base\Cms\Document\Page
     */
    private $class;
    /**
     * e.g. Cms:Page
     */
    private $documentName;

    const TABLE_ALIAS = 'a';

    /**
     * @param string $documentName e.g. "Cms:Page"
     */
    public function __construct($documentName)
    {
        $this->documentName = $documentName;
    }

    public function initialise($container)
    {
        parent::initialise($container);

        $this->manager = $container->get('doctrine.odm.mongodb.document_manager');
        $this->class = $this->manager->getClassMetadata($this->documentName)->getReflectionClass()->name;
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Columns $columns
     * @param \Sorien\DataGridBundle\Grid\Actions $actions
     * @return null
     */
    public function prepare($columns, $actions)
    {
        foreach ($this->getColumnMappings($this->documentName, $this->class) as $params)
        {
            $columnName = $params['type'];
            $columns->addColumn(new $columnName($params));
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

            $result->addRow($row);
        }

        return $result;
    }

    public function getTotalCount($columns)
    {
        $this->query->limit(null);
        $this->query->skip(null);

        return $this->query->getQuery()->execute()->count();

//        $this->query->select(sprintf("count (%s)", $this->getPrefixedName($columns->getPrimaryColumn()->getId())));
//        $this->query->setFirstResult(null);
//        //$this->query->setMaxResults(null);
//        $result = $this->query->getQuery()->getSingleResult();

        return 10; //(int)$result[1];
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
}
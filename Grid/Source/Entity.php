<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Source;

use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Rows;
use Sorien\DataGridBundle\Grid\Row;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Mapping\ClassMetadata;

class Entity extends Source
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $manager;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $query;

    /**
     * @var string e.g Vendor\Bundle\Entity\Page
     */
    private $class;

    /**
     * @var string e.g Cms:Page
     */
    private $entityName;

    /**
     * @var \Sorien\DataGridBundle\Grid\Mapping\Metadata\Metadata
     */
    private $metadata;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $ormMetadata;

    /**
     * @var array
     */
    private $joins;

    const TABLE_ALIAS = '__base__';

    /**
     * @param string $entityName e.g Cms:Page
     */
    public function __construct($entityName)
    {
        $this->entityName = $entityName;
        $this->joins = array();
    }

    public function initialise($container)
    {
        $this->manager = $container->get('doctrine')->getEntityManager();
        $this->ormMetadata = $this->manager->getClassMetadata($this->entityName);

        $this->class = $this->ormMetadata->getReflectionClass()->name;

        $mapping = $container->get('grid.mapping.manager');
        $mapping->addDriver($this, -1);
        $this->metadata = $mapping->getMetadata($this->class);
    }
    
    /**
     * @param string $name e.g. vendor.name or name
     * @return string e.g. vendor.name or __base__.name
     */
    private function getPrefixedName($name)
    {
        if (($pos = strpos($name, '.')) !== false)
        {
            $parent = substr($name, 0, $pos);
            $this->joins[$parent] = self::TABLE_ALIAS.'.'.$parent;

            return $name;
        }
        else
        {
            return self::TABLE_ALIAS.'.'.$name;
        }
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

    private function normalizeOperator($operator)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? 'like' : $operator);
    }

    private function normalizeValue($operator, $value)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? '\''.str_replace('.*', '%', $value).'\'' : $value);
    }

    /**
     * @param $columns \Sorien\DataGridBundle\Grid\Column\Column[]
     * @param $page int Page Number
     * @param $limit int Rows Per Page
     * @return \Sorien\DataGridBundle\Grid\Rows
     */
    public function execute($columns, $page, $limit)
    {
        $this->query = $this->manager->createQueryBuilder($this->class);
        $this->query->from($this->class, self::TABLE_ALIAS);
        
        $where = $this->query->expr()->andx();

        foreach ($columns as $column)
        {
            $this->query->addSelect($this->getPrefixedName($column->getId()));

            if ($column->isSorted())
            {
                $this->query->orderBy($this->getPrefixedName($column->getId()), $column->getOrder());
            }

            if ($column->isFiltered())
            {
                if($column->getFiltersConnection() == column::DATA_CONJUNCTION)
                {
                    foreach ($column->getFilters() as $filter)
                    {
                        $operator = $this->normalizeOperator($filter->getOperator());

                        $where->add($this->query->expr()->$operator(
                            $this->getPrefixedName($filter->hasId() ? $filter->getId() : $column->getId()),
                            $this->normalizeValue($filter->getOperator(), $filter->getValue())
                        ));
                    }
                }
                elseif($column->getFiltersConnection() == column::DATA_DISJUNCTION)
                {
                    $sub = $this->query->expr()->orx();

                    foreach ($column->getFilters() as $filter)
                    {
                        $operator = $this->normalizeOperator($filter->getOperator());

                        $sub->add($this->query->expr()->$operator(
                              $this->getPrefixedName($filter->hasId() ? $filter->getId() : $column->getId()),
                              $this->normalizeValue($filter->getOperator(), $filter->getValue())
                        ));
                    }
                    $where->add($sub);
                }
                $this->query->where($where);
            }
        }

        foreach ($this->joins as $alias => $field)
        {
            $this->query->leftJoin($field, $alias);
        }

        if ($page > 0)
        {
            $this->query->setFirstResult($page * $limit);
        }
        
        $this->query->setMaxResults($limit);

        //call overridden prepareQuery or associated closure
        $this->prepareQuery($this->query);

        // get query result
        $items = $this->query->getQuery()->execute(array(), Query::HYDRATE_ARRAY);

        // hydrate result
        $result = new Rows();
        foreach ($items as $item)
        {
            $row = new Row();

            foreach ($columns as $column)
            {
               $value = array_shift($item);
               $row->setField($column->getId(), $value);
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
        $this->query->select(sprintf("count (%s)", $this->getPrefixedName($columns->getPrimaryColumn()->getId())));
        $this->query->setFirstResult(null);
        $this->query->setMaxResults(null);
        $result = $this->query->getQuery()->getSingleResult();

        return (int)$result[1];
    }

    public function getFieldsMetadata($class)
    {
        $result = array();
        foreach ($this->ormMetadata->getFieldNames() as $name)
        {
            $mapping = $this->ormMetadata->getFieldMapping($name);
            $values = array('title' => $name);

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
                case 'integer':
                case 'smallint':
                case 'bigint':
                case 'string':
                case 'text':
                case 'float':
                case 'decimal':
                    $values['type'] = 'text';
                break;
                case 'boolean':
                    $values['type'] = 'boolean';
                break;
                case 'date':
                case 'datetime':
                case 'time':
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
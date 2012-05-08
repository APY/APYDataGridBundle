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
use Doctrine\ORM\Query\Expr\Comparison;

class Entity extends Source
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $manager;

    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $query;

    /**
     * @var string e.g Vendor\Bundle\Entity\Page
     */
    protected $class;

    /**
     * @var string e.g Cms:Page
     */
    protected $entityName;

    /**
     * @var string e.g mydatabase
     */
    protected $managerName;


    /**
     * @var \Sorien\DataGridBundle\Grid\Mapping\Metadata\Metadata
     */
    protected $metadata;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    protected $ormMetadata;

    /**
     * @var array
     */
    protected $joins;

    /**
     * @var string
     */
    protected $group;

    /**
     * @var string
     */
    protected $groupBy;

    const TABLE_ALIAS = '_a';
    const COUNT_ALIAS = '__count';

    /**
     * @param string $entityName e.g Cms:Page
     * @param string $managerName e.g. mydatabase
     */
    public function __construct($entityName, $group = 'default', $managerName = null)
    {
        $this->entityName = $entityName;
        $this->managerName = $managerName;
        $this->joins = array();
        $this->group = $group;
    }

    public function initialise($container)
    {
        $this->manager = $container->get('doctrine')->getEntityManager($this->managerName);
        $this->ormMetadata = $this->manager->getClassMetadata($this->entityName);

        $this->class = $this->ormMetadata->getReflectionClass()->name;

        $mapping = $container->get('grid.mapping.manager');

        /** todo autoregister mapping drivers with tag */
        $mapping->addDriver($this, -1);
        $this->metadata = $mapping->getMetadata($this->class, $this->group);

        $this->groupBy = $this->metadata->getGroupBy();
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Column\Column $column
     * @return string
     */
    protected function getFieldName($column, $withAlias = false)
    {
        $name = $column->getField();

        if (strpos($name, '.') === false) {
            return self::TABLE_ALIAS.'.'.$name;
        }

        $parent = $previousParent = self::TABLE_ALIAS;

        $elements = explode('.', $name);

        while ($element = array_shift($elements)) {
            if (count($elements) > 0) {
                $this->joins['_' . $element] = $parent . '.' . $element;
                $previousParent = $parent;
                $parent = '_' . $element;
                $name = $element;
            } else {
                $name .= '.'.$element;
            }
        }

        // Aggregate dql functions
        if (preg_match('/.(?P<all>(?P<field>\w+):(?P<function>\w+))$/', $name, $matches)) {
            if ($withAlias) {
                // Group by the primary field of the previous entity
                $this->query->addGroupBy($previousParent);

                return $matches['function'].'('.$parent.'.'.$matches['field'].') as '.substr($parent, 1).'::'.$matches['all'];
            }

            return substr($parent, 1).'::'.$matches['all'];
        }

        if ($withAlias) {
            return '_' . $name.' as '.str_replace('.', '::', $column->getId());
        }

        return '_'.$name;
    }

    /**
     * @param string $fieldName
     * @return string
     */
    protected function getGroupByFieldName($fieldName)
    {
        if (strpos($fieldName, '.') === false) {
            return self::TABLE_ALIAS.'.'.$fieldName;
        }

        return '_'.$fieldName;
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

    protected function normalizeOperator($operator)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? 'like' : $operator);
    }

    protected function normalizeValue($operator, $value)
    {
        if ($operator == COLUMN::OPERATOR_REGEXP)
        {
            preg_match('/\/\.\*([^\/]+)\.\*\//s', $value, $matches);
            return '\'%'.$matches[1].'%\'';
        }
        else
        {
            return $value;
        }
    }

    /**
     * @param $columns \Sorien\DataGridBundle\Grid\Column\Column[]
     * @param $page int Page Number
     * @param $limit int Rows Per Page
     * @return \Sorien\DataGridBundle\Grid\Rows
     */
    public function execute($columns, $page = 0, $limit = 0)
    {
        $this->query = $this->manager->createQueryBuilder($this->class);
        $this->query->from($this->class, self::TABLE_ALIAS);

        $where = $this->query->expr()->andx();

        $serializeColumns = array();

        foreach ($columns as $column)
        {
            $this->query->addSelect($this->getFieldName($column, true));

            if ($column->isSorted())
            {
                $this->query->orderBy($this->getFieldName($column), $column->getOrder());
            }

            if ($column->isFiltered())
            {
                if($column->getFiltersConnection() == column::DATA_CONJUNCTION)
                {
                    foreach ($column->getFilters() as $filter)
                    {
                        $operator = $this->normalizeOperator($filter->getOperator());

                        $where->add($this->query->expr()->$operator(
                            $this->getFieldName($column),
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
                              $this->getFieldName($column),
                              $this->normalizeValue($filter->getOperator(), $filter->getValue())
                        ));
                    }

                    $where->add($sub);
                }

                $this->query->where($where);
            }

            if ($column->getType() === 'array') {
                $serializeColumns[] = $column->getId();
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

        if ($limit > 0)
        {
            $this->query->setMaxResults($limit);
        }

        if (!empty($this->groupBy)) {
            $this->query->resetDQLPart('groupBy');

            foreach ($this->groupBy as $field)
            {
                $this->query->addGroupBy($this->getGroupByFieldName($field));
            }
        }

        //call overridden prepareQuery or associated closure
        $this->prepareQuery($this->query);

        $items = $this->query->getQuery()->getResult();

        // hydrate result
        $result = new Rows();

        foreach ($items as $item)
        {
            $row = new Row();

            foreach ($item as $key => $value)
            {
                $key = str_replace('::', '.', $key);

                if (in_array($key, $serializeColumns))
                {
                    $value = unserialize($value);
                }

                $row->setField($key, $value);
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
        $this->query->select($this->getFieldName($columns->getPrimaryColumn()));
        $this->query->setFirstResult(null);
        $this->query->setMaxResults(null);

        $qb = $this->manager->createQueryBuilder();

        // Remove useless part
        $this->query->resetDQLPart('orderBy');

        $qb->select($qb->expr()->count(self::COUNT_ALIAS. '.' . $columns->getPrimaryColumn()->getField()));
        $qb->from($this->entityName, self::COUNT_ALIAS);
        $qb->where($qb->expr()->in(self::COUNT_ALIAS. '.' . $columns->getPrimaryColumn()->getField(), $this->query->getDQL()));

        //copy existing parameters.
        $qb->setParameters($this->query->getParameters());

        $result = $qb->getQuery()->getSingleResult();

        return (int) $result[1];
    }

    public function getFieldsMetadata($class)
    {
        $result = array();
        foreach ($this->ormMetadata->getFieldNames() as $name)
        {
            $mapping = $this->ormMetadata->getFieldMapping($name);
            $values = array('title' => $name, 'source' => true);

            if (isset($mapping['fieldName']))
            {
                $values['field'] = $mapping['fieldName'];
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
                case 'array':
                case 'object':
                    $values['type'] = 'array';
                    break;
            }

            $result[$name] = $values;
        }

        return $result;
    }

    public function getHash()
    {
        return $this->entityName;
    }

    public function delete(array $ids)
    {
        $repository = $this->getRepository();

        foreach ($ids as $id) {
            $object = $repository->find($id);

            if (!$object) {
                throw new \Exception(sprintf('No %s found for id %s', $this->entityName, $id));
            }

            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function getRepository()
    {
        return $this->manager->getRepository($this->entityName);
    }
}

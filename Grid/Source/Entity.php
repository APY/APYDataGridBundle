<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Source;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Rows;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Helper\ORMCountWalker;
use Doctrine\ORM\Query;

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
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $querySelectfromSource;

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
     * @var \APY\DataGridBundle\Grid\Mapping\Metadata\Metadata
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
     * @param \APY\DataGridBundle\Grid\Column\Column $column
     * @return string
     */
    protected function getFieldName($column, $withAlias = false, $forHavingClause = false)
    {
        $name = $column->getField();

        if (strpos($name, '.') === false) {
            return self::TABLE_ALIAS.'.'.$name;
        }

        $parent = self::TABLE_ALIAS;
        $previousParent = '';

        $elements = explode('.', $name);

        while ($element = array_shift($elements)) {
            if (count($elements) > 0) {
                $previousParent .= '_' . $element;
                $this->joins[$previousParent] = $parent . '.' . $element;
                $parent = '_' . $element;
            } else {
                $name = $previousParent . '.' . $element;
            }
        }

        // Aggregate dql functions
        $matches = array();
        if ($column->hasDQLFunction($matches)) {
            if (strtolower($matches['parameters']) == 'distinct') {
                $functionWithParameters = $matches['function'].'(DISTINCT '.$parent.'.'.$matches['field'].')';
            } else {
                $parameters = '';
                if ($matches['parameters'] !== '') {
                    $parameters = ', ' . (is_numeric($matches['parameters']) ? $matches['parameters'] : "'".$matches['parameters']."'");
                }

                $functionWithParameters = $matches['function'].'('.$parent.'.'.$matches['field'].$parameters.')';
            }

            if ($withAlias) {
                // Group by the primary field of the previous entity
                $this->query->addGroupBy($previousParent);
                $this->querySelectfromSource->addGroupBy($previousParent);

                return $functionWithParameters.' as '.substr($parent, 1).'::'.$matches['all'];
            }

            if ($forHavingClause) {
                return $functionWithParameters;
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
     * @param \APY\DataGridBundle\Grid\Columns $columns
     * @return null
     */
    public function getColumns($columns)
    {
        foreach ($this->metadata->getColumnsFromMapping($columns) as $column) {
            $columns->addColumn($column);
        }
    }

    protected function normalizeOperator($operator)
    {
        switch ($operator) {
            //case Column::OPERATOR_REGEXP:
            case Column::OPERATOR_LIKE:
            case Column::OPERATOR_LLIKE:
            case Column::OPERATOR_RLIKE:
            case Column::OPERATOR_NLIKE: return 'like';
            default: return $operator;
        }
    }

    protected function normalizeValue($operator, $value)
    {
        switch ($operator) {
            //case Column::OPERATOR_REGEXP:
            case Column::OPERATOR_LIKE:
            case Column::OPERATOR_NLIKE: return "%$value%";
            case Column::OPERATOR_LLIKE: return "%$value";
            case Column::OPERATOR_RLIKE: return "$value%";
            default: return $value;
        }
    }

    /**
     * @param \APY\DataGridBundle\Grid\Column\Column[] $columns
     * @param int $page Page Number
     * @param int $limit Rows Per Page
     * @param int $gridDataJunction  Grid data junction
     * @return \APY\DataGridBundle\Grid\Rows
     */
    public function execute($columns, $page = 0, $limit = 0, $maxResults = null, $gridDataJunction = Column::DATA_CONJUNCTION)
    {
        $this->query = $this->manager->createQueryBuilder($this->class);
        $this->query->from($this->class, self::TABLE_ALIAS);
        $this->querySelectfromSource = clone $this->query;

        $bindIndex = 123;
        $serializeColumns = array();
        $where = $gridDataJunction === Column::DATA_CONJUNCTION ? $this->query->expr()->andx() : $this->query->expr()->orx();

        foreach ($columns as $column) {
            $fieldName = $this->getFieldName($column, true);
            $this->query->addSelect($fieldName);
            $this->querySelectfromSource->addSelect($fieldName);

            if ($column->isSorted()) {
                $this->query->orderBy($this->getFieldName($column), $column->getOrder());
            }

            if ($column->isFiltered()) {
                // Some attributes of the column can be changed in this function
                $filters = $column->getFilters('entity');

                $isDisjunction = $column->getDataJunction() === Column::DATA_DISJUNCTION;

                $hasHavingClause = $column->hasDQLFunction();

                $sub = $isDisjunction ? $this->query->expr()->orx() : ($hasHavingClause ? $this->query->expr()->andx() : $where);

                foreach ($filters as $filter) {
                    $operator = $this->normalizeOperator($filter->getOperator());

                    $q = $this->query->expr()->$operator($this->getFieldName($column, false, $hasHavingClause), "?$bindIndex");

                    if ($filter->getOperator() == Column::OPERATOR_NLIKE) {
                        $q = $this->query->expr()->not($q);
                    }

                    $sub->add($q);

                    if ($filter->getValue() !== null) {
                        $this->query->setParameter($bindIndex++, $this->normalizeValue($filter->getOperator(), $filter->getValue()));
                    }
                }

                if ($hasHavingClause) {
                    $this->query->having($sub);
                } elseif ($isDisjunction) {
                    $where->add($sub);
                }
            }

            if ($column->getType() === 'array') {
                $serializeColumns[] = $column->getId();
            }
        }

        if ($where->count()> 0) {
            $this->query->where($where);
        }

        foreach ($this->joins as $alias => $field) {
            $this->query->leftJoin($field, $alias);
            $this->querySelectfromSource->leftJoin($field, $alias);
        }

        if ($page > 0) {
            $this->query->setFirstResult($page * $limit);
        }

        if ($limit > 0) {
            if ($maxResults !== null && ($maxResults - $page * $limit < $limit)) {
                $limit = $maxResults - $page * $limit;
            }

            $this->query->setMaxResults($limit);
        } elseif ($maxResults !== null) {
            $this->query->setMaxResults($maxResults);
        }

        if (!empty($this->groupBy)) {
            $this->query->resetDQLPart('groupBy');
            $this->querySelectfromSource->resetDQLPart('groupBy');

            foreach ($this->groupBy as $field) {
                $this->query->addGroupBy($this->getGroupByFieldName($field));
                $this->querySelectfromSource->addGroupBy($this->getGroupByFieldName($field));
            }
        }

        //call overridden prepareQuery or associated closure
        $this->prepareQuery($this->query);

        $items = $this->query->getQuery()->getResult();

        // hydrate result
        $result = new Rows();

        foreach ($items as $item) {
            $row = new Row();

            foreach ($item as $key => $value) {
                $key = str_replace('::', '.', $key);

                if (in_array($key, $serializeColumns) && is_string($value)) {
                    $value = unserialize($value);
                }

                $row->setField($key, $value);
            }

            //call overridden prepareRow or associated closure
            if (($modifiedRow = $this->prepareRow($row)) != null) {
                $result->addRow($modifiedRow);
            }
        }

        return $result;
    }

    public function getTotalCount($maxResults = null)
    {
        // From Doctrine\ORM\Tools\Pagination\Paginator::count()
        $countQuery = $this->query->getQuery();

        if ( ! $countQuery->getHint(ORMCountWalker::HINT_DISTINCT)) {
            $countQuery->setHint(ORMCountWalker::HINT_DISTINCT, true);
        }

        $countQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, array('APY\DataGridBundle\Grid\Helper\ORMCountWalker'));
        $countQuery->setFirstResult(null)->setMaxResults($maxResults);

        try {
            $data = $countQuery->getScalarResult();
            $data = array_map('current', $data);
            $count = array_sum($data);
        } catch(NoResultException $e) {
            $count = 0;
        }

        return $count;
    }

    public function getFieldsMetadata($class, $group = 'default')
    {
        $result = array();
        foreach ($this->ormMetadata->getFieldNames() as $name)
        {
            $mapping = $this->ormMetadata->getFieldMapping($name);
            $values = array('title' => $name, 'source' => true);

            if (isset($mapping['fieldName'])) {
                $values['field'] = $mapping['fieldName'];
                $values['id'] = $mapping['fieldName'];
            }

            if (isset($mapping['id']) && $mapping['id'] == 'id') {
                $values['primary'] = true;
            }

            switch ($mapping['type']) {
                case 'string':
                case 'text':
                    $values['type'] = 'text';
                    break;
                case 'integer':
                case 'smallint':
                case 'bigint':
                case 'float':
                case 'decimal':
                    $values['type'] = 'number';
                    break;
                case 'boolean':
                    $values['type'] = 'boolean';
                    break;
                case 'date':
                    $values['type'] = 'date';
                    break;
                case 'datetime':
                    $values['type'] = 'datetime';
                    break;
                case 'time':
                    $values['type'] = 'time';
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

    public function populateSelectFilters($columns, $loop = false)
    {
        /* @var $column Column */
        foreach ($columns as $column) {
            $selectFrom = $column->getSelectFrom();

            if ($column->getFilterType() === 'select' && ($selectFrom === 'source' || $selectFrom === 'query')) {

                // For negative operators, show all values
                if ($selectFrom === 'query') {
                    foreach($column->getFilters('entity') as $filter) {
                        if (in_array($filter->getOperator(), array(Column::OPERATOR_NEQ, Column::OPERATOR_NLIKE))) {
                            $selectFrom = 'source';
                            break;
                        }
                    }
                }

                // Dynamic from query or not ?
                $query = ($selectFrom === 'source') ? clone $this->querySelectfromSource : clone $this->query;

                $result = $query->select($this->getFieldName($column, true))
                    ->distinct()
                    ->orderBy($this->getFieldName($column), 'asc')
                    ->setFirstResult(null)
                    ->setMaxResults(null)
                    ->getQuery()
                    ->getResult();

                $values = array();
                foreach($result as $row) {
                    $value = $row[str_replace('.', '::', $column->getId())];

                    switch ($column->getType()) {
                        case 'array':
                            if (is_string($value)) {
                                $value = unserialize($value);
                            }
                            foreach ($value as $val) {
                                $values[$val] = $val;
                            }
                            break;
                        case 'number':
                            $values[$value] = $column->getDisplayedValue($value);
                            break;
                        case 'datetime':
                        case 'date':
                        case 'time':
                            $displayedValue = $column->getDisplayedValue($value);
                            $values[$displayedValue] = $displayedValue;
                            break;
                        default:
                            $values[$value] = $value;
                    }
                }

                // It avoids to have no result when the other columns are filtered
                if ($selectFrom === 'query' && empty($values) && $loop === false) {
                    $column->setSelectFrom('source');
                    $this->populateSelectFilters($columns, true);
                } else {
                    if ($column->getType() == 'array') {
                        natcasesort($values);
                    }

                    $column->setValues($values);
                }
            }
        }
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

    public function getHash()
    {
        return $this->entityName;
    }
}

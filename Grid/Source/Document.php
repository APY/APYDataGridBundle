<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 */

namespace APY\DataGridBundle\Grid\Source;

use APY\DataGridBundle\Grid\Rows;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Column\Column;

class Document extends Source
{
    /**
     * @var \Doctrine\ODM\MongoDB\Query\Builder;
     */
    protected $query;

    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $manager;

    /**
     * e.g. Base\Cms\Document\Page
     */
    protected $class;

    /**
     * @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    protected $odmMetadata;

    /**
     * e.g. Cms:Page
     */
    protected $documentName;

    /**
     * @var \APY\DataGridBundle\Grid\Mapping\Metadata\Metadata
     */
    protected $metadata;

    /**
     * @var int Items count
     */
    protected $count;

    /**
     * @var string
     */
    protected $group;

    /**
     * @param string $documentName e.g. "Cms:Page"
     */
    public function __construct($documentName, $group = 'default')
    {
        $this->documentName = $documentName;
        $this->group = $group;
    }

    public function initialise($container)
    {
        $this->manager = $container->get('doctrine.odm.mongodb.document_manager');
        $this->odmMetadata = $this->manager->getClassMetadata($this->documentName);
        $this->class = $this->odmMetadata->getReflectionClass()->name;

        $mapping = $container->get('grid.mapping.manager');
        $mapping->addDriver($this, -1);
        $this->metadata = $mapping->getMetadata($this->class, $this->group);
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
            // For case insensitive
            case Column::OPERATOR_EQ:
            case Column::OPERATOR_LIKE:
            case Column::OPERATOR_NLIKE:
            case Column::OPERATOR_RLIKE:
            case Column::OPERATOR_LLIKE:
            case Column::OPERATOR_SLIKE:
            case Column::OPERATOR_NSLIKE:
            case Column::OPERATOR_RSLIKE:
            case Column::OPERATOR_LSLIKE:
            case Column::OPERATOR_NEQ:
                return 'equals';
            case Column::OPERATOR_ISNULL:
            case Column::OPERATOR_ISNOTNULL:
                return 'exists';
            default:
                return $operator;
        }
    }

    protected function normalizeValue($operator, $value)
    {
        switch ($operator) {
            case Column::OPERATOR_EQ:
                return new \MongoRegex('/^'.$value.'$/i');
            case Column::OPERATOR_NEQ:
                return new \MongoRegex('/^(?!'.$value.'$).*$/i');
            case Column::OPERATOR_LIKE:
                return new \MongoRegex('/'.$value.'/i');
            case Column::OPERATOR_NLIKE:
                return new \MongoRegex('/^((?!'.$value.').)*$/i');
            case Column::OPERATOR_RLIKE:
                return new \MongoRegex('/^'.$value.'/i');
            case Column::OPERATOR_LLIKE:
                return new \MongoRegex('/'.$value.'$/i');
            case Column::OPERATOR_SLIKE:
                return new \MongoRegex('/'.$value.'/');
            case Column::OPERATOR_SLIKE:
                return new \MongoRegex('/^((?!'.$value.').)*$/');
            case Column::OPERATOR_RSLIKE:
                return new \MongoRegex('/^'.$value.'/');
            case Column::OPERATOR_LSLIKE:
                return new \MongoRegex('/'.$value.'$/');
            case Column::OPERATOR_ISNULL:
                return false;
            case Column::OPERATOR_ISNOTNULL:
                return true;
            default:
                return $value;
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
        $this->query = $this->manager->createQueryBuilder($this->documentName);

        foreach ($columns as $column) {
            $this->query->select($column->getField());

            if ($column->isSorted()) {
                $this->query->sort($column->getField(), $column->getOrder());
            }

            if ($column->isPrimary()) {
                $column->setFilterable(false);
            } elseif ($column->isFiltered()) {
                // Some attributes of the column can be changed in this function
                $filters = $column->getFilters('document');

                foreach ($filters as $filter) {
                    //normalize values
                    $operator = $this->normalizeOperator($filter->getOperator());
                    $value = $this->normalizeValue($filter->getOperator(), $filter->getValue());

                    if ($column->getDataJunction() === Column::DATA_DISJUNCTION) {
                        $this->query->addOr($this->query->expr()->field($column->getField())->$operator($value));
                    } else {
                        $this->query->field($column->getField())->$operator($value);
                    }

                }
            }
        }

        if ($page > 0) {
            $this->query->skip($page * $limit);
        }

        if ($limit > 0) {
            if ($maxResults !== null && ($maxResults - $page * $limit < $limit)) {
                $limit = $maxResults - $page * $limit;
            }

            $this->query->limit($limit);
        } elseif ($maxResults !== null) {
            $this->query->limit($maxResults);
        }

        //call overridden prepareQuery or associated closure
        $this->prepareQuery($this->query);

        //execute and get results
        $result = new Rows();

        $cursor = $this->query->getQuery()->execute();

        $this->count = $cursor->count();

        foreach ($cursor as $resource) {
            $row = new Row();
            $properties = $this->getClassProperties($resource);

            foreach ($columns as $column) {
                if (isset($properties[$column->getId()])) {
                    $row->setField($column->getId(), $properties[$column->getId()]);
                }
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
        if ($maxResults !== null) {
            return min(array($maxResults, $this->count));
        }

        return $this->count;
    }

    protected function getClassProperties($obj)
    {
        $reflect = new \ReflectionClass($obj);
        $props   = $reflect->getProperties();
        $result  = array();

        foreach ($props as $property) {
            $property->setAccessible(true);
            $result[$property->getName()] = $property->getValue($obj);
        }

        return $result;
    }

    public function getFieldsMetadata($class, $group = 'default')
    {
        $result = array();
        foreach ($this->odmMetadata->getReflectionProperties() as $property) {
            $name = $property->getName();
            $mapping = $this->odmMetadata->getFieldMapping($name);
            $values = array('title' => $name, 'source' => true);

            if (isset($mapping['fieldName'])) {
                $values['field'] = $mapping['fieldName'];
                $values['id'] = $mapping['fieldName'];
            }

            if (isset($mapping['id']) && $mapping['id'] == 'id') {
                $values['primary'] = true;
            }

            switch ($mapping['type']) {
                case 'id':
                case 'string':
                case 'bin_custom':
                case 'bin_func':
                case 'bin_md5':
                case 'bin':
                case 'bin_uuid':
                case 'file':
                case 'key':
                case 'increment':
                    $values['type'] = 'text';
                    break;
                case 'int':
                case 'float':
                    $values['type'] = 'number';
                    break;
                /*case 'hash':
                    $values['type'] = 'array';*/
                case 'boolean':
                    $values['type'] = 'boolean';
                    break;
                case 'date':
                case 'timestamp':
                    $values['type'] = 'date';
                    break;
                case 'collection':
                    $values['type'] = 'array';
                    break;
                case 'one':
                    $values['type'] = 'array';
                    break;
                case 'many':
                    $values['type'] = 'array';
                    break;
                default:
                    $values['type'] = 'text';
            }

            $result[$name] = $values;
        }

        return $result;
    }

    public function populateSelectFilters($columns, $loop = false)
    {
        $queryFromSource = $this->manager->createQueryBuilder($this->documentName);
        $queryFromQuery = clone $this->query;

        // Clean the select fields from the query
        foreach ($columns as $column) {
            $queryFromQuery->exclude($column->getField());
        }

        /* @var $column Column */
        foreach ($columns as $column) {
            $selectFrom = $column->getSelectFrom();

            if ($column->getFilterType() === 'select' && ($selectFrom === 'source' || $selectFrom === 'query')) {

                // For negative operators, show all values
                if ($selectFrom === 'query') {
                    foreach ($column->getFilters('document') as $filter) {
                        if (in_array($filter->getOperator(), array(Column::OPERATOR_NEQ, Column::OPERATOR_NLIKE,Column::OPERATOR_NSLIKE))) {
                            $selectFrom = 'source';
                            break;
                        }
                    }
                }

                // Dynamic from query or not ?
                $query = ($selectFrom === 'source') ? clone $queryFromSource : clone $queryFromQuery;

                $result = $query->select($column->getField())
                    ->distinct($column->getField())
                    ->sort($column->getField(), 'asc')
                    ->skip(null)
                    ->limit(null)
                    ->getQuery()
                    ->execute();

                $values = array();
                foreach ($result as $value) {

                    switch ($column->getType()) {
                        case 'number':
                            $values[$value] = $column->getDisplayedValue($value);
                            break;
                        case 'datetime':
                        case 'date':
                        case 'time':
                            if ($value instanceof \MongoDate || $value instanceof \MongoTimestamp) {
                                $value = $value->sec;
                            }

                            // Mongodb bug ? timestamp value is on the key 'i' instead of the key 't'
                            if (is_array($value) && array_keys($value) == array('t','i')) {
                                $value = $value['i'];
                            }

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
                throw new \Exception(sprintf('No %s found for id %s', $this->documentName, $id));
            }

            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function getRepository()
    {
        return$this->manager->getRepository($this->documentName);
    }

    public function getHash()
    {
        return $this->documentName;
    }
}

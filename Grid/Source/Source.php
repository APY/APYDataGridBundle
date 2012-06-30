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

use APY\DataGridBundle\Grid\Mapping\Driver\DriverInterface;
use Symfony\Component\Form\Exception\PropertyAccessDeniedException;
use APY\DataGridBundle\Grid\Column;
use APY\DataGridBundle\Grid\Rows;
use APY\DataGridBundle\Grid\Row;

abstract class Source implements DriverInterface
{
    protected $prepareQueryCallback = null;
    protected $prepareRowCallback = null;
    protected $data = null;
    protected $items = array();

    /**
     * @param \Doctrine\ODM\MongoDB\Query\Builder $queryBuilder
     */
    public function prepareQuery($queryBuilder)
    {
        if (is_callable($this->prepareQueryCallback)) {
            call_user_func($this->prepareQueryCallback, $queryBuilder);
        }
    }

    /**
     * @param \APY\DataGridBundle\Grid\Row $row
     * @return \APY\DataGridBundle\Grid\Row|null
     */
    public function prepareRow($row)
    {
        if (is_callable($this->prepareRowCallback)) {
            return call_user_func($this->prepareRowCallback, $row);
        }

        return $row;
    }

    /**
     * @param \Closure $callback
     */
    public function manipulateQuery(\Closure $callback = null)
    {
        $this->prepareQueryCallback = $callback;

        return $this;
    }

    /**
     * @param \Closure $callback
     */
    public function manipulateRow(\Closure $callback = null)
    {
        $this->prepareRowCallback = $callback;

        return $this;
    }

    /**
     * Find data for current page
     *
     * @abstract
     * @param \APY\DataGridBundle\Grid\Column\Column[] $columns
     * @param int $page
     * @param int $limit
     * @return \APY\DataGridBundle\DataGrid\Rows
     */
    abstract public function execute($columns, $page = 0, $limit = 0, $maxResults = null);

    /**
     * Get Total count of data items
     *
     * @param int $maxResults
     * @return int
     */
    abstract public function getTotalCount($maxResults = null);

    /**
     * Set container
     *
     * @abstract
     * @param  $container
     * @return void
     */
    abstract public function initialise($container);

    /**
     * @abstract
     * @param $columns
     */
    abstract public function getColumns($columns);

    public function getClassColumns($class, $group = 'default')
    {
        return array();
    }

    public function getFieldsMetadata($class, $group = 'default')
    {
        return array();
    }

    public function getGroupBy($class, $group = 'default')
    {
        return array();
    }

    abstract public function populateSelectFilters($columns, $loop = false);

    /**
    * Return source hash string
    * @abstract
    */
    abstract public function getHash();

    /**
     * Delete one or more objects
     *
     * @abstract
     * @param array $ids
     * @return void
     */
    abstract public function delete(array $ids);

    /**
     * Use data instead of fetching the source
     *
     * @param array|object $data
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the loaded data
     *
     * @return array|object
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Check if data is loaded
     *
     * @return boolean
     */
    public function isDataLoaded()
    {
        return $this->data !== null;
    }

    /**
     * Find data from array|object
     *
     * @param \APY\DataGridBundle\Grid\Column\Column[] $columns
     * @param int $page
     * @param int $limit
     * @return \APY\DataGridBundle\DataGrid\Rows
     */
    public function executeFromData($columns, $page = 0, $limit = 0, $maxResults = null)
    {
        // Populate from data
        $items = array();
        $serializeColumns = array();

        foreach ($this->data as $key => $item) {
            $keep = true;

            foreach ($columns as $column) {
                $fieldName = $column->getField();

                if ($this instanceof Entity) {
                    // Mapped field
                    $itemEntity = $item;
                    if (strpos($fieldName, '.') === false) {
                        $functionName = ucfirst($fieldName);
                    } else {
                        // loop through all elements until we find the final entity and the name of the value for which we are looking
                        $elements = explode('.', $fieldName);
                        while ($element = array_shift($elements)) {
                            if (count($elements) > 0) {
                                $itemEntity = call_user_func(array($itemEntity, 'get'.$element));
                            } else {
                                $functionName = ucfirst($element);
                            }
                        }
                    }

                    // Get value of the column
                    if (isset($itemEntity->$fieldName)) {
                        $fieldValue = $itemEntity->$fieldName;
                    } elseif (is_callable(array($itemEntity, $fullFunctionName = 'get'.$functionName))
                           || is_callable(array($itemEntity, $fullFunctionName = 'has'.$functionName))
                           || is_callable(array($itemEntity, $fullFunctionName = 'is'.$functionName))) {
                        $fieldValue = call_user_func(array($itemEntity, $fullFunctionName));
                    } else {
                        throw new PropertyAccessDeniedException(sprintf('Property "%s" is not public or has no accessor.', $fieldName));
                    }
                } else {
                    $fieldValue = $item[$fieldName];
                }


                $items[$key][$fieldName] = $fieldValue;

                // Filter
                if ($column->isFiltered()) {
                    // Some attributes of the column can be changed in this function
                    $filters = $column->getFilters('vector');

                    if ($column->getDataJunction() === Column\Column::DATA_DISJUNCTION) {
                        $disjunction = true;
                        $keep = false;
                    } else {
                        $disjunction = false;
                        $keep = true;
                    }

                    $found = false;
                    foreach ($filters as $filter) {
                        $operator = $filter->getOperator();
                        $value = $filter->getValue();

                        // Normalize value
                        switch ($operator) {
                            case Column\Column::OPERATOR_EQ:
                                $value = "/^$value$/i";
                                break;
                            case Column\Column::OPERATOR_NEQ:
                                $value = "/^(?!$value$).*$/i";
                                break;
                            case Column\Column::OPERATOR_LIKE:
                                $value = "/$value/i";;
                                break;
                            case Column\Column::OPERATOR_NLIKE:
                                $value = "/^((?!$value).)*$/i";
                                break;
                            case Column\Column::OPERATOR_LLIKE:
                                $value = "/$value$/i";
                                break;
                            case Column\Column::OPERATOR_RLIKE:
                                $value = "/^$value/i";
                                break;
                        }

                        // Test
                        switch ($operator) {
                            case Column\Column::OPERATOR_EQ:
                            case Column\Column::OPERATOR_NEQ:
                            case Column\Column::OPERATOR_LIKE:
                            case Column\Column::OPERATOR_NLIKE:
                            case Column\Column::OPERATOR_LLIKE:
                            case Column\Column::OPERATOR_RLIKE:
                                if ($column->getType() === 'array') {
                                    $fieldValue = str_replace(':{i:0;', ':{', serialize($fieldValue));
                                }

                                $found = preg_match($value, $fieldValue);
                                break;
                            case Column\Column::OPERATOR_GT:
                                $found = $fieldValue > $value;
                                break;
                            case Column\Column::OPERATOR_GTE:
                                $found = $fieldValue >= $value;
                                break;
                            case Column\Column::OPERATOR_LT:
                                $found = $fieldValue < $value;
                                break;
                            case Column\Column::OPERATOR_LTE:
                                $found = $fieldValue <= $value;
                                break;
                            case Column\Column::OPERATOR_ISNULL:
                                $found = $fieldValue === null;
                                break;
                            case Column\Column::OPERATOR_ISNOTNULL:
                                $found = $fieldValue !== null;
                                break;
                        }

                        // AND
                        if (!$found && !$disjunction) {
                            $keep = false;
                            break 2;
                        }

                        // OR
                        if ($found && $disjunction) {
                            $keep = true;
                            break 2;
                        }
                    }
                }

                if ($column->getType() === 'array') {
                    $serializeColumns[] = $column->getId();
                }
            }

            if (!$keep) {
                unset($items[$key]);
            }
        }

        // Order
        foreach ($columns as $column) {
            if ($column->isSorted()) {
                $sortTypes = array();
                $sortedItems = array();
                foreach ($items as $key => $item) {
                    $value = $item[$column->getField()];

                    // Format values for sorting and define the type of sort
                    switch ($column->getType()) {
                        case 'text':
                            $sortedItems[$key] = strtolower($value);
                            $sortType = SORT_STRING;
                            break;
                        case 'datetime':
                        case 'date':
                        case 'time':
                            if ($value instanceof \DateTime) {
                                $sortedItems[$key] = $value->getTimestamp();
                            } else {
                                $sortedItems[$key] = strtotime($value);
                            }
                            $sortType = SORT_NUMERIC;
                            break;
                        case 'boolean':
                            $sortedItems[$key] = $value ? 1 : 0;
                            $sortType = SORT_NUMERIC;
                            break;
                        case 'array':
                            $sortedItems[$key] = json_encode($value);
                            $sortType = SORT_STRING;
                            break;
                        case 'number':
                            $sortedItems[$key] = $value;
                            $sortType = SORT_NUMERIC;
                            break;
                        default:
                            $sortedItems[$key] = $value;
                            $sortType = SORT_REGULAR;
                    }
                }

                array_multisort($sortedItems, ($column->getOrder() == 'asc') ? SORT_ASC : SORT_DESC, $sortType, $items);
                break;
            }
        }

        // Pagination
        if ($limit > 0) {
            $maxResults = ($maxResults !== null && ($maxResults - $page * $limit < $limit)) ? $maxResults - $page * $limit : $limit;

            $items = array_slice($items, $page * $limit, $maxResults);
        } elseif ($maxResults !== null) {
            $items = array_slice($items, 0, $maxResults);
        }

        $rows = new Rows();
        foreach ($items as $item) {
            $row = new Row();

            if ($this instanceof Vector) {
                $row->setPrimaryField($this->id);
            }

            foreach ($item as $fieldName => $fieldValue) {
                if ($this instanceof Entity) {
                    if (in_array($fieldName, $serializeColumns)) {
                        if (is_string($fieldValue)) {
                            $fieldValue = unserialize($fieldValue);
                        }
                    }
                }

                $row->setField($fieldName, $fieldValue);
            }

            //call overridden prepareRow or associated closure
            if (($modifiedRow = $this->prepareRow($row)) != null) {
                $rows->addRow($modifiedRow);
            }
        }

        $this->items = $items;

        return $rows;
    }

    public function populateSelectFiltersFromData($columns, $loop = false)
    {
        /* @var $column Column */
        foreach ($columns as $column) {
            $selectFrom = $column->getSelectFrom();

            if ($column->getFilterType() === 'select' && ($selectFrom === 'source' || $selectFrom === 'query')) {

                // For negative operators, show all values
                if ($selectFrom === 'query') {
                    foreach($column->getFilters('vector') as $filter) {
                        if (in_array($filter->getOperator(), array(Column\Column::OPERATOR_NEQ, Column\Column::OPERATOR_NLIKE))) {
                            $selectFrom = 'source';
                            break;
                        }
                    }
                }

                // Dynamic from query or not ?
                $item = ($selectFrom === 'source') ? $this->data : $this->items;

                $values = array();
                foreach($item as $row) {
                    $value = $row[$column->getField()];

                    switch ($column->getType()) {
                        case 'number':
                        case 'datetime':
                        case 'date':
                        case 'time':
                            // For document
                            if ($value instanceof \MongoDate || $value instanceof \MongoTimestamp) {
                                $value = $value->sec;
                            }

                            // Mongodb bug ? timestamp value is on the key 'i' instead of the key 't'
                            if (is_array($value) && array_keys($value) == array('t','i')) {
                                $value = $value['i'];
                            }

                            $values[$value] = $column->getDisplayedValue($value);
                            break;
                        case 'array':
                            if (is_string($value)) {
                                $value = unserialize($value);
                            }
                            foreach ($value as $val) {
                                $values[$val] = $val;
                            }
                            break;
                        default:
                            $values[$value] = $value;
                    }
                }

                // It avoids to have no result when the other columns are filtered
                if ($selectFrom === 'query' && empty($values) && $loop === false) {
                    $column->setSelectFrom('source');
                    $this->populateSelectFiltersFromData($columns, true);
                } else {
                    if ($column->getType() == 'array') {
                        natcasesort($values);
                    }
                    $column->setValues(array_unique($values));
                }
            }
        }
    }

    /**
     * Get Total count of data items
     *
     * @return int
     */
    public function getTotalCountFromData($maxResults = null)
    {
        if ($maxResults !== null) {
            return min(array($maxResults, count($this->data)));
        }

        return count($this->data);
    }
}

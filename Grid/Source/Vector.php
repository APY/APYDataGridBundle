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

/**
 * Vector is really an Array
 * @author dellamowica
 */
class Vector extends Source 
{
    /**
     * @var string e.g Cms:Page
     */
    private $entityName = 'Vector';

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var array
     */
    private $ormMetadata;

    /**
     *
     * @var array
     */
    private $data;
    
    /**
     * List of column names to display
     * @var array
     */
    private $whiteList = array();

    /**
     * List of column names to hide
     * @var array
     */
    private $blackList = array();
    /**
     *
     * @var mixed
     */
    private $id = null;

    const TABLE_ALIAS = '_a';
    const COUNT_ALIAS = '__count';

    /**
     * @param array $array e.g Cms:Page
     */
    public function __construct(array $array) 
    {
        $this->setData($array);
    }

    public function initialise($container) 
    {
        $this->ormMetadata = array_keys(reset($this->data));
        $this->metadata = $this->getFieldsMetadata();
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Columns $columns
     * @return null
     */
    public function getColumns($columns) 
    {
        if(empty($this->whiteList)&&empty($this->blackList)){
            $whiteList = $this->ormMetadata;
        } elseif(empty($this->whiteList) && !empty ($this->blackList)) {
            $whiteList = array_diff($this->ormMetadata, $this->blackList);
        } elseif (!empty($this->whiteList) && empty ($this->blackList)) {
            $whiteList = array_intersect($this->ormMetadata, $this->whiteList);
        } else {
            $whiteList = array_intersect($this->ormMetadata, $this->whiteList);
            $whiteList = array_diff($whiteList, $this->blackList);
        }
        
        $token = empty($this->id);
        foreach ($this->ormMetadata as $column) {
            $columns->addColumn(new \Sorien\DataGridBundle\Grid\Column\TextColumn(array(
                        'id' => $column,
                        'title' => $column,
                        'primary' => ($column == $this->id) || $token,
                        'source' => true,
                        'filterable' => true,
                        'sortable' => true,
                        'visible' => in_array($column, $whiteList),
                        'field' => $column,
                    )));
            $token = false;
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
        $items = $this->data;
        // Order
        foreach ($columns as $column) {
            if ($column->isSorted()) {
                $sortedItems = array();
                foreach ($items as $key => $item) {
                    $sortedItems[$key] = $item[$column->getField()];
                }
                // Type ? (gettype function)
                array_multisort($sortedItems, ($column->getOrder() == 'asc') ? SORT_ASC : SORT_DESC, SORT_STRING, $items);
            }
            if ($column->isFiltered()) {
                $filter = $column->getFilters();

                $filter = $filter[0];
                $filter = $filter->getValue();

                $filter = trim(str_replace('%', '*', $filter), "'");
                foreach ($items as $key => $item) {
                    if (!preg_match($filter, $item[$column->getField()])) {
                        unset($items[$key]);
                    }
                }
            }
        }
        $this->data = $items;
        $data = array_slice($items, $page * $limit, $limit);
        $rows = new \Sorien\DataGridBundle\Grid\Rows();
        foreach ($data as $item) {
            $row = new \Sorien\DataGridBundle\Grid\Row();
            foreach ($item as $key => $value) {
                $row->setField($key, $value);
            }
            $rows->addRow($row);
        }
        return $rows;
    }

    public function getTotalCount($columns) 
    {
        return count($this->data);
    }

    public function getFieldsMetadata($class = '') 
    {
        $result = array();
        foreach ($this->ormMetadata as $name) {
            $values = array(
                'title' => $name,
                'source' => true,
                'field' => $name,
                'id' => $name,
                'type' => 'string',
            );
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
        foreach ($ids as $id) {
            unset($this->data[$id]);
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Set a two-dimentional array
     * @param array $data 
     * @throws \InvalidArgumentException 
     */
    public function setData(array $data){
        $this->data = $data;
        if(!is_array($this->data) || empty($this->data)){
            throw new \InvalidArgumentException('Data should be an array with content');
        }
        if (is_object(reset($this->data))) {
            foreach ($this->data as $key => $object) {
                $this->data[$key] = (array) $object;
            }
        }
        $firstRaw = reset($this->data);
        if(!is_array($firstRaw) || empty($firstRaw)){
            throw new \InvalidArgumentException('Data should be a two-dimentional array');
        }
    }
    
    /**
     * Set a list of columns to display
     * @param array $whitelist 
     */
    public function setWhiteList(array $whiteList){
        $this->whiteList = $whiteList;
    }
    
    /**
     * Set a list of columns to hide
     * @param array $whitelist 
     */
    public function setBlackList(array $blackList){
        $this->blackList = $blackList;
    }
}
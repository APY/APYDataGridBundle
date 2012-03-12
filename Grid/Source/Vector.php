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
     * @var string e.g Vendor\Bundle\Entity\Page
     */
    private $class = 'Vector';

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
     * @var array
     */
    private $joins = array();

    /**
     *
     * @var array
     */
    private $data;

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
        $this->data = $array;
    }

    public function initialise($container) 
    {
        if (is_object($this->data[0])) {
            foreach ($this->data as $key => $object) {
                $this->data[$key] = (array) $object;
            }
        }
        $this->ormMetadata = array_keys($this->data[0]);
        $this->metadata = $this->getFieldsMetadata();
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Columns $columns
     * @return null
     */
    public function getColumns($columns) 
    {
        $token = empty($this->id);
        foreach ($this->ormMetadata as $column) {
            $columns->addColumn(new \Sorien\DataGridBundle\Grid\Column\TextColumn(array(
                        'id' => $column,
                        'title' => $column,
                        'primary' => ($column == $this->id) || $token,
                        'source' => true,
                        'filterable' => true,
                        'sortable' => true,
                        'visible' => true,
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
}
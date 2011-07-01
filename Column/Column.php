<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Column;

class Column
{
    private $id;
    private $title;
    private $sortable;
    private $isSorted;
    private $filterable;
    private $visible;
    private $callback;
    private $order;
    private $size;
    private $orderUrl;
    private $visibleForSource;

    protected $data;

    const DATA_CONJUNCTION = 0;
    const DATA_DISJUNCTION = 1;

    const OPERATOR_EQ   = 'eq';
    const OPERATOR_NEQ  = 'neq';
    const OPERATOR_LT   = 'lt';
    const OPERATOR_LTE  = 'lte';
    const OPERATOR_GT   = 'gt';
    const OPERATOR_GTE  = 'gte';
    const OPERATOR_LIKE = 'like';

    /**
     * Default Column constructor
     *
     * @param string|int $id
     * @param string $title
     * @param int $size
     * @param bool $sortable
     * @param bool $filterable
     * @param bool $visible
     * @return Column
     */
    public function __construct($id, $title = '', $size = 0, $sortable = true, $filterable = true, $visible = true)
    {
        $this->id = $id;
        $this->title = $title;
        $this->sortable = $sortable;
        $this->visible = $visible;
        $this->size = $size;
        $this->filterable = $filterable;
        $this->isSorted = false;
        $this->order = '';
        $this->visibleForSource = true;
    }

    /**
     * Draw filter
     *
     * @param string $gridHash
     * @return string
     */
    public function renderFilter($gridHash)
    {
        return '';
    }

    /**
     * Draw cell
     *
     * @param string $value
     * @param Row $row
     * @param $router
     * @param $primaryColumnValue
     * @return string
     */
    public function renderCell($value, $row, $router, $primaryColumnValue)
    {
        if (is_callable($this->callback))
        {
            return call_user_func($this->callback, $value, $row, $router, $primaryColumnValue);
        }
        else
        {
            return $value;
        }
    }

    /**
     * @param  $callback
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * set column identifier
     *
     * @param $id
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * get column identifier
     *
     * @return int|string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * set column title
     *
     * @param string $title
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * get column title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * show column
     *
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function show()
    {
        $this->visible = true;

        return $this;
    }

    /**
     * hide column
     *
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function hide()
    {
        $this->visible = false;

        return $this;
    }

    /**
     * column visibility
     *
     * @return bool return true when column is visible
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * column sorted
     *
     * @return bool return true when column is sorted
     */
    public function isSorted()
    {
        return $this->isSorted;
    }

    /**
     * column is filtred
     *
     * @return bool return true when column is filtred
     */
    public function isFiltred()
    {
        return $this->data != null;
    }

    /**
     * column ability to filter
     *
     * @return bool return true when column can be filtred
     */
    public function isFilterable()
    {
        return $this->filterable;
    }

    /**
     * column ability to sort
     *
     * @return bool return true when column can be sorted
     */
    public function isSortable()
    {
        return $this->sortable;
    }

    /**
     * set column order
     *
     * @param string $order asc|desc
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function setOrder($order)
    {
        $this->order = $order;
        $this->isSorted = true;

        return $this;
    }

    /**
     * get column order
     *
     * @return string asc|desc
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * get data filter connection (how column filters are connected with column data)
     *
     * @return bool column::DATA_CONJUNCTION | column::DATA_DISJUNCTION
     */
    public function getFiltersConnection()
    {
        return self::DATA_CONJUNCTION;
    }

    /**
     * get column data filters
     * todo: maybe change to own class not array
     *
     * @return \Sorien\DataGridBundle\DataGrid\Filter[]
     */
    public function getFilters()
    {
        return array();
    }

    /**
     * set column width
     *
     * @param int $size in pixels
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * get column width
     *
     * @return int column width in pixels
     */
    public function getSize()
    {
        return $this->size;
    }


    public function setOrderUrl($url)
    {
        $this->orderUrl = $url;

        return $this;
    }

    public function getOrderUrl()
    {
        return $this->orderUrl;
    }

    /**
     * set filter data from session | request
     *
     * @param  $data
     * @return \Sorien\DataGridBundle\Column\Column
     */
    public function setData($data)
    {
        if (isset($data) && $data != '')
        {
            $this->data = $data;
        }

        return $this;
    }

    /**
     * get filter data from session | request
     *
     * @return array data
     */
    public function getData()
    {
        if ($this->isFiltred())
        {
            return $this->data;
        }

        return null;
    }

    public function setIsVisibleForSource($value)
    {
        $this->visibleForSource = $value;
    }

    public function isVisibleForSource()
    {
        return $this->visibleForSource;
    }
}
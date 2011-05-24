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

abstract class Column
{
    private $id;
    private $title;
    private $sortable;
    private $isSorted;
    private $filterable;
    private $visible;
    private $callback;
    private $order;
    private $filters;
    private $size;
    private $orderUrl;
    private $filterDrawCache;
    private $filterData;

    const DATA_CONJUNCTION = 0;
    const DATA_DISJUNCTION = 1;

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
    }

    /**
     * Draw filter
     *
     * @abstract
     * @param string $gridId
     * @return string
     */
    abstract public function renderFilter($gridId);

    public final function prepareFilter($gridId)
    {
        $this->filterDrawCache = $this->renderFilter($gridId);
    }

    public final function getFilter()
    {
        return $this->filterDrawCache;
    }

    /**
     * Draw cell
     *
     * @param string $value
     * @param Row $row
     * @param $router
     * @return string
     */
    public function renderCell($value, $row, $router)
    {
        if (is_callable($this->callback))
        {
            return call_user_func($this->callback, $value, $row, $router);
        }
        else
        {
            return $value;
        }
    }

    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function show()
    {
        $this->visible = true;
        return $this;
    }

    public function hide()
    {
        $this->visible = false;
        return $this;
    }

    public function isVisible()
    {
        return $this->visible;
    }

    public function isSorted()
    {
        return $this->isSorted;
    }

    public function isFiltred()
    {
        return $this->filterData != null;
    }

    public function isFilterable()
    {
        return $this->filterable;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function setOrder($order)
    {
        $this->order = $order;
        $this->isSorted = true;
        return $this;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getDataFiltersConnection()
    {
        return self::DATA_CONJUNCTION;
    }

    public function getDataFilters()
    {
        return array();
    }

    public function setFilterData($data)
    {
        $this->filterData = $data;
    }

    public function getFilterData()
    {
        return $this->filterData;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setOrderUrl($url)
    {
        $this->orderUrl = $url;
    }

    public function getOrderUrl()
    {
        return $this->orderUrl;
    }

    public static function nextOrder($value)
    {
        return  $value == 'asc' ? 'desc' : 'asc';
    }
}
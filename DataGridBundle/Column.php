<?php

namespace Sorien\DataGridBundle;

class Column
{
	private $id;
	private	$title;
	private $sortable;
	private $isSorted;
	private $filterable;
	private $visible;
	private $callback;
	private $order;
	private $filtersConnected;
	private $filters;
	private $filterData;
	private $size;
	/*
	 * Draws filter box for column
	 */
	public function __construct($id, $title = '', $size = null, $sortable = true, $filterable = true, $visible = true)
	{
		$this->id = $id;
		$this->title = $title;
		$this->sortable = $sortable;
		$this->visible = $visible;
		$this->size = $size;
		$this->filterable = $filterable;
		$this->isSorted = false;
	}

	public function drawFilter($gridId)
	{
		return '';
	}

	public function drawCell($value, $row)
	{
		if ($this->callback != null)
		{
			//call callback
		}
		else
		{
			return $value;
		}
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
		return true;
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

	public function setCallback($callback)
	{
		$this->callback = $callback;
	}

	public function getCallback()
	{
		return $this->callback;
	}

	public function filtersConnected()
	{
		return $this->filtersConnected;
	}

	public function getFilters()
	{
		return $this->filters;
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
}


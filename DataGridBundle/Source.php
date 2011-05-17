<?php
namespace Sorien\DataGridBundle;

use Sorien\DataGridBundle\Column;

abstract class Source
{
	private $columns;
	private $count;
	private $totalCount;
	private $page;

	public function __construct()
	{
		$this->columns = new \SplObjectStorage();
	}
	
	function addColumn($column)
	{
		$this->columns->attach($column);
		return $this;
	}

	abstract public function prepare();

	abstract public function execute();

	/**
	 * @return Column[] 
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	public function setCount($count)
	{
		$this->count = $count;
	}

	public function getCount()
	{
		return $this->count;
	}

	public function setPage($page)
	{
		$this->page = $page;
	}

	public function getPage()
	{
		return $this->page;
	}

	public function setTotalCount($totalCount)
	{
		$this->totalCount = $totalCount;
	}

	public function getTotalCount()
	{
		return $this->totalCount;
	}

	/**
	 * @return Array
	 */
	public function getRow()
	{
		return Array();
	}

}





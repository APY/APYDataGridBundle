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

	abstract public function prepare();

	abstract public function execute();

	function addColumn($column)
	{
		$this->columns->attach($column);
		return $this;
	}

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
		return $this;
	}

	public function getCount()
	{
		return $this->count;
	}

	public function setPage($page)
	{
		$this->page = $page;
		return $this;
	}

	public function getPage()
	{
		return $this->page;
	}

	public function setTotalCount($totalCount)
	{
		$this->totalCount = $totalCount;
		return $this;		
	}

	public function getTotalCount()
	{
		return $this->totalCount;
	}
}





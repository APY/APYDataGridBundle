<?php

namespace Sorien\DataGridBundle;

use Sorien\DataGridBundle\Source;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class Grid
{
	private $session;
	/**
	* @var Request
	*/
	private $request;
	private $url;
	private $id;

	/**
	* @var Source
	*/
	private $source;
	private $totalRows;
	private $page;
	private $data;


	public function __construct($source, $controller, $route = null, $id = '')
	{
		$this->source = $source;

		$this->session = $controller->get('request')->getSession();
		$this->request = $controller->get('request');

		$this->url = (!is_null($route)) ? $controller->get('router')->generate($route) : '';

		$name = explode('::', $controller->get('request')->get('_controller'));
		$this->id = md5($name[0].$id);

		//get cols from source
		$this->source->prepare();
		$saveData = array();

		if (is_array($grid = $this->session->get('grid_'.$this->id)))
		{
			//set orders from session [grid_717127575fasdf1as7dfa1sf][a.author_id][orders][asc]
			foreach ($this->source->getColumns() as $column)
			{
				if (isset($grid[$column->getId()]) && is_array($grid[$column->getId()]) )
				{
					//set orders
					if (isset($grid[$column->getId()]['order']))
					{
						$column->setOrder($grid[$column->getId()]['order']);
					}

					//set filters
					if (isset($grid[$column->getId()]['filter']))
					{
//						var_dump('session:'. $grid[$column->getId()]['filter']);
						$column->setFilterData($grid[$column->getId()]['filter']);
					}
				}
			}
		}

		//set order form get
		if (is_array($orders = $this->request->query->get('grid_'.$this->id)))
		{
			//$saveOrders = array();

			foreach ($this->source->getColumns() as $column)
			{
				if (isset($orders[$column->getId()]))
				{
					$column->setOrder($orders[$column->getId()]['order']);
					$saveData[$column->getId()]['order'] = $column->getOrder();

					if ($column->isFiltred())
					{
						$saveData[$column->getId()]['filter'] = $column->getFilterData();
					}

				}
			}

			//if (!empty($saveOrders)) $saveData['order'] = $saveOrders;
		}

		//set filter from post
		if (is_array($filters = $this->request->request->get('grid_'.$this->id)))
		{
			//$saveFilters = array();
			foreach ($this->source->getColumns() as $column)
			{
				if (isset($filters[$column->getId()]))
				{
					$column->setFilterData($filters[$column->getId()]['filter']);
					$saveData[$column->getId()]['filter'] = $column->getFilterData();

					if ($column->isSorted())
					{
						$saveData[$column->getId()]['order'] = $column->getOrder();
					}
				}
			}

			//if (!empty($saveFilters)) $saveData['filter'] = $saveFilters;
		}

		// if we need save sessions
		if (!empty($saveData))
		{
			$this->session->set('grid_'.$this->id, $saveData);
		}

	}

	private function negateOrder($value)
	{
		return  $value == 'asc' ? 'desc' : 'asc';
	}

	/**
	 * get data form Source Object
	 * @return void
	 */
	public function prepare()
	{
		//get titles/orders/filters
		$this->data['columns'] = $this->data['items'] = array();
		$_filter = $_title = false;

		foreach ($this->source->getColumns() as $column)
		{
			if ($column->isVisible())
			{
				$filter  = $column->isFilterable() ? $column->drawFilter('grid_'.$this->id) : '';

				if ($column->isSorted())
				{
					$order = $this->url.'?grid_'.$this->id.'['.$column->getId().'][order]='.$this->negateOrder($column->getOrder());
				}
				else
				{
					$order = $this->url.'?grid_'.$this->id.'['.$column->getId().'][order]=asc';
				}

				if (!$_filter && $column->isFilterable()) $_filter = true;
				if (!$_title && $column->getTitle() != '') $_title = true;

				$this->data['columns'][] = array(
					'title' => $column->getTitle(),
					'order' => array('type' => (string)$column->getOrder(), 'url' => $order),
					'width' => (int)$column->getSize(),
					'filter' => $filter
				);
			}
		}

		//get data
		foreach ($this->source->execute() as $row)
		{
			$item = array();

			foreach ($this->source->getColumns() as $column)
			{
				if ($column->isVisible())
				{
					$item[] = $column->drawCell($row[$column->getId()], $row);
				}
			}

			$this->data['items'][] = $item;
		}

		$this->data['show_filters'] = $_filter;
		$this->data['show_titles'] = $_title;

		//get size
		$this->totalRows = $this->source->getTotalCount();
	}

	public function getData()
	{
		if (empty($this->data))
		{
			$this->prepare();
		}

		//draw template
		return array(
			'show_filters' => $this->data['show_filters'],
			'show_titles'  => $this->data['show_titles'],
			'columns'  	   => $this->data['columns'],
			'items'        => $this->data['items'],
			'url'	       => $this->url
		);
	}

}

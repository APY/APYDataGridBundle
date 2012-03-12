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
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr\Comparison;

/*
 * Vector is really an Array
 */
class Vector extends Source
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $manager;

    /**
     * @var string e.g Vendor\Bundle\Entity\Page
     */
    private $class;

    /**
     * @var string e.g Cms:Page
     */
    private $entityName;

    /**
     * @var \Sorien\DataGridBundle\Grid\Mapping\Metadata\Metadata
     */
    private $metadata;

    /**
     * @var \Doctrine\ORM\Mapping\ClassMetadata
     */
    private $ormMetadata;

    /**
     * @var array
     */
    private $joins;
	
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
        $this->entityName = 'Vector';
        $this->joins = array();
		$this->data = $array;
    }

    public function initialise($container)
    {
        $this->manager = $container->get('doctrine')->getEntityManager();
		if(is_object($this->data[0])){
			foreach ($this->data as $key => $object) {
				$this->data[$key] = (array) $object;
			}
		}
		$this->ormMetadata = array_keys($this->data[0]);

        $this->class = 'Vector';
        $this->metadata = $this->getFieldsMetadata();
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Columns $columns
     * @return null
     */
    public function getColumns($columns)
    {
		$token = empty($this->id);
        foreach ($this->ormMetadata as $column)
        {
            $columns->addColumn(new \Sorien\DataGridBundle\Grid\Column\TextColumn(array(
				'id'=>$column,
				'title'=>$column,
				'primary'=>($column==$this->id)||$token,
				'source'=>true,
				'filterable'=>true,
				'sortable'=>true,
				'visible'=>true,
				'field'=>$column,
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
        foreach ($columns as $column)
        {
            if ($column->isSorted())
            {
                $sortedItems = array();
                foreach ($items as $key => $item)
                {
					$sortedItems[$key] = $item[$column->getField()];
                }
                // Type ? (gettype function)
                array_multisort($sortedItems, ($column->getOrder() == 'asc') ? SORT_ASC : SORT_DESC, SORT_STRING, $items);
            }
			if ($column->isFiltered())
            {
				$filter = $column->getFilters();
				
				$filter = $filter[0];
				$filter = $filter->getValue();
				
				$filter = trim(str_replace('%', '*', $filter),"'");
                foreach ($items as $key => $item)
                {
					if(!preg_match($filter, $item[$column->getField()])){
						unset($items[$key]);
					}
                }
            }
        }
		$this->data = $items;
		$data = array_slice($items, $page*$limit, $limit);
		$rows = new \Sorien\DataGridBundle\Grid\Rows();
        foreach ($data as $row_id => $item){
			$row = new \Sorien\DataGridBundle\Grid\Row();
			foreach ($item as $key => $value)
            {
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
        foreach ($this->ormMetadata as $name)
        {
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
        $repository = $this->manager->getRepository($this->entityName);

        foreach ($ids as $id) {
            $object = $repository->find($id);

            if (!$object) {
                throw new \Exception(sprintf('No %s found for id %s', $this->entityName, $id));
            }

            $this->manager->remove($object);
        }

        $this->manager->flush();
    }
	
	public function setId($id){
		$this->id = $id;
	}
}

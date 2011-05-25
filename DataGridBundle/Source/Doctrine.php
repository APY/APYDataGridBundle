<?php

namespace Sorien\DataGridBundle\Source;

use Sorien\DataGridBundle\Source\Source;
use Sorien\DataGridBundle\Column\Range;
use Sorien\DataGridBundle\Column\Text;
use Sorien\DataGridBundle\Column\Column;
use Sorien\DataGridBundle\DataGrid\Rows;
use Doctrine\ORM\Query\Expr\Orx;

class Doctrine extends Source
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $entityName;
    private $columnMappings;
    private $table;
    private $tablePrefix;

    public function __construct($entityName)
    {
        $this->entityName = $entityName;
        $this->tablePrefix = 'a';
    }

    public function initialize($container)
    {
        $this->entityManager = $container->get('doctrine')->getEntityManager();
        $metadata = $this->entityManager->getClassMetadata($this->entityName);

        foreach ($metadata->getColumnNames() as $value)
        {
            $this->columnMappings[] = $metadata->getFieldMapping($value);
        }

        $this->table = $metadata->getReflectionClass()->name;//$metadata->getTableName();
    }

    public function getPrefixedName($name)
    {
        return $this->tablePrefix.'.'.$name;
    }

    /**
     * @param $columns \Sorien\DataGridBundle\DataGrid\Columns
     * @param $actions
     * @return null
     */
    public function prepare($columns, $actions)
    {
        foreach ($this->columnMappings as $columnMappingData)
        {
            switch ($columnMappingData['type'])
            {
                case 'integer':
                    $columns->addColumn(new Range($columnMappingData['fieldName'], $columnMappingData['fieldName'], 100));
                break;

                case 'string':
                    $columns->addColumn(new Text($columnMappingData['fieldName'], $columnMappingData['fieldName'], 100));
                break;
            }
        }
    }

    /**
     * @param $columns \Sorien\DataGridBundle\Column\Column[]
     * @param $page int Page Number
     * @param $limit int Rows Per Page
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    public function execute($columns, $page, $limit)
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->from($this->table, $this->tablePrefix);

        if ($page > 0)
        {
            $query->setFirstResult($page * $limit);
        }

        $query->setMaxResults($limit);
        $where = $query->expr()->andx();

        foreach ($columns as $column)
        {
            $query->addSelect($this->getPrefixedName($column->getId()));

            if ($column->isSorted())
            {
                $query->orderBy($this->getPrefixedName($column->getId()), $column->getOrder());
            }

            if ($column->isFiltred())
            {
                if($column->getDataFiltersConnection() == column::DATA_CONJUNCTION)
                {
                    foreach ($column->getDataFilters() as $filter)
                    {
                        $operator = $filter->getOperator();
                        $where->add($query->expr()->$operator($this->getPrefixedName($column->getId()), $filter->getValue()));
                    }
                }
                elseif($column->getDataFiltersConnection() == column::DATA_DISJUNCTION)
                {
                    $sub = $query->expr()->orx();
                    foreach ($column->getDataFilters() as $filter)
                    {
                        $operator = $filter->getOperator();
                        $sub->add($query->expr()->$operator($this->getPrefixedName($column->getId()), $filter->getValue()));
                    }
                    $where->add($sub);
                }
                $query->where($where);
            }
        }
        return new Rows($query->getQuery()->getResult());
    }
}
<?php

namespace Sorien\DataGridBundle\Source;

use Sorien\DataGridBundle\Column\Range;
use Sorien\DataGridBundle\Column\Text;
use Sorien\DataGridBundle\Column\Column;
use Sorien\DataGridBundle\Source\Source;
use Sorien\DataGridBundle\DataGrid\Row;
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

    public function execute($columns, $page)
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->from($this->table, $this->tablePrefix);

        $query->setFirstResult(1);
        $query->setMaxResults(20);

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
                        $operator = $filter['operator'];
                        $where->add($query->expr()->$operator($this->getPrefixedName($column->getId()), $filter['value']));
                    }
                }
                elseif($column->getDataFiltersConnection() == column::DATA_DISJUNCTION)
                {
                    $sub = $query->expr()->orx();
                    foreach ($column->getDataFilters() as $filter)
                    {
                        $operator = $filter['operator'];
                        $sub->add($query->expr()->$operator($this->getPrefixedName($column->getId()), $filter['value']));
                    }
                    $where->add($sub);
                }
                $query->where($where);
            }
        }
        return new Rows($query->getQuery()->getResult());
    }
}
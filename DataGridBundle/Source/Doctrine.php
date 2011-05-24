<?php

namespace Sorien\DataGridBundle\Source;

use Sorien\DataGridBundle\Column\Range;
use Sorien\DataGridBundle\Column\Text;
use Sorien\DataGridBundle\Column\Column;
use Sorien\DataGridBundle\Source\Source;
use Sorien\DataGridBundle\DataGrid\Row;
use Sorien\DataGridBundle\DataGrid\Rows;

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
        $this->tablePrefix = 'u';
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

    public function getFiledName($name)
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
                    $columns->addColumn(new Range($this->getFiledName($columnMappingData['fieldName']), $columnMappingData['fieldName'], 100));
                break;

                case 'string':
                    $columns->addColumn(new Text($this->getFiledName($columnMappingData['fieldName']), $columnMappingData['fieldName'], 100));
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

        foreach ($columns as $column)
        {
            $query->addSelect($column->getId());
            if ($column->isSorted())
            {
                $query->orderBy($column->getId(), $column->getOrder());
            }

            if ($column->isFiltred())
            {
                $connector = $column->getDataFiltersConnection() == column::DATA_CONJUNCTION ? 'andX' : 'orX';

                $filters = array();
                foreach ($column->getDataFilters() as $filter)
                {
                    $operator = $filter['operator'];
                    $filters[] = $query->expr()->$operator($column->getId(), $filter['value']);
                }

                $query->where(call_user_func_array(array($query->expr(), $connector), $filters));
            }
        }

        var_dump($query->getQuery()->getResult());
        return new Rows();
    }
}
<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *
 * @todo use repositories http://symfony.com/doc/current/book/doctrine.html#using-doctrine-s-query-builder
 */

namespace Sorien\DataGridBundle\Source;

use Sorien\DataGridBundle\Source\Source;
use Sorien\DataGridBundle\Column\Range;
use Sorien\DataGridBundle\Column\Text;
use Sorien\DataGridBundle\Column\Select;
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
    private $query;

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

        $this->table = $metadata->getReflectionClass()->name;
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
        foreach ($this->columnMappings as $mapping)
        {
            switch ($mapping['type'])
            {
                case 'integer':
                case 'smallint':
                case 'bigint':
                case 'integer':
                case 'float':
                    $columns->addColumn(new Range($mapping['fieldName'], $mapping['fieldName'], $mapping['length'] === null ? 100 : $mapping['length']*10, false, false, false));
                break;

                case 'string':
                    $columns->addColumn(new Text($mapping['fieldName'], $mapping['fieldName'], $mapping['length']));
                break;

                case 'text':
                    $columns->addColumn(new Text($mapping['fieldName'], $mapping['fieldName']));
                break;

                case 'boolean':
                    $columns->addColumn(new Select($mapping['fieldName'], $mapping['fieldName'], array('true', 'false'), 50));
                break;
            }

            if (isset($mapping['id']) && $mapping['id'] === true)
            {
                $columns->setPrimaryColumn($mapping['fieldName']);
            }
        }

        //$actions->addMassAction('action', null);
    }

    /**
     * @param $columns \Sorien\DataGridBundle\Column\Column[]
     * @param $page int Page Number
     * @param $limit int Rows Per Page
     * @return \Sorien\DataGridBundle\DataGrid\Rows
     */
    public function execute($columns, $page, $limit)
    {
        $this->query = $this->entityManager->createQueryBuilder();
        $this->query->from($this->table, $this->tablePrefix);

        $where = $this->query->expr()->andx();

        foreach ($columns as $column)
        {
            if ($column->isSpecial()) continue;

            $this->query->addSelect($this->getPrefixedName($column->getId()));

            if ($column->isSorted())
            {
                $this->query->orderBy($this->getPrefixedName($column->getId()), $column->getOrder());
            }

            if ($column->isFiltred())
            {
                if($column->getDataFiltersConnection() == column::DATA_CONJUNCTION)
                {
                    foreach ($column->getDataFilters() as $filter)
                    {
                        $operator = $filter->getOperator();
                        $where->add($this->query->expr()->$operator($this->getPrefixedName($column->getId()), $filter->getValue()));
                    }
                }
                elseif($column->getDataFiltersConnection() == column::DATA_DISJUNCTION)
                {
                    $sub = $this->query->expr()->orx();
                    foreach ($column->getDataFilters() as $filter)
                    {
                        $operator = $filter->getOperator();
                        $sub->add($this->query->expr()->$operator($this->getPrefixedName($column->getId()), $filter->getValue()));
                    }
                    $where->add($sub);
                }
                $this->query->where($where);
            }
        }

        if ($page > 0)
        {
            $this->query->setFirstResult($page * $limit);
        }

        $this->query->setMaxResults($limit);

        return new Rows($this->query->getQuery()->getResult());
    }

    public function getTotalCount($columns)
    {
        $this->query->select(sprintf("count (%s)", $this->getPrefixedName($columns->getPrimaryColumn()->getId())));
        $this->query->setFirstResult(null);
        $this->query->setMaxResults(null);
        $result = $this->query->getQuery()->getSingleResult();

        return $result[1];
    }
}
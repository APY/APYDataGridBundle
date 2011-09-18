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
 */

namespace Sorien\DataGridBundle\Grid\Source;

use Sorien\DataGridBundle\Grid\Mapping\Entity as GridClassMetadata;
use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Rows;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\Mapping\ClassMetadata;

class Entity extends Annotation
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $table;
    private $query;
    private $_class;
    private $_entityName;

    const TABLE_ALIAS = 'a';

    /**
     * @param \Doctrine\ORM\Mapping\ClassMetadata|string $class ClassMetadata or Document Name
     */
    public function __construct($class)
    {
        if ($class instanceof ClassMetadata)
        {
            $this->_entityName = $class->name;
            $this->_class = $class;
        }

        if (is_string($class))
        {
            $this->_entityName = $class;
        }
    }

    public function initialise($container)
    {
        parent::initialise($container);

        if ($this->manager === null)
        {
            $this->manager = $container->get('doctrine')->getEntityManager();
        }

        if ($this->_class === null)
        {
            $this->_class = $this->manager->getClassMetadata($this->_entityName);
        }

        $this->table = $this->_class->getReflectionClass()->name;
    }

    private function getPrefixedName($name)
    {
        return self::TABLE_ALIAS.'.'.$name;
    }

    /**
     * @param \Sorien\DataGridBundle\Grid\Columns $columns
     * @param \Sorien\DataGridBundle\Grid\Actions $actions
     * @return null
     */
    public function prepare($columns, $actions)
    {
        foreach ($this->getColumnsMapping($this->_entityName, $this->table, $columns) as $column)
        {
            $columns->addColumn($column);
        }
    }

    private function normalizeOperator($operator)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? 'like' : $operator);
    }

    private function normalizeValue($operator, $value)
    {
        return ($operator == COLUMN::OPERATOR_REGEXP ? '\''.str_replace('.*', '%', $value).'\'' : $value);
    }

    /**
     * @param $columns \Sorien\DataGridBundle\Grid\Column\Column[]
     * @param $page int Page Number
     * @param $limit int Rows Per Page
     * @return \Sorien\DataGridBundle\Grid\Rows
     */
    public function execute($columns, $page, $limit)
    {
        $this->query = $this->manager->createQueryBuilder();
        $this->query->from($this->table, self::TABLE_ALIAS);

        $where = $this->query->expr()->andx();

        foreach ($columns as $column)
        {
            $this->query->addSelect($this->getPrefixedName($column->getId()));

            if ($column->isSorted())
            {
                $this->query->orderBy($this->getPrefixedName($column->getId()), $column->getOrder());
            }

            if ($column->isFiltered())
            {
                if($column->getFiltersConnection() == column::DATA_CONJUNCTION)
                {
                    foreach ($column->getFilters() as $filter)
                    {
                        $operator = $this->normalizeOperator($filter->getOperator());

                        $where->add($this->query->expr()->$operator(
                            $this->getPrefixedName($column->getId()),
                            $this->normalizeValue($filter->getOperator(), $filter->getValue())
                        ));
                    }
                }
                elseif($column->getFiltersConnection() == column::DATA_DISJUNCTION)
                {
                    $sub = $this->query->expr()->orx();

                    foreach ($column->getFilters() as $filter)
                    {
                        $operator = $this->normalizeOperator($filter->getOperator());

                        $sub->add($this->query->expr()->$operator(
                              $this->getPrefixedName($column->getId()),
                              $this->normalizeValue($filter->getOperator(), $filter->getValue())
                        ));
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

        return new Rows($this->query->getQuery()->getResult());
    }

    public function getTotalCount($columns)
    {
        $this->query->select(sprintf("count (%s)", $this->getPrefixedName($columns->getPrimaryColumn()->getId())));
        $this->query->setFirstResult(null);
        $this->query->setMaxResults(null);
        $result = $this->query->getQuery()->getSingleResult();

        return (int)$result[1];
    }
}
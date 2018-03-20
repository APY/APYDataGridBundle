<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid;

use Doctrine\ORM\EntityRepository;

class Row
{
    /** @var array */
    protected $fields;

    /** @var string */
    protected $class;

    /** @var string */
    protected $color;

    /** @var string|null */
    protected $legend;

    /** @var mixed */
    protected $primaryField;

    /** @var mixed */
    protected $entity;

    /** @var EntityRepository */
    protected $repository;

    public function __construct()
    {
        $this->fields = [];
        $this->color = '';
    }

    /**
     * @param EntityRepository $repository
     */
    public function setRepository(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return null|object
     */
    public function getEntity()
    {
        $primaryKeyValue = current($this->getPrimaryKeyValue());

        return $this->repository->find($primaryKeyValue);
    }

    /**
     * @return array
     */
    public function getPrimaryKeyValue()
    {
        $primaryFieldValue = $this->getPrimaryFieldValue();

        if (is_array($primaryFieldValue)) {
            return $primaryFieldValue;
        }

        // @todo: is that correct? shouldn't be [$this->primaryField => $primaryFieldValue] ??
        return ['id' => $primaryFieldValue];
    }

    /**
     * @throws \InvalidArgumentException
     *
     * @return array|mixed
     */
    public function getPrimaryFieldValue()
    {
        if (null === $this->primaryField) {
            throw new \InvalidArgumentException('Primary column must be defined');
        }

        if (is_array($this->primaryField)) {
            return array_intersect_key($this->fields, array_flip($this->primaryField));
        }

        if (!isset($this->fields[$this->primaryField])) {
            throw new \InvalidArgumentException('Primary field not added to fields');
        }

        return $this->fields[$this->primaryField];
    }

    /**
     * @param mixed $primaryField
     *
     * @return $this
     */
    public function setPrimaryField($primaryField)
    {
        $this->primaryField = $primaryField;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrimaryField()
    {
        return $this->primaryField;
    }

    /**
     * @param mixed $columnId
     * @param mixed $value
     *
     * @return $this
     */
    public function setField($columnId, $value)
    {
        $this->fields[$columnId] = $value;

        return $this;
    }

    /**
     * @param mixed $columnId
     *
     * @return mixed
     */
    public function getField($columnId)
    {
        return isset($this->fields[$columnId]) ? $this->fields[$columnId] : '';
    }

    /**
     * @param string $class
     *
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $legend
     *
     * @return $this
     */
    public function setLegend($legend)
    {
        $this->legend = $legend;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLegend()
    {
        return $this->legend;
    }
}

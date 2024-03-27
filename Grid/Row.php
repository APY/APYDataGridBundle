<?php

namespace APY\DataGridBundle\Grid;

use Doctrine\ORM\EntityRepository;

class Row
{
    protected array $fields;

    protected ?string $class = null;

    protected string $color;

    protected ?string $legend = null;

    protected mixed $primaryField = null;

    protected mixed $entity = null;

    protected ?EntityRepository $repository = null;

    public function __construct()
    {
        $this->fields = [];
        $this->color = '';
    }

    public function setRepository(EntityRepository $repository): void
    {
        $this->repository = $repository;
    }

    public function getEntity(): ?object
    {
        $primaryKeyValue = \current($this->getPrimaryKeyValue());

        return $this->repository->find($primaryKeyValue);
    }

    public function getPrimaryKeyValue(): array
    {
        $primaryFieldValue = $this->getPrimaryFieldValue();

        if (\is_array($primaryFieldValue)) {
            return $primaryFieldValue;
        }

        // @todo: is that correct? shouldn't be [$this->primaryField => $primaryFieldValue] ??
        return ['id' => $primaryFieldValue];
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function getPrimaryFieldValue(): mixed
    {
        if (null === $this->primaryField) {
            throw new \InvalidArgumentException('Primary column must be defined');
        }

        if (\is_array($this->primaryField)) {
            return \array_intersect_key($this->fields, \array_flip($this->primaryField));
        }

        if (!isset($this->fields[$this->primaryField])) {
            throw new \InvalidArgumentException('Primary field not added to fields');
        }

        return $this->fields[$this->primaryField];
    }

    public function setPrimaryField(mixed $primaryField): static
    {
        $this->primaryField = $primaryField;

        return $this;
    }

    public function getPrimaryField(): mixed
    {
        return $this->primaryField;
    }

    public function setField(mixed $columnId, mixed $value): static
    {
        $this->fields[$columnId] = $value;

        return $this;
    }

    public function getField(mixed $columnId): mixed
    {
        return $this->fields[$columnId] ?? '';
    }

    public function setClass(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setLegend(string $legend): static
    {
        $this->legend = $legend;

        return $this;
    }

    public function getLegend(): ?string
    {
        return $this->legend;
    }
}

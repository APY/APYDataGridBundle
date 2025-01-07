<?php

namespace APY\DataGridBundle\Grid;

class Filter
{
    protected mixed $value;
    protected string $operator;
    protected ?string $columnName;

    public function __construct(string $operator, mixed $value = null, ?string $columnName = null)
    {
        $this->value = $value;
        $this->operator = $operator;
        $this->columnName = $columnName;
    }

    public function setOperator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setValue(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function hasColumnName(): bool
    {
        return null !== $this->columnName;
    }

    public function setColumnName(string $columnName): static
    {
        $this->columnName = $columnName;

        return $this;
    }

    public function getColumnName(): ?string
    {
        return $this->columnName;
    }
}

<?php

namespace APY\DataGridBundle\Grid;

class Rows implements \IteratorAggregate, \Countable
{
    protected \SplObjectStorage $rows;

    public function __construct(array $rows = [])
    {
        $this->rows = new \SplObjectStorage();

        foreach ($rows as $row) {
            $this->addRow($row);
        }
    }

    public function getIterator(): \Traversable
    {
        return $this->rows;
    }

    public function addRow(Row $row): static
    {
        $this->rows->attach($row);

        return $this;
    }

    public function count(): int
    {
        return $this->rows->count();
    }

    public function toArray(): array
    {
        return \iterator_to_array($this->getIterator(), true);
    }
}

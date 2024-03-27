<?php

namespace APY\DataGridBundle\Grid\Column;

class UntypedColumn extends Column
{
    protected ?string $type = null;

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }
}

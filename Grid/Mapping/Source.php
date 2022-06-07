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

namespace APY\DataGridBundle\Grid\Mapping;

use Attribute;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Source
{
    protected array $columns;
    protected bool $filterable;
    protected bool $sortable;
    protected array $groups;
    protected array $groupBy;

    public function __construct(array $metadata = [], array $groups = ['default'], array $columns = [], bool $filterable = true, bool $sortable = true, array $groupBy = [])
    {
        if ($metadata['columns'] ?? []) {
            $this->columns = \array_map('trim', \is_array($metadata['columns']) ? $metadata['columns'] : \explode(',', $metadata['columns'])) ;
        } else {
            $this->columns = $columns;
        }
        $this->filterable = $metadata['filterable'] ?? $filterable;
        $this->sortable = $metadata['sortable'] ?? $sortable;
        $this->groups = (isset($metadata['groups']) && !empty($metadata)) ? (array) $metadata['groups'] : $groups;
        $this->groupBy = (isset($metadata['groupBy']) && !empty($metadata)) ? (array) $metadata['groupBy'] : $groupBy;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function hasColumns(): bool
    {
        return !empty($this->columns);
    }

    public function isFilterable(): bool
    {
        return $this->filterable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }

    public function getGroupBy(): array
    {
        return $this->groupBy;
    }
}

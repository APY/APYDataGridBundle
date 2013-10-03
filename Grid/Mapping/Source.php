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

/**
 * @Annotation
 */
class Source
{
    protected $columns;
    protected $filterable;
    protected $sortable;
    protected $groups;
    protected $groupBy;

    public function __construct($metadata = array())
    {
        $this->columns = (isset($metadata['columns']) && $metadata['columns'] != '') ? array_map('trim', explode(',', $metadata['columns'])) : array();
        $this->filterable = isset($metadata['filterable']) ? $metadata['filterable'] : true;
        $this->sortable = isset($metadata['sortable']) ? $metadata['sortable'] : true;
        $this->groups = (isset($metadata['groups']) && $metadata['groups'] != '') ? (array) $metadata['groups'] : array('default');
        $this->groupBy = (isset($metadata['groupBy']) && $metadata['groupBy'] != '') ? (array) $metadata['groupBy'] : array();
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumns()
    {
        return !empty($this->columns);
    }

    public function isFilterable()
    {
        return $this->filterable;
    }

    public function isSortable()
    {
        return $this->sortable;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function getGroupBy()
    {
        return $this->groupBy;
    }
}

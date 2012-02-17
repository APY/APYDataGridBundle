<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Mapping;

/**
 * @Annotation
 */
class Source
{
    private $columns;
    private $filterable;

    public function __construct($metadata = array())
    {
        $this->columns = isset($metadata['columns']) ? array_map('trim', explode(',', $metadata['columns'])) : array();
        $this->filterable = !(isset($metadata['filterable']) && $metadata['filterable']);
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
}

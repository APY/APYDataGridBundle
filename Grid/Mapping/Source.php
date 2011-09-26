<?php
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
        $this->columns = isset($metadata['columns']) ? explode(',', $metadata['columns']) : array();
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

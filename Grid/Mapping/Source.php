<?php
namespace Sorien\DataGridBundle\Grid\Mapping;

/**
 * @Annotation
 */
class Source
{
    private $columns;
    private $attachedColumns;

    private $filterable;
    private $attached;

    public function __construct($metadata = array())
    {
        $this->columns = isset($metadata['columns']) ? explode(',', $metadata['columns']) : array();
        $this->filterable = !(isset($metadata['filterable']) && $metadata['filterable']);

        if (isset($metadata['attach']))
        {
            $this->attached = $metadata['attach'];

            if (!isset($this->attached ['type']) || !isset($this->attached ['id']))
            {
                throw new \Exception('Attached column need to have specified "type" and "id"');
            }

            if (!in_array($this->attached['id'], $this->columns))
            {
                $this->columns[] = $this->attached['id'];
            }
        }
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

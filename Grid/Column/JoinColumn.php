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

namespace APY\DataGridBundle\Grid\Column;

class JoinColumn extends TextColumn
{
    protected $joinColumns = array();

    protected $dataJunction = self::DATA_DISJUNCTION;

    public function __initialize(array $params)
    {
        parent::__initialize($params);

        $this->setJoinColumns($this->getParam('columns', array()));
        $this->setSeparator($this->getParam('separator', '&nbsp;'));

        $this->setVisibleForSource(true);
        $this->setIsManualField(true);
    }

    public function setJoinColumns(array $columns) {
        $this->joinColumns = $columns;
    }

    public function getJoinColumns() {
        return $this->joinColumns;
    }

    public function getFilters($source)
    {
        $filters = array();

        // Apply same filters on each column
        foreach ($this->joinColumns as $columnName) {
            $tempFilters = parent::getFilters($source);

            foreach ($tempFilters as $filter) {
                $filter->setColumnName($columnName);
            }

            $filters = array_merge($filters, $tempFilters);
        }

        return $filters;
    }

    public function getType()
    {
        return 'join';
    }
}

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

class ActionsColumn extends Column
{
    protected $rowActions;
    protected $separator;

    public function __construct($column, $title, array $rowActions = array(), $separator = '<br />')
    {
        $this->rowActions = $rowActions;
        $this->separator = $separator;

        parent::__construct(array(
            'id'         => $column,
            'title'      => $title,
            'sortable'   => false,
            'source'     => false,
            'filterable' => true // Show a reset link instead of a filter
        ));
    }

    public function getRouteParameters($row, $action)
    {
        // default the entity primary field with value, can be overriden in buttom
        $routeParameters = array(
            $row->getPrimaryField() => $row->getPrimaryFieldValue()
        );
        $actionParameters = $action->getRouteParameters();

        if(!empty($actionParameters)) {
            foreach ($actionParameters as $name => $parameter) {
                if(is_int($name)) {
                    $routeParameters[$this->getValidRouteParameters($parameter)] = $row->getField($parameter);
                } else {
                    $routeParameters[$this->getValidRouteParameters($name)] = $parameter;
                }
            }
        }

        return $routeParameters;
    }

    protected function getValidRouteParameters($name)
    {
        $pos = 0;
        while (($pos = strpos($name, '.', ++$pos)) !== false) {
            $name = substr($name, 0, $pos) . strtoupper(substr($name, $pos+1, 1)) . substr($name, $pos+2);
        }

        return $name;
    }

    public function getRowActions()
    {
        return $this->rowActions;
    }

    public function setRowActions(array $rowActions)
    {
        $this->rowActions = $rowActions;

        return $this;
    }

    public function isVisible($isExported = false)
    {
        if ($isExported) {
            return false;
        }

        return parent::isVisible();
    }

    public function setSeparator($separator)
    {
        $this->separator = $separator;

        return $this;
    }

    public function getSeparator()
    {
        return $this->separator;
    }

    public function getFilterType()
    {
        return $this->getType();
    }

    public function getType()
    {
        return 'actions';
    }
}

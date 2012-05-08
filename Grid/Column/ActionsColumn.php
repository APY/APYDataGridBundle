<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Column;

use Sorien\DataGridBundle\Grid\Action\RowAction;

class ActionsColumn extends Column
{
    protected $rowActions;

    public function __construct($column, $title, array $rowActions = array())
    {
        $this->rowActions = $rowActions;
        parent::__construct(array('id' => $column, 'title' => $title, 'sortable' => false, 'source' => false, 'filterable' => false));
    }

    public function getRouteParameters($row, $action)
    {
        $actionParameters = $action->getRouteParameters();
        if(!empty($actionParameters)){
            $routeParameters = array();
            foreach ($actionParameters as $name => $parameter) {
                if(is_numeric($name)){
                    $routeParameters[$parameter] = $row->getField($parameter);
                } else {
                    $routeParameters[$name] = $parameter;
                }
            }
            return $routeParameters;
        }

        return array_merge(
            array($row->getPrimaryField() => $row->getPrimaryFieldValue()),
            $action->getRouteParameters()
        );
    }

    public function getRowActions()
    {
        return $this->rowActions;
    }

    public function setRowActions(array $rowActions) {
        $this->rowActions = $rowActions;
    }

    public function getType()
    {
        return 'actions';
    }
}

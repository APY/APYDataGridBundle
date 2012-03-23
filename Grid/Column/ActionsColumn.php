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
    private $rowActions;

    public function __construct($column, $title, array $rowActions = array(), array $params = array())
    {
        $this->rowActions = $rowActions;
        parent::__construct(
            array_merge(
                $params,
                array('id' => $column, 'title' => $title, 'sortable' => false, 'source' => false)
            )
        );
    }

    public function renderCell($value, $row, $router)
    {
        $return = '';
        /* @var $action RowAction */
        foreach ($this->rowActions as $action) {
            $routeParameters = array_merge(
                array($row->getPrimaryField() => $row->getPrimaryFieldValue()),
                $action->getRouteParameters()
            );

            $route = $action->getRoute();
            if (is_callable($route)) {
                $url = $route($router, $routeParameters, $row);
            } else {
                $url = $router->generate($route, $routeParameters);
            }

            $return .= "<a href='". $url;

            if ($action->getConfirm())
                $return .= "' onclick=\"return confirm('".$action->getConfirmMessage()."');\"";
            $return .= "' target='".$action->getTarget()."'";


            foreach ($action->getAttributes() as $key => $value) {
                $return .= ' ' . $key . '="' . $value . '"';
            }

            $return .=">".$action->getTitle()."</a> ";
        }

        return $return;
    }

    public function renderFilter($gridHash)
    {
        if (!$this->getSubmitOnChange()) {
            return '<input name="'.$gridHash.'[submit]" type="submit" value="Filter"/>';
        }
    }

    public function setRowActions(array $rowActions) {
        $this->rowActions = $rowActions;
    }
}

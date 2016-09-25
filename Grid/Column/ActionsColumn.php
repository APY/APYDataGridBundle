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

    public function __construct($column, $title, array $rowActions = array())
    {
        $this->rowActions = $rowActions;

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
        $actionParameters = $action->getRouteParameters();

        if(!empty($actionParameters)) {
            $routeParameters = array();

            foreach ($actionParameters as $name => $parameter) {
                if(is_int($name)) {
                    if(($name = $action->getRouteParametersMapping($parameter)) === null) {
                        $name = $this->getValidRouteParameters($parameter);
                    }
                    $routeParameters[$name] = $row->getField($parameter);
                } else {
                    $routeParameters[$this->getValidRouteParameters($name)] = $parameter;
                }
            }

            return $routeParameters;
        }

        return array($row->getPrimaryField() => $row->getPrimaryFieldValue());
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

    public function getFilterType()
    {
        return $this->getType();
    }

    /**
     * Get the list of actions to render
     *
     * @param $row
     * @return array
     */
    public function getActionsToRender($row)
    {
        $list = $this->rowActions;
        foreach($list as $i=>$a) {
            $action = clone $a;
            $list[$i] = $action->render($row);
            if(null === $list[$i]) {
                unset($list[$i]);
            }
        }

        return $list;
    }

    public function getType()
    {
        return 'actions';
    }
}

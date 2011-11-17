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

    public function __construct($column, $title, array $rowActions = array())
    {
        $this->rowActions = $rowActions;
        parent::__construct(array('id' => $column, 'title' => $title, 'sortable' => false, 'source' => false));
    }

    public function renderCell($value, $row, $router)
    {
        $return = '';
        /* @var $action RowAction */
        foreach ($this->rowActions as $action) {
            $return .= "<a href='".$router->generate($action->getRoute(), array($row->getPrimaryField() => $row->getPrimaryFieldValue()), false);

            if ($action->getConfirm())
                $return .= "' onclick=\"return confirm('".$action->getConfirmMessage()."');\"";

            $return .= "' target='".$action->getTarget()."'";
            $return .=">".$action->getTitle()."</a> ";
        }

        return $return;
    }
    
    public function setRowActions(array $rowActions) {
        $this->rowActions = $rowActions;
    }
}

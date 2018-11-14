Working Example
===============

```php
<?php

namespace MyProject\MyBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Action\MassAction;
use APY\DataGridBundle\Grid\Action\DeleteMassAction;
use APY\DataGridBundle\Grid\Action\RowAction;

class DefaultController extends Controller
{
    public static function myStaticMethod(array $ids)
    {
        // Do whatever you want with these ids
    }

    public function myMethod(array $ids)
    {
        // Do whatever you want with these ids
    }

    public function gridAction()
    {
        $source = new Entity('MyProjectMyBundle:User');

        /* @var $grid APY\DataGridBundle\Grid\Grid */

        // Get a grid instance
        $grid = $this->get('grid');
        
        // Set the source
        $grid->setSource($source);

        // Set the selector of the number of items per page
        $grid->setLimits(array(5, 10, 15));

        // Set the default page
        $grid->setDefaultPage(1);

        // Add a mass action with static callback
        $yourMassAction = new MassAction('Action 1', 'MyProject\MyBundle\Controller\DefaultController::myStaticMethod');
        $grid->addMassAction($yourMassAction);

        // Add a mass action with object callback
        $yourMassAction = new MassAction('Action 2', array($this, 'myMethod'));
        $grid->addMassAction($yourMassAction);

        // Add a delete mass action
        $grid->addMassAction(new DeleteMassAction());

        // Add a column in the third position
        $MyColumn = new BlankColumn(array('id' => 'My Column', 'title'=>'My Column', 'size' => '54'));
        $grid->addColumn($MyColumn, 3);

        // Add a typed column with a rendering callback
        $MyColumn2 = new DateColumn(array('id' => 'Another Column', 'sortable' => true, 'filterable' => false, 'source' => false));
        $MyColumn2->manipulateRenderCell(function($value, $row, $router) {
            return $router->generateUrl('_my_route', array('param' => $row->getField('column')));}
        );
        $grid->addColumn($MyColumn2);

        // Add row actions in the default row actions column
        $myRowAction = new RowAction('Edit', 'route_to_edit');
        $grid->addRowAction($myRowAction);

        $myRowAction = new RowAction('Delete', 'route_to_delete', true, '_self');
        $grid->addRowAction($myRowAction);

        // Custom actions column in the wanted position
        $myActionsColumn = new ActionsColumn('info_column','Info');
        $grid->addColumn($myActionsColumn, 1);

        $myRowAction = new RowAction('Show', 'route_to_show');
        $myRowAction->setColumn('info_column');
        $grid->addRowAction($myRowAction);

        return $grid->getGridResponse('MyProjectMyBundle::grid.html.twig');
    }
}
```

And the template:

```janjo
<!-- MyProjectMyBundle::grid.html.twig -->
<html>
  <head>
    <meta charset="utf-8">
    <title>grid</title>
    <style type="text/css">
<!--
.grid table
{
    font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
    border-collapse: collapse;
    color: #555555;
    font-size: 1em;
    width: 100%;
}

.grid td, .grid th
{
    border: 1px solid #D4E0EE;
    padding: 3px 7px 2px 7px;
}
.grid th
{
    background-color: #E6EDF5;
    vertical-align: top;
}

.grid th a {
    color: #4F76A3;
    text-decoration: none;
}

.grid th a.grid-reset {
    margin-left: 5px;
    font-weight: normal;
}

.grid tr.even
{
    background-color: #FCFDFE;
}

.grid tr.odd {
    background-color: #F7F9FC;

}

.grid_header, .grid_footer {
    margin: 5px 0;
}

/* Icons for order */
/* You can find this icons in the images directory of the docuementation */
th div {
    height: 10px;
    width: 20px;
    float: right;
    padding-top: 4px;
}

.grid th div.sort_up {
    background: transparent url("data:image/gif;base64,R0lGODlhFwAKAIABAJCQkO/v7yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAXAAoAAAIajI+py+0GwGsxTmVDvlqe/YCQ52wmyaXqUQAAOw==") no-repeat bottom left;
}

.grid th div.sort_down {
    background: transparent url("data:image/gif;base64,R0lGODlhFwAKAHAAACH5BAEAAAIALAAAAAAXAAoAgQAAAJCQkAAAAAAAAAIalI+py60RDpTRiZmwvdXozXkdKH6keKZqUwAAOw==") no-repeat bottom left;
}

/* Boolean column */
.grid .grid_boolean_true {
    background: transparent url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACl0lEQVR42q2T60uTYRiH/Tv2bnttAwlkRCGChFD7FCQSm2ZDMQ/L0nRnj7TNGDbTooychzFSSssstdqc8zB1anNrSpm47FVCzH3pQLVhdLBfzztoJlifvOEHz4fnuu7nGBe311XgOyLMnTmsz/akMBljB8OSEVFY4kpkJM5Efbp9v/C/cJ43VSrzJId0HhluBy3oW+mKpnOpGSWuExD30iFxDy3dFSZdpZkTSZHr80Y41/phe3UDpvnKaNixY60PjbNVOGTjRZJtvJ2SHE+KINOdtMHC7MSaQBkq/CXQzJ6DjqScpNp3HvY3D3B5ugIiC3dDdJMriAlk7iSDajwr2pmFWVDlPQPFTCEU0wVQTxfCvT4Ig1cJB5Hk9hxDwjWuISbIGBExncFmWINNqPAVQ/lUTsB8KKdIPPmYeOsCW6HIOtpeNMI234j4ei4TExy3J2w+Wr2L2oAGWm8RWckAlj4uQDVZiPH1oSj8c+sH2p5fgWGyGH3BTvCN1GZMIH5Ib/avdMPoV6HWr8Xnb5+i0Iev72KwZa4ealc29O6z6A92gF/zt6CHZm4tNKF98Sp0U3KYfdWIfP8Shbd+bcHy7BLKnFnQEEFLoA7tXjPoKmp7C6l3+Ab5QBrsq/dRPSmH2n0adTPlWH6/iLa5BpQOnoTCcQo6Zw7sr7uRbj0KupLaPsRkK09wgFyN2aPBY+YeKkfzoB3OgWpIBqWDDQtn48lyF4xDxeCrORu0mhLseAuJTVxpfAMVMbnL4CCS1oAZ+tEiXBiWo5VswU5gvbMIvFJOhMC7v8Z9DVwpbaJCkg4x2v1m9L60onfBCovXhLSWVPAVnBCt+gf8p+iLXCFtoPR0DcXwtZwwX8UJk44MiZ4upYR7/nt/A+w9sdKFchsrAAAAAElFTkSuQmCC") no-repeat bottom left;
    display: inline-block;
    text-indent: 16px;
    width: 16px;
    overflow: hidden;
}

.grid .grid_boolean_false {
    background: transparent url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACSElEQVR42q2Ty0vUURTHv/5SJ1SmUaGckUYdG5FyVdAicGMkFAXBtEl8gJseIP0FEVEtbNNiFm0iMMUgGKhVIUj0oqKFQ40N2TwwdewxOik6zcy953tbmD/HxFV9d/dwz+f7PXAO8I8q+bugb1xspjFdhuwlWUcSJL+SvEfhaPXgcHxbQOH6hYChCa6WlLvLm/eh1OkEAKjlJWSjUVjZpXlSBvbcehDaAshfOx8w5FB+t7eyosUPSU2DqWkAgOX2wvI0YPljFLmpD6sU6fPefhSyAbmr53wkXxTqvJ7KlhYUXo0BACou3wEArFzpB2hQ3t6JpcgkVqITKSHb/XefJCwAENHdWcux0WwMDI09GjUh2iA7/hjO1v0wVbUerVQ3AFgAoLXucfj9kNkkDA0oBkZzE4BKIAUin4hjV2sblFI9xYD6MpcLeiYJo2k7rksKhKi1ei4WR5mrBlrp+g2A0jBi7MZ1RzUV2RhB/YEIASG0VihKoOby6UVY7gY7qiji18txrDy8b7tTEaV7G5FNp6GVnitOMPwz8h47PI32Z1GEs/8Sqk6fBRUhmhAhHI0+fA+/g9Z62AYopUYKC6lUZjKCio7jNmQxeBMLwUGIrL1dJ07iRySMzMznlNZ6ZNMiTXZ3BCgy5DpwuLK6tQ25ZAK5WBzGGDiafHA0+ZCOhPHl9dgqyb6jTz+FtqzyxJkjASGDO2s87tq2g3C4qmEMkMss4tvEW2RmY/MkB449mwpte0xvTh1qJtlFYS8pm4+JHO18Hovjf+o3Xg+XX4ZLBPIAAAAASUVORK5CYII=") no-repeat bottom left;
    display: inline-block;
    text-indent: 16px;
    width: 16px;
    overflow: hidden;
}

/* Alignement */
.grid .align-left {
    text-align: left;
}

.grid .align-center {
    text-align: center;
}

.grid .align-right {
    text-align: right;
}

/* Column filter */
.grid .grid-filter-operator select{
    width: 70px;

}

.grid .grid-filter-input-query input, .grid .grid-filter-select-query select{
    width: 50px;
}

.grid .grid-filter-input-query-to, .grid .grid-filter-select-query-to{
    margin-left: 77px;
    display: block;
}

/* Grid Search */

.grid-search {
    border: 1px solid #D4E0EE;
    padding: 10px;
}

.grid-search label{
    width: 80px;
    display: inline-block;
    text-align: right;
}

.grid-search select, .grid-search .grid-filter-input-query input {
    width: 150px;
}
-->
    </style>
  </head>
  <body>
    {{ grid(grid) }}
  </body>
</html>
```

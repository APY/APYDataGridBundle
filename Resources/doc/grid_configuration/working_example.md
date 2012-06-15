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

        $grid = $this->get('grid');

        // Set the selector of the number of items per page
        $grid->setLimits(array(5, 10, 15));

        // Set the default page
        $grid->setPage(1);

        // Add a mass action with static callback
        $yourMassAction = new MassAction('Action 1', 'MyProject\MyBundle\Controller\DefaultController::myStaticMethod');
        $grid->addMassAction($yourMassAction);

        // Add a mass action with object callback
        $yourMassAction = new MassAction('Action 2', array($this, 'myMethod'));
        $grid->addMassAction($yourMassAction);

        // Add a delete mass action
        $grid->addMassAction(new DeleteMassAction());

        // Set the source
        $grid->setSource($source);

        // Add a column in the third position
        $MyColumn = new TextColumn(array('id' => 'My Column', 'title'=>'My Column', 'size' => '54', 'sortable' => true, 'filterable' => false, 'source' => false));
        $grid->addColumn($MyColumn, 3);

        // Add a column with a rendering callback
        $MyColumn2 = new TextColumn(array('id' => 'Another Column')); 
        $MyColumn2->manipulateRender(function($value, $row, $router) {
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
.grid th div.sort_up {
    background: transparent url("data:image/gif;base64,R0lGODlhFwAKAHAAACH5BAEAAAIALAAAAAAXAAoAgQAAAJCQkAAAAAAAAAIalI+py60RDpTRiZmwvdXozXkdKH6keKZqUwAAOw==") no-repeat bottom left;
}

.grid th div.sort_down {
    background: transparent url("data:image/gif;base64,R0lGODlhFwAKAIABAJCQkO/v7yH+EUNyZWF0ZWQgd2l0aCBHSU1QACH5BAEKAAEALAAAAAAXAAoAAAIajI+py+0GwGsxTmVDvlqe/YCQ52wmyaXqUQAAOw==") no-repeat bottom left;
}

th div {
    height: 10px;
    width: 20px;
    float: right;
    padding-top: 4px;
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
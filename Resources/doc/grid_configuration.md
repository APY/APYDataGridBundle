Grid configurations
===================

## Configure the pager
```php
<?php
// Set the selector of the number of items per page
$grid->setLimits(array(5 => '5', 10 => '10', 15 => '15'));

// Set the default page
$grid->setPage(2);

$grid->setSource($source);
```

## Add columns to the grid

A column must be defined after the source otherwise it will appear always before the columns of the source.

```php
<?php
$grid->setSource($source);

// First parameter : Associative array of parameters (See annotations) 
$MyColumn = new Column(array('id' => 'My Column', 'title'=>'My Column', 'size' => '54', 'sortable' => true, 'filterable' => false, 'source' => false));

// Add the column to the last position
$grid->addColumn($MyColumn);

// or add this column to the third position
$grid->addColumn($MyColumn, 3);
```

**Note**: To keep the correct position of each column, it's better to define them in ascending order of position.

## Add a mass action

A mass action calls a function with an array of the selected rows as first argument.

A mass action must be defined before the source because the callback to the function is performed when you set the source.

```php
<?php
// First parameter : Title displayed in the selector
// Second parameter : Callback function
$yourMassAction = new MassAction('Action 1', 'MyProject\MyBundle\Controller\DefaultController::myStaticMethod');

// OR 

$yourMassAction = new MassAction('Action 2', array('MyProject\MyBundle\Controller\DefaultController','myMethod'));
        
$grid->addMassAction($yourMassAction);

$grid->setSource($source);
```

If you define mass actions, a selector appears and a new column of checkboxes is displayed on the left of the grid.

### Add a default delete mass action `Beta`

```php
<?php
$grid->addMassAction(new DeleteMassAction());

$grid->setSource($source);
```

This mass action calls the delete method of the source.

**Notes**: 
`The primary field of the grid must be the same that the primary key of your source.
Don't use this mass action with the 'one' Entity or Document of a one-to-many relation.`

## Add row actions

A row action is an action performed only on the current row. It's represented by a route to a controller with the identifier of the row.

Row actions are all put in the same new action column at the last position of the grid.

Row actions must be defined after the source otherwise it will appear always before the columns of the source.


```php
<?php
$grid->setSource($source);

// First parameter : Title displayed in the column
// Second parameter : Identifier of your route defined in your routing file
// Third parameter : Set to true if you want a confirm message (default: false)
// Fourth parameter : Set the target of the generated link (default:_self, _blank, _top, _parent)
$myRowAction = new RowAction('Delete', 'route_to_delete', true, '_self');
$grid->addRowAction($myRowAction);

$myRowAction = new RowAction('Edit', 'route_to_edit');
$grid->addRowAction($myRowAction);
```

### Add multiple columns of row actions

You can create other columns of row actions and choose the position of these ones.

```php
<?php
$grid->setSource($source);

// Add an actions column to the grid with the wanted position
// First parameter : Identifier of the column
// Second parameter : Title of the column
$myActionsColumn = new ActionsColumn('info_column','Info');
$grid->addColumn($myActionsColumn, 1);

// and linked your row action to this new column
$myRowAction = new RowAction('Show', 'route_to_show');
// First parameter : Identifier of the actions column
$myRowAction->setColumn('info_column');
$grid->addRowAction($myRowAction);

$myRowAction = new RowAction('Edit', 'route_to_edit');
$myRowAction->setColumn('info_column');
$grid->addRowAction($myRowAction);
```

## Manipulate the query builder

You can set a callback to manipulate the query builder.

Must be defined before the source.

```php
<?php
$source->setCallback(Source::EVENT_PREPARE_QUERY, function ($query) {
	$query->setMaxResults(1);
});

$grid->setSource($source);
```

## Manipulate rows

You can set a callback to manipulate the row of the grid.

Must be defined before the source.

```php
<?php
$source->setCallback(Source::EVENT_PREPARE_ROW, function ($row) {
	if ($row->getField('enabled')=='1') {
		$row->setColor('#00ff00');
	}
});

$grid->setSource($source);
```

## Grid Response

A gridResponse method is also available which handle the redirection and the rendering

```php
<?php
$source = new Entity('MyProjectMyBundle:MyEntity');

$grid = $this->get('grid');

// Mass actions, query and row manipulations are defined here

$grid->setSource($source);

return $gridManager->gridResponse(array('data' => $grid), 'MyProjectMyBundle::my_grid.html.twig');

```

**Note:** Input arguments of gridResponse are reverse. If you use the @Template annotation, don't define a template view.

```php
<?php
...
return $gridManager->gridResponse(array('data' => $grid));

```

Working Exemple
----------------

```php
<?php

namespace MyProject\MyBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sorien\DataGridBundle\Grid\Source\Entity;
use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Column\ActionsColumn;
use Sorien\DataGridBundle\Grid\Action\MassAction;
use Sorien\DataGridBundle\Grid\Action\DeleteMassAction;
use Sorien\DataGridBundle\Grid\Action\RowAction;

class DefaultController extends Controller
{
    static public function myStaticMethod(array $ids)
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

        /* @var $grid Sorien\DataGridBundle\Grid\Grid */

        $grid = $this->get('grid');

        // Set the selector of the number of items per page
        $grid->setLimits(array(5 => '5', 10 => '10', 15 => '15'));

        // Set the default page
        $grid->setPage(1);

        // Add a mass action
        $yourMassAction = new MassAction('Action 1', 'MyProject\MyBundle\Controller\DefaultController::myStaticMethod');
        $grid->addMassAction($yourMassAction);

        // Add a mass action
        $yourMassAction = new MassAction('Action 2', array('MyProject\MyBundle\Controller\DefaultController','myMethod'));
        $grid->addMassAction($yourMassAction);

        // Add a delete mass action
        $grid->addMassAction(new DeleteMassAction());

        // Set the source
        $grid->setSource($source);

        // Add a column in the third position
        $MyColumn = new Column(array('id' => 'My Column', 'title'=>'My Column', 'size' => '54', 'sortable' => true, 'filterable' => false, 'source' => false));
        $grid->addColumn($MyColumn, 3);

        // Add a column with a rendering callback
        $MyColumn2 = new Column(array('id' => 'Another Column', 'callback' => function($value, $row, $router) {
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

        return $gridManager->gridResponse(array('data' => $grid), 'MyProjectMyBundle::my_grid.html.twig');
    }
}

```

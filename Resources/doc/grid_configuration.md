Grid configurations
===================

# Summary

 * [Configure the pager](#configure_pager)
 * [Add columns to the grid](#add_column)
 * [Add a mass action](#add_mass_action)
 * [Add a default delete mass action `Beta`](#delete_mass_action)
 * [Add row actions](#add_row_actions)
 * [Add multiple columns of row actions](#custom_row_actions)
 * [Init filters value](#init_filters)
 * [Manipulate the query builder](#manipulate_query)
 * [Manipulate rows](#manipulate_rows)
 * [Set data to avoid calling the database](#set_data)
 * [Grid Response helper](#grid_response)
 * [Set visible columns] (#set_visible_columns) 
 * [Set hidden columns] (#set_hidden_columns) 
 * [Working Example](#working_example)

<a name="configure_pager"/>
## Configure the pager

```php
<?php
// Set the selector of the number of items per page
$grid->setLimits(array(5 => '5', 10 => '10', 15 => '15'));

// Set the default page
$grid->setPage(2);

$grid->setSource($source);
```
<a name="add_column"/>
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

<a name="add_mass_action"/>
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

<a name="delete_mass_action"/>
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

<a name="add_row_actions"/>
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
// Specify route parameters for the edit action
$myRowAction->setRouteParameters(array('version' => 2));
$grid->addRowAction($myRowAction);
```

<a name="custom_row_actions"/>
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

<a name="init_filters"/>
## Init filters value

You can set a default value for filters.

Must be defined after the source.

```php
<?php
$grid->setSource($source);

$grid->initFilters(array('your_column_to_filtered' => 'your_init_value'));
...
```

**Note:** Keep in mind that filters are stored by default in a cookie session.
To init filters with this method, you have to delete your cookie first or restart your web browser.

<a name="manipulate_query"/>
## Manipulate the query builder

You can set a callback to manipulate the query builder.

Must be defined before the source.

```php
<?php
$source->setCallback($source::EVENT_PREPARE_QUERY, function ($query) {
	$query->setMaxResults(1);
});

$grid->setSource($source);
```

With context injection:

```php
<?php
$tableAlias = $source::TABLE_ALIAS;
$source->setCallback($source::EVENT_PREPARE_QUERY, function ($query) use ($tableAlias) {
    $query->andWhere($tableAlias . '.user = 1');
});
```
You must use "andWhere" instead of "Where" statement otherwise column filtering will not work properly.

<a name="manipulate_rows"/>
## Manipulate rows

You can set a callback to manipulate the row of the grid.

Must be defined before the source.

```php
<?php
$source->setCallback($source::EVENT_PREPARE_ROW, function ($row) {
	if ($row->getField('enabled')=='1') {
		$row->setColor('#00ff00');
	}
	
	// Don't show the row if the price is superior to 10
	if ($row->getField('price')>10) {
		return null;
	}
	
	return $row;
});

$grid->setSource($source);
```

**Note:** You can hide a row if your callback return `null`.

<a name="set_data"/>
## Set data

You can use existing data to avoid unnecessary queries.

Imagine a user with bookmarks represented by a type (youtube, twitter,...) and a link.

Current behavior to display the bookmarks of a user:

```php
<?php
public function displayBookmarksOfTheUserAction()
{
    // Get the user from context
	$user = $this->container->get('security.context')->getToken()->getUser();
	if (!is_object($user) || !$user instanceof UserInterface) {
        throw new AccessDeniedException('This user does not have access to this section.');
	}

    // Instanciate the grid
	$grid = $this->get('grid');

    // Define the source of the grid
	$source = new Entity('MyProjectMyBundle:Bookmark');

    // Add a where condition to the query to get only bookmarks of the user
	$tableAlias = $source::TABLE_ALIAS;
	$source->setCallback($source::EVENT_PREPARE_QUERY, function ($query) use ($tableAlias, $user) {
        $query->where($tableAlias . '.member = '.$user->getId());
	});

	$grid->setSource($source);

	if ($grid->isReadyForRedirect())
	{
        return new RedirectResponse($grid->getRouteUrl());
	}
	else
	{
        return $this->render('MyProjectMyBundle::my_grid.html.twig', array('data' => $grid));
	}
}
```

Bookmarks are related by the user, so you can retrieve it directly from the user and use it for the grid:

```php
<?php
public function displayBookmarksOfTheUserAction()
{
    // Get the user from context
    $user = $this->container->get('security.context')->getToken()->getUser();
    if (!is_object($user) || !$user instanceof UserInterface) {
        throw new AccessDeniedException('This user does not have access to this section.');
	}

    // Instanciate the grid
	$grid = $this->get('grid');

    // Define the source of the grid
	$grid->setSource(new Entity('MyProjectMyBundle:Bookmark'));
    
    // Get bookmarks related to the user and set the grid data 
    $grid->setData($user->getBookmarks());

	if ($grid->isReadyForRedirect())
	{
        return new RedirectResponse($grid->getRouteUrl());
	}
	else
	{
        return $this->render('MyProjectMyBundle::my_grid.html.twig', array('data' => $grid));
	}
}
```

<a name="grid_response"/>
## Grid Response helper

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

With this new feature you avoid some unnecessary queries 

<a name="set_visible_columns"/>
## Set visible columns

sets a list of columns that the grid will display

```php
<?php 
//MyEntity has A to E fields
$source = new Entity('MyProjectMyBundle:MyEntity');

$grid = $this->get('grid');

$grid->setSource($source);

//We want to display only A, C and E, setVisibleColumns sets B and D to hidden
$grid->setVisibleColumns(array('A', 'C', 'E'));

//The grid displays only A, C and E
return $gridManager->gridResponse(array('data' => $grid), 'MyProjectMyBundle::my_grid.html.twig');
```

<a name="set_hidden_columns"/>
## Set hidden columns

sets a list of columns that the grid will hide

```php
<?php 
//MyEntity has A to E fields
$source = new Entity('MyProjectMyBundle:MyEntity');

$grid = $this->get('grid');

$grid->setSource($source);

//We want to display only A, C and E, setHiddenColumns sets B and D to hidden
$grid->setHiddenColumns(array('B', 'D'));

//The grid displays only A, C and E
return $gridManager->gridResponse(array('data' => $grid), 'MyProjectMyBundle::my_grid.html.twig');
```

This method can be used with setVisibleColumns, for instance:

```php
<?php 
//MyEntity has A to E fields
$source = new Entity('MyProjectMyBundle:MyEntity');
$grid = $this->get('grid');
$grid->setSource($source);

//setVisibleColumns sets D and E to hidden
$grid->setVisibleColumns(array('A', 'B', 'C'));

//setHiddenColumns sets B and D to hidden
$grid->setHiddenColumns(array('B', 'D'));


//The grid displays A and C
return $gridManager->gridResponse(array('data' => $grid), 'MyProjectMyBundle::my_grid.html.twig');
```


<a name="working_example"/>
Working Example
----------------

```php
<?php

namespace MyProject\MyBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sorien\DataGridBundle\Grid\Source\Entity;
use Sorien\DataGridBundle\Grid\Column\TextColumn;
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
        $MyColumn = new TextColumn(array('id' => 'My Column', 'title'=>'My Column', 'size' => '54', 'sortable' => true, 'filterable' => false, 'source' => false));
        $grid->addColumn($MyColumn, 3);

        // Add a column with a rendering callback
        $MyColumn2 = new TextColumn(array('id' => 'Another Column', 'callback' => function($value, $row, $router) {
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

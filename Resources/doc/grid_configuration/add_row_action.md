Add a row action
================

A row action is an action performed on the current row. It's represented by a route to a controller with the identifier of the row.  
Row actions are all put in the same new action column at the last position of the grid.

## Usage
```php
<?php
use APY\DataGridBundle\Grid\Action\RowAction;
...
$grid->setSource($source);

$rowAction = new RowAction($title, $route, $confirm, $target, $attributes);
$grid->addRowAction($rowAction);
...
```

## Class parameters

|parameter|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|title|string|||Title of the row action|
|route|string|||Route to the row action|
|confirm|Boolean|false|true or false|Set to true to have a confirm message on click.|
|target|string|_self|_self, _blank, _parent or _top|Set the target of this action|
|attributes|array|array()||Add attributes to the anchor tag|

**Note**: Every parameter have a setter and a getter method. and others options can be set too.


## Additionnal parameters

These parameters have a setter and a getter method. 

|parameter|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|confirmMessage|string|'Do you want to '.strtolower($title).' this row?'||Confirm message on click|
|routeParameters|string or array|array()||Add additional parameters to the route.|

**Note**: For the route parameters, if you pass a column identifier instead of a key/value pair, the row action will use the value of the column of the selected row to generate its url.

## Exemple
```php
<?php
use APY\DataGridBundle\Grid\Action\RowAction;
...
$grid->setSource($source);

$rowAction = new RowAction('Delete', 'route_to_delete', true, '_self', array('class' => 'grid_delete_action'));
$grid->addRowAction($rowAction);

// Specify route parameters for the edit action
$rowAction2 = new RowAction('Edit', 'route_to_edit');
$rowAction2->setRouteParameters(array('id', 'version' => 2));
$grid->addRowAction($rowAction2);
...
```

For mapped fields, you catch a parameter with its camelCase representation. e.g. `user.information.country` turn into `userInformationCountry`.

## Exemple
```php
<?php
use APY\DataGridBundle\Grid\Action\RowAction;
...
$rowAction2 = new RowAction('Edit', 'route_to_edit');
$rowAction2->setRouteParameters(array('user.information.country'));
$grid->addRowAction($rowAction2);
...
```

```php
<?php
...
/**
 * @Route("/{userInformationCountry}", name="route_to_edit")
 * @Template
 */
public function djettePlayListShowAction($userInformationCountry)
{
    ...
}
...
```



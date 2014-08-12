Manipulate column render cell
=============================

You can set a callback to manipulate the render of a cell.  
If the callback returns nothing, the cell will be empty.

## Usage

```php
<?php
...

$grid->setSource($source);

$grid->getColumn('my_column_id')->manipulateRenderCell($callback);
...
```

## Method Column::manipulateRenderCell parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|callback|[\Closure](http://php.net/manual/en/functions.anonymous.php) or [callable](http://php.net/manual/en/language.types.callable.php)|null|Callback to manipulate rows. Null means no callback.|

## Callback parameters

|parameter|Type|Description|
|:--:|:--|:--|:--|:--|
|value|string|The value of the cell|
|row|instance of Row|The current row|
|router|instance of the router engine|The symfony router|

## Examples

```php
<?php
...
$grid->setSource($source);

$grid->getColumn('my_column_id')->manipulateRenderCell(
    function($value, $row, $router) {
        return $router->generate('_my_route', array('param' => $row->getField('column4')));
    }
);
...
```

**Note**: You can fetch hidden columns if the source attribute is set to true.

Use this method to fill an empty column:

```php
<?php
...
$grid->setSource($source);

// Add a column with a rendering callback
$MyColumn = new TextColumn(array('id' => 'Another Column'));

$MyColumn->manipulateRenderCell(function($value, $row, $router) {
    return $router->generate('_my_route', array('param' => $row->getField('column4')));}
);

$grid->addColumn($MyColumn);
...
```

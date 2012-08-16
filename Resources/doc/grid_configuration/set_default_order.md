Set the default order of the grid
====================================

You can define a default order. This order will be used on each new session of the grid.

## Usage
```php
<?php
...
// Set the source
$grid->setSource($source);

// Set the default order of the grid
$grid->setDefaultOrder($columnId, $order);
...
```

## Grid::setDefaultOrder parameters

|parameter|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|columnId|string|_none_||Identifier of the column|
|order|string|asc|asc or desc|Order of the column|

## Available Order

|Order|Meaning|
|:--:|:--|
|asc|Ascending|
|desc|Descending|

## Example
```php
<?php
...
// Set the source
$grid->setSource($source);

// Set the default order of the grid
$grid->setDefaultOrder('my_column_id', 'asc');

...
```
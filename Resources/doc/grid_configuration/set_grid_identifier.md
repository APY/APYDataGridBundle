Set the identifier of the grid
==============================

You can set the identifier of a grid to manage easily the grid with css and javascript for instance.

You have to define the identifier of a grid if you use two grids with the same source on the same page.

The grid will have the identifier grid_<grid_id> in html pages. And every request will use this variable to query the grid.

**Note:** The Identifier mustn't use special chars for url like dot (. or +)

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setId($id);
...
```
## Method parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|id|string|_none_|Identifier of the grid|

## Example

```php
<?php
...
$grid->setSource($source);

$grid->setId('user');
...
```

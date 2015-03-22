Set the order of the columns
============================

You can already define the order of the columns with the columns option of the [Source annotation](../columns_configuration/annotations/source_annotation.md).  
You can also define the order in a controller.

## Usage
```php
$grid->setColumnsOrder($columnIds, $keepOtherColumns);
```

## Grid::setColumnsOrder parameters

|parameter|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|columnIds|array|_none_|Ids of the columns|Order of the columns|
|keepOtherColumns|boolean|true|true or false|Keep or not the columns not in columnIds|

**Note**: Don't forget to keep your primary column in columnsIds if keepOtherColumns is false.

## Example

Initial columns : Column1, Column2, Column3, Column4, Column5

```php
$userColumns = array('Column2', 'Column5', 'Column1');
$grid->setColumnsOrder(userColumns);
```

The new order will be : Column2, Column5, Column1, Column3, Column4

```php
$userColumns = array('Column2', 'Column5', 'Column1');
$grid->setColumnsOrder($userColumns, false);
```

The new order will be : Column2, Column5, Column1

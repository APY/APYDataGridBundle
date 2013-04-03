Add a column
=======================

You can add a column to the grid. You can fill it with the row manipulator, in your template or tell the grid what field the column will be mapped.  
A column must be defined after the source otherwise it will always appear before the columns of the source.
If negative column numbers are used, then the column is added that far from the last column.

## Usage

```php
<?php
use APY\DataGridBundle\Grid\Column\BlankColumn;
...
$grid->setSource($source);

// create a column
$MyColumn = new BlankColumn($params);

// Add the column to the last position
$grid->addColumn($MyColumn);

// OR add this column to the third position
$grid->addColumn($MyColumn, 3);

// OR add this column to the next to last position
$grid->addColumn($MyColumn, -1);
...
```

**Note**: To keep the correct position of each column, it's better to define them in ascending order of position.

## Column parameters

See [column annotations for property](../columns_configuration/annotations/column_annotation_property.md#available-attributes)

## Grid::addColumn parameters

|parameter|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|column|instance of Column||BlankColumn(), TextColumn(), DateColumn(), BooleanColumn...||
|position|integer|0|position >= 0|0 means last position|

## Example

```php
<?php
use APY\DataGridBundle\Grid\Column\BlankColumn;
...
$grid->setSource($source);

// First parameter : Associative array of parameters (See column annotations for property) 
$MyBlankColumn = new BlankColumn(array('id' => 'myBlankColumn', 'title' => 'My Blank Column', 'size' => '54'));

// Add the column to the last position
$grid->addColumn($MyBlankColumn);

// OR add this column to the third position
$grid->addColumn($MyBlankColumn, 3);

$MyTypedColumn = new DateColumn(array('id' => 'myTypedColumn', 'title' => 'My Typed Column', 'source' => false, 'filterable' => false, 'sortable' => false));
$grid->addColumn($MyTypedColumn);

$MyMappedColumn = new DateColumn(array('id' => 'myMappedColumn', 'field' => 'myMappedColumn', 'title' => 'My Mapped Column'));
$grid->addColumn($MyMappedColumn);
...
```

**Note:** If you want to use a typed column (Date, Array, ...), this column mustn't be marked as sortable, filterable and source. Else use the BlankColumn.  
**Note²:** If you want to map your new column to a field of your source you have to set `'source' => true` and define the field attribute equal to the id attribute.


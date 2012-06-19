Add a non mapped column
=======================

You can add a empty column to the grid. You can fill it with the row manipulator or in your template.  
A column must be defined after the source otherwise it will always appear before the columns of the source.

**This column mustn't be marked as sortable, filterable and source.**

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

## Exemple

```php
<?php
use APY\DataGridBundle\Grid\Column\BlankColumn;
...
$grid->setSource($source);

// First parameter : Associative array of parameters (See column annotations for property) 
$MyColumn = new BlankColumn(array('id' => 'My Column', 'title' => 'My Column', 'type' => 'number', 'size' => '54'));

// Add the column to the last position
$grid->addColumn($MyColumn);

// OR add this column to the third position
$grid->addColumn($MyColumn, 3);
...
```

**Note**: We use a BlankColumn because this type of column is not sortable and not filterable.

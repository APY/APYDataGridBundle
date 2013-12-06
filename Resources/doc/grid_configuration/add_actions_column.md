Add multiple row actions columns
================================

You can create other columns of row actions and choose the position of these ones.

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
...
$grid->setSource($source);

// Create an Actions Column
$actionsColumn = new ActionsColumn($column, $title);
$grid->addColumn($actionsColumn, $position);

// Attach a rowAction to the Actions Column
$rowAction1 = new RowAction('Show', 'route_to_show');
$rowAction1->setColumn($column);
$grid->addRowAction($rowAction1);


// OR add a second row action directly to a new action column
$rowAction2 = new RowAction('Edit', 'route_to_edit');

$actionsColumn2 = new ActionsColumn($column, $title, array(rowAction2), $separator);
$grid->addColumn($actionsColumn2, $position2);
...
```

## Class ActionsColumn parameters

|Parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|column|string||Identifier of the column|
|title|string||Title of the column|
|rowActions|array|array()|Array of rowAction|
|separator|string|' '|The separator between each action|

**Note**: This parameter accepts HTML tags.

## Method RowAction::setColumn parameters

|Parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|column|string||Identifier of the actions column|

Example:
```php
<?php
...
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
...
$grid->setSource($source);

// Create an Actions Column
$actionsColumn = new ActionsColumn('info_column_1', 'Actions 1');
$actionsColumn->setSeparator("<br />");
$grid->addColumn($actionsColumn, 1);

// Attach a rowAction to the Actions Column
$rowAction1 = new RowAction('Show', 'route_to_show');
$rowAction1->setColumn('info_column_1');
$grid->addRowAction($rowAction1);


// OR add a second row action directly to a new action column
$rowAction2 = new RowAction('Edit', 'route_to_edit');

$actionsColumn2 = new ActionsColumn('info_column_2', 'Actions 3', array(rowAction2));
$grid->addColumn($actionsColumn2, 2);
...
```

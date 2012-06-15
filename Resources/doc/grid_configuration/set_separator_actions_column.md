Set the separator of the actions column
===============================================

## Usage

```php
<?php
...
$grid->setActionsColumnSeparator($separator);

$grid->setSource($source);
...
```

**Note**: This parameter must be defined before the source.

## Grid::setActionsColumnSize parameters

|Parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|separator|string|' '|Separator of the default actions column|

**Note**: This parameter accepts HTML tags.

## Example

```php
<?php
...
use APY\DataGridBundle\Grid\Action\RowAction;
...
$grid->setActionsColumnSeparator("<br />");

$grid->setSource($source);

// Attach a rowAction to the Actions Column
$rowAction1 = new RowAction('Show', 'route_to_show');
$grid->addRowAction($rowAction1);

$rowAction2 = new RowAction('Edit', 'route_to_edit');
$grid->addRowAction($rowAction2);

$rowAction3 = new RowAction('Delete', 'route_to_delete');
$grid->addRowAction($rowAction3);
...
```

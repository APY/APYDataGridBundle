Set the separator of the actions column
===============================================

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setActionsColumnSeparator($separator);
...
```

## Grid::setActionsColumnSize parameters

|Parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|separator|string|<br />|Separator of the default actions column|

**Note**: This parameter accepts HTML tags.

## Example

```php
<?php
...
use APY\DataGridBundle\Grid\Action\RowAction;
...
$grid->setSource($source);

$grid->setActionsColumnSeparator("<br />");

// Attach a rowAction to the Actions Column
$rowAction1 = new RowAction('Show', 'route_to_show');
$grid->addRowAction($rowAction1);

$rowAction2 = new RowAction('Edit', 'route_to_edit');
$grid->addRowAction($rowAction2);

$rowAction3 = new RowAction('Delete', 'route_to_delete');
$grid->addRowAction($rowAction3);
...
```

## Set the default separator of the actions column in your config.yml
```yml
apy_data_grid:
    actions_columns_separator: "<b> - </b>"
```

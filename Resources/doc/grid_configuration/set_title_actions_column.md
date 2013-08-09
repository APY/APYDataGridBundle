Set the title of the actions column
===========================================

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setActionsColumnTitle($title);
...
```

## Grid::setActionsColumnTitle parameters

|Parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|title|string|Actions|Title of the default actions column|

## Example

```php
<?php
...
use APY\DataGridBundle\Grid\Action\RowAction;
...
$grid->setSource($source);

$grid->setActionsColumnTitle('default_actions_column');

// Attach a rowAction to the Actions Column
$rowAction1 = new RowAction('Show', 'route_to_show');
$grid->addRowAction($rowAction1);

$rowAction2 = new RowAction('Edit', 'route_to_edit');
$grid->addRowAction($rowAction2);

$rowAction3 = new RowAction('Delete', 'route_to_delete');
$grid->addRowAction($rowAction3);
...
```

## Set the default title of the actions column in your config.yml
```yml
apy_data_grid:
    actions_columns_title: Actions
```

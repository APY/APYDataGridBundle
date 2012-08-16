Set the no data message
=======================

When you render a grid with no data in the source, the grid isn't displayed and a no data message is displayed.

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setNoDataMessage($noDataMessage);
...
```

## Grid::setNoDataMessage parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|noDataMessage|string|No data|No data message|

## Example

```php
<?php
...
$grid->setSource($source);

$grid->setNoDataMessage('There is no data!');
...
```

## Set the default no data message in your config.yml
```yml
apy_data_grid:
    no_data_message: There is no data!
```
Set the no result message
=========================

When you render a grid with no result after a filtering, a no result message is displayed in a unique row.

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setNoResultMessage($noResultMessage);
...
```

## Grid::setNoResultMessage parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|noResultMessage|string|No result|No result message|

## Example

```php
<?php
...
$grid->setSource($source);

$grid->setNoResultMessage('There is no result!');
...
```

## Set the default no result message in your config.yml
```yml
apy_data_grid:
    no_result_message: There is no result!
```
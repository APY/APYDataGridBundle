Set the persistence of the grid
===============================

By default, filters, page and order are reset when you quit the page where your grid is.

If you set to true the persistence, its parameters are kept until you close your web browser or you kill yourself the cookie of the session.
But don't forget to define an different identifier of your grids else your sessions will be reset by another grid with the same identifier.

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setPersistence($persistence);
...
```
## Method parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|persistence|boolean|false|Persistence of the grid|

## Example

```php
<?php
...
$grid->setSource($source);

$grid->setPersistence(true);
...
```

## Set the default persistence in your config.yml
```yml
apy_data_grid:
    persistence: true
```
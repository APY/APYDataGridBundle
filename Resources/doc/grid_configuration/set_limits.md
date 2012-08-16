Define the selector of the number of items per page
===================================================

Default limits = array(20 => '20', 50 => '50', 100 => '100')

## Example
```php
<?php
...
// Set the source
$grid->setSource($source);

// Set the selector of the number of items per page
$grid->setLimits(array(5, 10, 15));

// OR with only one value
$grid->setLimits(50);

// OR with labels
$grid->setLimits(array(5 => 'five', 10 => 'ten', 15 => 'fifteen'));
...
```

## Method parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|limits|string or array||Values of items per page|

## Set default limits in your config.yml
```yml
apy_data_grid:
    limits: {5: 'five', 10: 'ten', 15: 'fifteen'}
```
Or
```yml
apy_data_grid:
    limits: [5, 10, 15]
```
Or
```yml
apy_data_grid:
    limits: 5
```
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

Note that the selector and accompanying pager will not appear if the total number of rows in the grid is less than the
minimum pager limit.  For example, if the minimum pager limit is 20 and the number of results in the grid is 10, then
the the limit selector and pager will not be rendered.

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
Set permanent filters of the grid
===============================

You can define permanent filters. These values will be used every time and the filter part will be disable for columns which have a permanent filter.

## Usage

```php
<?php
...
// Set the source
$grid->setSource($source);

// Set permanent filters of the grid
$grid->setPermanentFilters($filters);
...
```

## Grid::setPermanentFilters parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|filters|array|array()|Array of array or string pair|

## Values for the filters parameter

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|operator|string|default operator of the column|Operator used to filter|
|from|string|null|The value of the filter or the _from_ value for the between operators|
|to|string|null|The _to_ value for the between operators|

**Note**: If these three parameters are not defined and only a string value is defined, this value will be attributed to the _from_ value of the filter and will use the default operator of the column.

## Available Operators

|Operator|Meaning|
|:--:|:--|
|eq|Equals|
|neq|Not equal to|
|lt|Lower than|
|lte|Lower than or equal to|
|gt|Greater than|
|gte|Greater than or equal to|
|like|Contains (case insensitive)|
|nlike|Not contain (case insensitive)|
|rlike|Starts with (case insensitive)|
|llike|Ends with (case insensitive)|
|slike|Contains|
|nslike|Not contain|
|rslike|Starts with|
|lslike|Ends with|
|btw|Between exclusive|
|btwe|Between inclusive|
|isNull|Is not defined|
|isNotNull|Is defined|

## Example

```php
<?php
...
// Set the source
$grid->setSource($source);

// Set default filters of the grid
$grid->setPermanentFilters(array(
    'your_column_to_filter1' => 'your_init_value1', // Use the default operator of the column
    'your_column_to_filter1' => array('from' => 'your_init_value1'), // Use the default operator of the column
    'your_column_to_filter2' => array('operator' => 'eq', 'from' => 'your_init_value_from2'), // Define an operator
    'your_column_to_filter3' => array('from' => 'your_init_value_from3', 'to' => 'your_init_value_to3'), // Range filter with the default operator 'btw'
    'your_column_to_filter4' => array('operator' => 'btw', 'from' => 'your_init_value_from4', 'to' => 'your_init_value_to4') // Range filter with the operator 'btw'
    'your_column_to_filter5' => array('operator' => 'isNull') // isNull operator
));
...
```

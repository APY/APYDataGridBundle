Manipulate rows data
====================

You can set a callback to manipulate the row of the grid.  
If the callback returns `null` or don't return the row, the row won't be displayed.

## Usage
```php
<?php
...
$source->manipulateRow($callback);

$grid->setSource($source);
...
```

## Method Source::manipulateRow parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|callback|[\Closure](http://php.net/manual/en/functions.anonymous.php) or [callable](http://php.net/manual/en/language.types.callable.php)|null|Callback to manipulate rows. Null means no callback.|

## Callback parameters

|parameter|Type|Description|
|:--:|:--|:--|:--|:--|
|row|instance of Row|The current row to manipulate|

## Examples

```php
<?php
...
$source->manipulateRow(
    function ($row)
    {
        if ($row->getField('enabled') == '1') {
            $row->setClass('border-enabled');  // add a css class to the <tr> tag
            $row->setColor('#00ff00');  // set background-color as inline style
        }
        
        // Don't show the row if the price is greater than 10
        if ($row->getField('price')>10) {
            return null;
        }
        
        return $row;
    }
);

$grid->setSource($source);
...
```

If you want to pass some context parameters:
```php
<?php
...
$maxPrice = 10;
$soldOutLabel = 'Sold out';

$source->manipulateRow(
    function ($row) use ($maxPrice, $soldOutLabel)
    {
        // Don't show the row if the price is greater than $maxPrice
        if ($row->getField('price') > $maxPrice) {
            return null;
        }
        
        // Change the ouput of the column quantity if anarticle is sold out
        if ($row->getField('quantity') == 0) {
            $row->setField('quantity', $soldOutLabel);
        }
        
        return $row;
    }
);

$grid->setSource($source);
...
```

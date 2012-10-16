Manipulate row action rendering
=============================

You can set a callback to manipulate the rendering of an action.
The callback **MUST** return the action or return false, to prevent the action from rendering.

## Usage

```php
<?php
...
$rowAction->manipulateRender(function($action, $row) {
    if($row->getField('id') == 10) {
        return $action;
    } else {
        return false;
    }
});
...
```

## Method RowAction::manipulateRender parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|callback|[\Closure](http://php.net/manual/en/functions.anonymous.php) or [callable](http://php.net/manual/en/language.types.callable.php)|null|Callback to manipulate action rendering. Null means no callback.|

## Callback parameters

|parameter|Type|Description|
|:--:|:--|:--|:--|:--|
|action|instance of RowAction|The action|
|row|instance of Row|The current row|

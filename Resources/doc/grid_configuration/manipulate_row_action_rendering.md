Manipulate row action rendering
=============================

You can set a callback to manipulate the rendering of an action.
If the callback returns `null` or don't return the action, the action won't be displayed.

## Usage

```php
<?php
...
$rowAction->manipulateRender($callback);

$grid->addRowAction($rowAction);
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

## Action disabling

The action can be disabled using the manipulate render callback. See the example.
If the action is disabled, only its title is displayed, with all additional attributes used.

## Example

```php
<?php
...
$rowAction->manipulateRender(
    function ($action, $row)
    {
        if ($row->getField('quantity') == 0) {
            $action->setTitle('Sold out');
        }

        if ($row->getField('price') > 20) {
            return null;
        }

        if ($row->getField('enabled') == false) {
            $action->setEnabled(false);
        }

        return $action;
    }
);

$grid->addRowAction($rowAction);
...
```
Add a mass action
=================

A mass action is like a row action but over many lines at the same time.
It calls a function with an array of the selected rows as first argument.
When you define mass actions, a selector appears and a new column of checkboxes is displayed on the left of the grid.

## Usage
```php
<?php
use APY\DataGridBundle\Grid\Action\MassAction;
...
$grid->setSource($source);

$massAction = new MassAction($title, $callback, $confirm, $parameters, $role);

$grid->addMassAction($massAction);
...
```

## Class parameters

|parameter|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|title|string|||Title of the mass action|
|callback|[\Closure](http://php.net/manual/en/functions.anonymous.php) or [callable](http://php.net/manual/en/language.types.callable.php) or a controller |null||Callback to the mass action|
|confirm|Boolean|false|true or false|Set to true to have a confirm message on select. (Not implemented)|
|parameters|array|array()||Add parameters for the mass action render|
|role|mixed|null|A symfony role|Don't add this mass action if the access isn't granted for the defined role(s)|

**Note**: Every parameter have a setter and a getter method. and others options can be set too.

## Example
```php
<?php
use APY\DataGridBundle\Grid\Action\MassAction;
...
$grid->setSource($source);

// Static class method call
$yourMassAction = new MassAction('Action 1', 'MyProject\MyBundle\Controller\DefaultController::myStaticMethod');
// OR
$yourMassAction = new MassAction('Action 1', array('MyProject\MyBundle\Controller\DefaultController','myStaticMethod'));

$grid->addMassAction($yourMassAction);

// Object method call
$yourMassAction2 = new MassAction('Action 2', array($obj,'myMethod'));

$grid->addMassAction($yourMassAction2);

// Controller call (Forwarding)
$yourMassAction3 = new MassAction('Action 3', 'AcmeHelloBundle:Hello:fancy');

$grid->addMassAction($yourMassAction3);
...
```

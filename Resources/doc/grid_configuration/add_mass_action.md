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

## Examples

### Callback and closure

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

// Closure
$yourMassAction3 = new MassAction('Action 3', function($ids, $selectAll, $session, $parameters) { ... });

$grid->addMassAction($yourMassAction3);
...
```

```
// Your static method (DefaultController)
function myStaticMethod($primaryKeys, $allPrimaryKeys, $session, $params)
{

}
```

#### With additionals parameters

```php
<?php
use APY\DataGridBundle\Grid\Action\MassAction;
...
$grid->setSource($source);

// Static class method call
$yourMassAction = new MassAction('Action 1', 'MyProject\MyBundle\Controller\DefaultController::myStaticMethod');

$yourMassAction->setParameters(array('param1' => $var1, 'param2' => $var2));

$grid->addMassAction($yourMassAction);
...
```

```
// Your static method (DefaultController)
function myStaticMethod($primaryKeys, $allPrimaryKeys, $session, $params)
{
    $param1 = params['param1'];
    $param2 = params['param2'];
}
```

### Controller 

```php
<?php
use APY\DataGridBundle\Grid\Action\MassAction;
...
$grid->setSource($source);

$yourMassAction4 = new MassAction('Action 4', 'AcmeHelloBundle:Hello:fancy');

$grid->addMassAction($yourMassAction4);
...
```

```
// Your action controller (HelloController)
function fancyAction($primaryKeys, $allPrimaryKeys)
{

}
```

#### With additionals parameters

```php
$yourMassAction5 = new MassAction('Action 5', 'AcmeHelloBundle:Hello:fancy');

$yourMassAction5->setParameters(array('param1' => $var1, 'param2' => $var2));

$grid->addMassAction($yourMassAction5);
...
```

```
// Your action controller (HelloController)
function fancyAction($primaryKeys, $allPrimaryKeys, $param1, $param2)
{

}
```

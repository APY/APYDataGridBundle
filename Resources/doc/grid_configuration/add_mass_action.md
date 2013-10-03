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
|callback|[\Closure](http://php.net/manual/en/functions.anonymous.php) or [callable](http://php.net/manual/en/language.types.callable.php) or a controller |null||Callback to the mass action (see examples below)|
|confirm|Boolean|false|true or false|Set to true to have a confirm message on select. (Not implemented)|
|parameters|array|array()||Add parameters for the mass action render|
|role|mixed|null|A symfony role|Don't add this mass action if the access isn't granted for the defined role(s)|

**Note**: Every parameter has a setter and a getter method. and others options can be set too.

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
$yourMassAction3 = new MassAction('Action 3', function($primaryKeys, $allPrimaryKeys, $session, $parameters) { ... });

$grid->addMassAction($yourMassAction3);
...
```

```
// Your static method (DefaultController)
static public function myStaticMethod($primaryKeys, $allPrimaryKeys, $session, $parameters)
{

}
```

##### With additionals parameters

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
static public function myStaticMethod($primaryKeys, $allPrimaryKeys, $session, $parameters)
{
    $param1 = $parameters['param1'];
    $param2 = $parameters['param2'];
}
```

**Note:** A Callback or a closure will return to the grid display.

##### Forwarding

If the Callback or the closure return a Response object, this response will be displayed.

```
// Your static method (DefaultController)
static public function myStaticMethod($primaryKeys, $allPrimaryKeys, $session, $parameters)
{
    return new Response(...);
    // return new RedirectResponse($this->generateUrl('homepage'));
}
```


### Controller

**Note:** A controller callback will forward to the specified controller.

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
	return new Response(...);
}
```

##### With additionals parameters

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
	return new Response(...);
}
```

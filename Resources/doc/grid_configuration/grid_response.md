Grid Response helper
====================

The getGridResponse method is an helper which manage the redirection, export and the rendering of the grid.  
For the rendering, the grid data is automatically passed to the parameters of the view with the identifier `grid`.

## Usage

#### Before:

```php
<?php
...
$grid->setSource($source);

if ($grid->isReadyForRedirect()) {
    if ($grid->isReadyForExport()) {
        return $grid->getExportResponse();
    }

    return new RedirectResponse($grid->getRouteUrl());
} else {
    return $this->render($view, $parameters, $response);
}
...
```

#### After:

```php
<?php
...
$grid->setSource($source);

return $grid->getGridResponse($view, $parameters, $response);
...
```

## Method parameters

|Parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|view|string|null|The view name|
|parameters|array|array()|An array of parameters to pass to the view|
|response|Response|null|A response instance|

**Note**: If you use the @Template annotation, you can define the parameters parameter in the first position (See the third example).

## Examples

#### With the @template annotation and without additionnal parameters

```php
<?php
...
$grid->setSource($source);

return $grid->getGridResponse();
...
```

#### With template and parameters

```php
<?php
...
$grid->setSource($source);

return $grid->getGridResponse('MyProjectMyBundle::my_grid.html.twig', array('param2' => $param2));
...
```

#### With the @template annotation and additionnal parameters

```php
<?php
...
$grid->setSource($source);

return $grid->getGridResponse(array('param2' => $param2));
...
```

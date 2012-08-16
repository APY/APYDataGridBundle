Set the route of the grid
=========================

The route of a grid is automatically retrieved from the request. But when you render a controller which contains a grid from twig, the route cannot be retrieved so you have to define it.

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setRouteUrl($routeUrl);
...
```
## Method parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|
|routeUrl|string|_none_|Url of the grid|

## Example

```php
<?php
namespace MyProject\MyBundle\Controller;
...
class DefaultController extends Controller
{
    /**
     * @Route("/grid", name="my_grid_route")
     */
    public function gridAction()
    {
        $source = new Entity('MyProjectMyBundle:User');
        
        $grid->setSource($source);

        $grid->setRouteUrl($this->generateUrl('my_grid_route'));
        
        return $grid->getGridResponse('MyProjectMyBundle::grid.html.twig');
    }
}
...
```

In a twig template:

```django
{% render 'MyProjectMyBundle:Default:grid' %}
```
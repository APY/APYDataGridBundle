Render the grid
================

## Usage

Pass the $grid object to the view and call your grid render in your template.  This will automatically populate a
Twig variable ```grid```.

```php
<?php
...
$grid = $this->get('grid');

$grid->setSource($source);

return $grid->getGridResponse('MyProjectMyBundle::my_grid.html.twig');
...
```

And the template

```janjo
<!-- MyProjectMyBundle::grid.html.twig -->

{{ grid(grid, theme, id, params) }}
```

## grid function parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|grid|APY/DataGridBundle/Grid/Grid||The grid object|
|theme|string|APYDataGridBundle::blocks.html.twig|Template used to render the grid|
|id|string|_none_|Set the identifier of the grid.|
|params|array|array()|Additional parameters passed to each block.|

## Exemple

```janjo
<!-- MyProjectMyBundle::grid.html.twig -->

{{ grid(grid) }}
...
```

## Override the getGridResponse function

See [Grid Response helper](../grid_configuration/grid_response.md) for a detailed outline of ```getGridResponse```.

Example with two grids:

```php
<?php
...
if ($grid->isReadyForRedirect()) {
    return $grid->getGridResponse();
} elseif ($grid2->isReadyForRedirect()) {
    return $grid2->getGridResponse();
} else {

    // Your code

    return $this->render('MyProjectMyBundle::my_grid.html.twig', array('grid' => $grid, 'grid2' => $grid2));
}
...
```

**Note:** GridResponse parameters are useless in this case and exports are managed directly in the getGridResponse function.

## _self template

If you want to override blocks inside current template you can use `_self` parameter in grid template definition.  
Current template will automatically extended from base block template

```html
<!-- MyProjectMyBundle::my_grid.html.twig -->
{{ grid(data, _self, 'custom_grid_id') }}

{% block grid_pager %}{% endblock grid_pager %}
```

**Note**: Blocks have to be define after the call of the grid.
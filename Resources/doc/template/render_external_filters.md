Render external filters
=======================

## Usage

Pass the $grid object to the view and call your grid render in your template.

```php
<?php
...
$grid = $this->get('grid');

return $grid->getGridResponse('MyProjectMyBundle::my_grid.html.twig');
...
```

And the template

```janjo
<!-- MyProjectMyBundle::grid.html.twig -->

{{ grid_search(grid, theme, id, params) }}
...
```


#### grid_search function parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|grid|APY/DataGridBundle/Grid/Grid||The grid object|
|theme|string|APYDataGridBundle::blocks.html.twig|Template used to render the filters blocks|
|id|string|_none_|Set the identifier of the grid.|
|params|array|array()|Additional parameters passed to each block.|

**Note**: You have to define the same `id` in this function and in the grid function. Same thing with the `param` argument if you use additionnal parameters in the rendering of the filters.

#### Example

```janjo
<!-- MyProjectMyBundle::grid.html.twig -->

{{ grid_search(grid) }}

{{ grid(grid) }}
...
```

**Note**: You can use a different template for the external filters but only the filters blocks can be useful.

#### Hide the grid filters

If you don't want to show the filter in the grid columns, you can disable the grid_filters blocks with an external template.

`{{ grid(grid, 'MyProjectMyBundle::grid.html.twig') }}`

And in your MyProjectMyBundle::grid.html.twig template

```janjo
{% block grid_filters %}{% endblock %}
```

Or directly use the same template :

```janjo
{{ grid_search(grid) }}

{{ grid(grid, _self) }}

{% block grid_filters %}{% endblock %}
```
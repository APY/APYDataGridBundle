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
|grid|string||The grid object|
|theme|string|APYDataGridBundle::blocks.html.twig|Temaplate used to render the grid|
|id|string|_none_|Set the identifier of the grid.|
|params|array|array()|Additional parameters passed to each block.|

#### Exemple

Disable the block grid_filters.

```janjo
<!-- MyProjectMyBundle::grid.html.twig -->

{{ grid_search(grid) }}

{{ grid(grid) }}
...
```

**Note**: You can use a different template for the external filters.
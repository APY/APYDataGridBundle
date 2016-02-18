# Display the Grid (Twig template)

## Usage

Pass the $grid object to the view and call your grid render in your template.  This will automatically populate a
Twig variable ```grid```.

```php
<?php
class DefaultController extends Controller
{
	public function myGridAction()
	{
		// [...]
		$grid = $this->get('grid');

		$grid->setSource($source);

		return $grid->getGridResponse('MyProjectMyBundle::my_grid.html.twig');
	}
	// [...]
}
```

And the Twig template

```djanjo
<!-- MyProjectMyBundle::grid.html.twig -->

{{ grid(grid, theme, id, params) }}
```

## Grid Function Parameters Reference

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|grid|APY/DataGridBundle/Grid/Grid||The grid object|
|theme|string|Template defined in configuration ([see here](overriding_internal_blocks.md#external-template))|Template used to render the grid|
|id|string|_none_|Set the identifier of the grid.|
|params|array|array()|Additional parameters passed to each block.|

## Overriding the getGridResponse function

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

## Learn more about advanced features and usages

* [Display an ajax grid](render_an_ajax_grid.md)
* [Cell rendering](cell_rendering.md)
* [Filter rendering](filter_rendering.md)
* [Overriding internal blocks](overriding_internal_blocks.md)
* [Display an external filters box](render_external_filters.md)
* [Display a pagerfanta pager](render_pagerfanta_pager.md)
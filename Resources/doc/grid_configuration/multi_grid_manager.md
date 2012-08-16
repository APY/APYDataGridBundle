Handle multiple grids on the same page
=========================================

There is an example of how to fully manage grids in the same controller:

```php
<?php
...
$grid = $this->get('grid');
$grid2 = $this->get('grid');

$grid->setSource($source1);
$grid2->setSource($source2);

if ($grid->isReadyForRedirect() || $grid2->isReadyForRedirect() )
{
    if ($grid->isReadyForExport())
	{
		return $grid->getExportResponse();
	}
	
	if ($grid2->isReadyForExport())
	{
		return $grid2->getExportResponse();
	}
	
	// Url is the same for the grids
    return new RedirectResponse($grid->getRouteUrl());
}
else
{
    return $this->render('MyProjectMyBundle::my_grid.html.twig', array('grid' => $grid, 'grid2' => $grid2));
}
```

But you'll have a grid collision if you use the same source for your grids with the same columns.

## Grids collision

If you use the same source for two grids, you have to define a identifier for your grids

```php
<?php
...
$grid = $this->get('grid');
$grid2 = $this->get('grid');

$grid->setId("first");
$grid->setSource($source1);

$grid2->setId("second");
$grid2->setSource($source1);
...
```

## Grid manager

To easily manage your grids, you can use the grid manager.

```php
<?php
$gridManager = $this->get('grid.manager');

$grid = $gridManager->createGrid();
$grid->setSource($source1);

$grid2 = $gridManager->createGrid();
$grid2->setSource($source2);

if ($gridManager->isReadyForRedirect())
{
    if ($gridManager->isReadyForExport())
	{
		return $gridManager->getExportResponse();
	}
	
    return new RedirectResponse($gridManager->getRouteUrl());
}
else
{
    return $this->render('MyProjectMyBundle::my_grid.html.twig', array('grid' => $grid, 'grid2' => $grid2));
}

```

A getGridManagerResponse method is also available which manage the redirection, export and the rendering

```php
<?php
...
$gridManager = $this->get('grid.manager');

$grid = $gridManager->createGrid();
$grid->setId('first');
$grid->setSource($source1);

$grid2 = $gridManager->createGrid('second'); // same as $grid2->setId('second');
$grid2->setSource($source1);

return $gridManager->getGridManagerResponse('MyProjectMyBundle::my_grid.html.twig');
...
```

**Note**: For the rendering, grids data are automatically passed to the parameters of the view with identifiers `grid1`, `grid2`, `grid3`, ....

## Method parameters

See [Grid Response](grid_response.md#method_parameters).

## Override the getGridManagerResponse function

Example with two grids:

```php
<?php
...
if ($gridManager->isReadyForRedirect()) {
    return $gridManager->getGridManagerResponse();
} else {

    // Your code

    return $this->render('MyProjectMyBundle::my_grid.html.twig', array('grid' => $grid, 'grid2' => $grid2));
}
...
```

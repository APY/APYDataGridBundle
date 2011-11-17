## Handle multiple grids on the same page
=========================================

You can set the identifier of your grid if you have more than one grid on your page.

```php
<?php
$grid = $this->get('grid');
$grid2 = $this->get('grid'); // or clone $grid;
$grid3 = $this->get('grid'); // or clone $grid;

$grid->setSource(new Entity('MyProjectMyBundle:MyEntity1'));

$grid2->setSource(new Entity('MyProjectMyBundle:MyEntity2'));

$grid3->setSource(new Entity('MyProjectMyBundle:MyEntity3'));

if ($grid->isReadyForRedirect() || $grid2->isReadyForRedirect() || $grid3->isReadyForRedirect() )
{
    // Data are stored, do redirect to prevent multiple post requests
    // Route is the same for all grid
    return new RedirectResponse($grid->getRouteUrl());
}
else
{
    return $this->render('MyProjectMyBundle::my_grid.html.twig', array('data' => $grid, 'data2' => $grid2, 'data3' => $grid3));
}
```

## Grids collision

If you use the same entity for two grids, you have to define a Identifier for your grids

```php
<?php
$grid = $this->get('grid');
$grid2 = $this->get('grid'); // or clone $grid;
$grid3 = $this->get('grid'); // or clone $grid;

$grid->setId("first");
$grid->setSource(new Entity('MyProjectMyBundle:MyEntity1'));

$grid2->setId("second");
$grid2->setSource(new Entity('MyProjectMyBundle:MyEntity1'));

$grid3->setSource(new Entity('MyProjectMyBundle:MyEntity3'));

...

```

## Grid manager

To easily manage your grids, you can use the grid manager.

```php
<?php
$gridManager = $this->get('grid.manager');

$grid = $gridManager->createGrid();
$grid->setSource(new Entity('MyProjectMyBundle:MyEntity1'));

$grid2 = $gridManager->createGrid();
$grid2->setSource(new Entity('MyProjectMyBundle:MyEntity2'));

$grid3 = $gridManager->createGrid();
$grid3->setSource(new Entity('MyProjectMyBundle:MyEntity3'));

if ($gridManager->isReadyForRedirect())
{
    // Data are stored, do redirect to prevent multiple post requests
    return new RedirectResponse($gridManager->getRouteUrl());
}
else
{
    return $this->render('MyProjectMyBundle::my_grid.html.twig', array('data' => $grid, 'data2' => $grid2, 'data3' => $grid3));
}

```

A gridManagerResponse method is also available which handle the redirection and the rendering

```php
<?php
$gridManager = $this->get('grid.manager');

$grid = $gridManager->createGrid();
$grid->setId("first");
$grid->setSource(new Entity('MyProjectMyBundle:MyEntity1'));

$grid2 = $gridManager->createGrid();
$grid2->setId("second");
$grid2->setSource(new Entity('MyProjectMyBundle:MyEntity2'));

$grid3 = $gridManager->createGrid();
$grid3->setId("third");
$grid3->setSource(new Entity('MyProjectMyBundle:MyEntity2'));

return $gridManager->gridManagerResponse(array('data' => $grid, 'data2' => $grid2, 'data3' => $grid3), 'MyProjectMyBundle::my_grid.html.twig');

```

**Note:** Input arguments of gridManagerResponse are reverse. If you use the @Template annotation, don't define a template view.

```php
<?php
...
return $gridManager->gridManagerResponse(array('data' => $grid, 'data2' => $grid2, 'data3' => $grid3));

```
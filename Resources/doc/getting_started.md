Getting Started With APYDataGridBundle
======================================

## Choose your source of data

You can choose between an [Entity (ORM)](source/entity_source.md) or a [Vector (Array)](source/vector_source.md) source.

#### [Entity (ORM)](source/entity_source.md)

```php
<?php
// MyProject\MyBundle\DefaultController.php
namespace MyProject\MyBundle\Controller;

use APY\DataGridBundle\Grid\Source\Entity;

class DefaultController extends Controller
{
    public function myGridAction()
    {
        // Creates simple grid based on your entity (ORM)
        $source = new Entity('MyProjectMyBundle:MyEntity');
        ...
    }
}
```

#### [Vector (Array)](source/vector_source.md)

```php
<?php
// MyProject\MyBundle\DefaultController.php
namespace MyProject\MyBundle\Controller;

use APY\DataGridBundle\Grid\Source\Vector;

class DefaultController extends Controller
{
    public function myGridAction()
    {
        $data = array(
            array(
                'id' => 1,
                'title' => 'book1',
                'publication' => '2012-04-06'
            ),
            array(
                'id' => 2,
                'title' => 'book2',
                'publication' => 'Apr. 6, 2012'
            ),
        );

        // Creates simple grid based on your data
        $source = new Vector($data);
        ...
    }
}
```

## Get a grid instance

```php
<?php
public function myGridAction()
{
    ...
    $grid = $this->get('grid');
    ...
}
```

## Attach the source to the grid

```php
<?php
public function myGridAction()
{
    ...
    $grid = $this->get('grid');

    $grid->setSource($source);
    ...
}
```

## Configuration of the grid

```php
<?php
public function myGridAction()
{
    ...
    $grid->setSource($source);

    // Set the identifier of the grid
    // Add a column
    // Show/Hide columns
    // Set default filters
    // Set the default order
    // Set the default page
    // Set max results
    // Set prefix titles
    // Add mass actions
    // Add row actions
    // Manipulate the query builder
    // Manipulate rows data
    // Manipulate columns
    // Manipulate column render cell
    // Set items per page selector
    // Set the data for Entity and Document sources
    // Exports
    ...
}
```

## Return the grid to the template

```php
<?php
public function myGridAction()
{
    ...
    $grid->setSource($source);

    // Prepare data and the grid

    $grid->isReadyForRedirect();

    // Configuration of the grid

    return $this->render('MyProjectMyBundle::grid.html.twig', array('grid' => $grid));
    ...
}
```

## Manage the grid redirection, exports and the response of the controller

```php
<?php
public function myGridAction()
{
    ...
    $grid->setSource($source);

    // Configuration of the grid

    return $grid->getGridResponse('MyProjectMyBundle::grid.html.twig');
    ...
}
```

See [grid response](grid_configuration/grid_response.md) for more informations.

## Complete example with an entity source

```php
<?php
// MyProject\MyBundle\DefaultController.php
namespace MyProject\MyBundle\Controller;

use APY\DataGridBundle\Grid\Source\Entity;

class DefaultController extends Controller
{
    public function myGridAction()
    {
        // Creates simple grid based on your entity (ORM)
        $source = new Entity('MyProjectMyBundle:MyEntity');

        // Get a grid instance
        $grid = $this->get('grid');

        // Attach the source to the grid
        $grid->setSource($source);

        // Configuration of the grid

        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('MyProjectMyBundle::grid.html.twig');
    }
}
```


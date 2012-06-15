Getting Started With DataGridBundle
===================================

## Choose your source of data

You can choose between an [Entity (ORM)](source/entity.md), a [Document (ODM)](source/document.md) or a [Vector (Array)](source/source.md) source.

#### [Entity (ORM)](source/entity.md)

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

#### [Document (ODM)](source/document.md)

```php
<?php
// MyProject\MyBundle\DefaultController.php
namespace MyProject\MyBundle\Controller;

use APY\DataGridBundle\Grid\Source\Document;

class DefaultController extends Controller
{
    public function myGridAction()
    {
        // Creates simple grid based on your document (ODM)
        $source = new Document('MyProjectMyBundle:MyDocument');
        ...
    }
}
```

#### [Vector (Array)](source/source.md)

```php
<?php
// MyProject\MyBundle\DefaultController.php
namespace MyProject\MyBundle\Controller;

use APY\DataGridBundle\Grid\Source\Document;

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

## Configuration before attached the source to the grid

```php
<?php
public function myGridAction()
{
    ...
    $grid = $this->get('grid');
    
    // Add mass actions
	// Add row actions
    // Manipulate the query builder
    // Manipulate rows data
    // Manipulate columns
    // Set the default page
    // Set items per page selector
    // Set max results
    // Set prefix titles
	// Set the data for Entity and Document sources
	// Exports
    
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
    
    // Configuration before attached the source to the grid
    
    $grid->setSource($source);
    ...
}
```

## Configuration after attached the source to the grid

```php
<?php
public function myGridAction()
{
    ...
    // Configuration before attached the source to the grid
    
    $grid->setSource($source);
    
    // Add row actions
    // Add a column
    // Show/Hide columns
    // Manipulate column render cell
    // Set default filters
    // Set the default order
    // Manipulate columns
    // Set max results
    // Set prefix titles
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

    // Configuration after attached the source to the grid

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

    // Configuration after attached the source to the grid

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

        // Configuration before attached the source to the grid

        // Attach the source to the grid
        $grid->setSource($source);

        // Configuration after attached the source to the grid

        // Manage the grid redirection, exports and the response of the controller
        return $grid->getGridResponse('MyProjectMyBundle::grid.html.twig');
    }
}
```


Datagrid for Symfony2 inspired by Zfdatagrid and Magento Grid.  
This bundle was initiated by Stanislav Turza (Sorien).

**Version**: 2.1-dev  
**Compatibility**: Symfony >= 2.0.0, Twig >= 1.5.0

[![Build Status](https://secure.travis-ci.org/Abhoryo/APYDataGridBundle.png?branch=master)](http://travis-ci.org/Abhoryo/APYDataGridBundle)

See [CHANGELOG](https://github.com/Abhoryo/APYDataGridBundle/blob/master/CHANGELOG.md) and [UPGRADE 2.0](https://github.com/Abhoryo/APYDataGridBundle/blob/master/UPGRADE-2.0.md)

## Features

- Supports Entity (ORM), Document (ODM) and Vector (Array) sources
- Sortable and Filterable with operators (Comparison operators, range, starts/ends with, (not) contains, is (not) defined, regex)
- Auto-typing columns (Text, Number, Boolean, Array, DateTime, Date, ...)
- Locale support for DateTime, Date and Number columns (Decimal, Currency, Percent, Duration, Scientific, Spell out)
- Input, Select, checkbox and radio button filters filled with the data of the grid or an array of values
- Export (CSV, Excel, _PDF_, XML, JSON, HTML, ...)
- Mass actions
- Row actions
- Supports mapped fields with Entity source
- Securing the columns, actions and export with security roles
- Annotations and PHP configuration
- External filters box
- Ajax loading
- Pagination (You can also use Pagerfanta)
- Column width and column align
- Prefix translated titles
- Grid manager for multi-grid on the same page
- Groups configuration for ORM and ODM sources
- Easy templates overriding (twig)
- Custom columns and filters creation
- ...

## Documentation

See the [summary](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/summary.md).

## Screenshot

Full example with this [CSS style file](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/grid_configuration/working_example.css):

![test](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/images/screenshot_full.png?raw=true)

Simple example with the external filter box in english:

![test](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/images/screenshot_en.png?raw=true)

Same example in french:

![test](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/images/screenshot_fr.png?raw=true)

Data used in these screenshots (this is a phpMyAdmin screenshot):

![test](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/images/screenshot_database.png?raw=true)

## Simple grid with an ORM source

```php
<?php
namespace MyProject\MyBundle\Controller;

use APY\DataGridBundle\Grid\Source\Entity;

class DefaultController extends Controller
{
	public function myGridAction()
	{
		// Creates a simple grid based on your entity (ORM)
		$source = new Entity('MyProjectMyBundle:MyEntity');

		// Get a Grid instance
		$grid = $this->get('grid');

		// Attach the source to the grid
		$grid->setSource($source);

		// Return the response of the grid to the template
		return $grid->getGridResponse('MyProjectMyBundle::myGrid.html.twig');
	}
}
```

#### Simple configuration of the grid in the entity

```php
<?php
namespace MyProject\MyBundle\Entity

use Doctrine\ORM\Mapping as ORM;
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * @GRID\Source(columns="id, my_datetime")
 */
class MyEntity
{
	/*
	 * @ORM\Column(type="integer")
	 */
	protected $id;

	/*
	 * @ORM\Column(type="datetime")
	 */
	protected $my_datetime;
}
```

#### Display the grid in a twig template

```php
<?php
<!-- MyProject\MyBundle\Resources\views\myGrid.html.twig -->

{{ grid(grid) }}
```

And clear your cache.

## Special thanks to all contributors

Abhoryo, golovanov, touchdesign, Spea, nurikabe, print, Gregory McLean, centove, lstrojny, Benedikt Wolters, Martin Parsiegla, evan and all bug reporters

## Todo list

See this [Pull Request](https://github.com/Abhoryo/APYDataGridBundle/issues/121)

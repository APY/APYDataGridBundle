
# APYDataGrid Bundle

This **Symfony Bundle** allows you to create wonderful grid based on data or entities of your projet.

[![Build Status](https://secure.travis-ci.org/APY/APYDataGridBundle.png?branch=master)](http://travis-ci.org/APY/APYDataGridBundle) [![Coverage Status](https://coveralls.io/repos/github/APY/APYDataGridBundle/badge.svg?branch=test-improvement)](https://coveralls.io/github/APY/APYDataGridBundle?branch=test-improvement)

## Features
This bundle allow you to create listing with many features that you can expect : 
- Various data sources : supports **Entity** (ORM), **Document** (ODM) and **Vector** (Array) sources
- Data manipulation : **Sortable** and **Filterable** with many operators 
- Auto-typing columns (Text, Number, Boolean, Array, DateTime, Date, ...)
- Locale support for columns and data (DateTime, Date and Number columns)
- Input, Select, checkbox and radio button filters filled with the data of the grid or an array of values
- Export (CSV, Excel, _PDF_, XML, JSON, HTML, ...)
- Mass actions, Row actions
- Supports mapped fields with Entity source
- Securing the columns, actions and export with security roles
- Annotations and PHP configuration
- External filters box
- Ajax loading
- Pagination (You can also use Pagerfanta)
- Grid manager for multi-grid on the same page
- Groups configuration for ORM and ODM sources
- Easy templates overriding (Twig)
- Custom columns and filters creation
- *and many more*

## Installation, documentation

See the [summary](https://github.com/APY/APYDataGridBundle/blob/master/Resources/doc/summary.md).

## Screenshot

Full example with this [CSS style file](https://github.com/APY/APYDataGridBundle/blob/master/Resources/doc/grid_configuration/working_example.css):

![test](https://github.com/APY/APYDataGridBundle/blob/master/Resources/doc/images/screenshot_full.png?raw=true)

Simple example with the external filter box in english:

![test](https://github.com/APY/APYDataGridBundle/blob/master/Resources/doc/images/screenshot_en.png)

Same example in french:

![test](https://github.com/APY/APYDataGridBundle/blob/master/Resources/doc/images/screenshot_fr.png?raw=true)

## Example of a simple grid with an ORM source

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

## Bundle history
Datagrid for Symfony inspired by Zfdatagrid and Magento Grid.  
This bundle was initiated by Stanislav Turza (Sorien).

See [CHANGELOG](https://github.com/APY/APYDataGridBundle/blob/master/CHANGELOG.md) and [UPGRADE 2.0](https://github.com/APY/APYDataGridBundle/blob/master/UPGRADE-2.0.md)


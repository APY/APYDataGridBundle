UPGRADE FROM S0RIENDataGridBundle(1.0) to APYDataGridBundle(2.0)
================================================================

## New namespace

The DataGridBundle has moved from the S0RIEN repository to the Abhoryo repository and his organisation APY.
Therefore you should change the namespace of this bundle in your AppKernel.php:

Before: `new Sorien\DataGridBundle\SorienDataGridBundle()`  
After: `new APY\DataGridBundle\APYDataGridBundle()`

And in autoload.php

Before: `'Sorien' => __DIR__.'/../vendor/bundles',`  
After: `'APY' => __DIR__.'/../vendor/bundles',`

Then in your files change all your `APY` use statements to `APY`

Change your include block template.

Before: `SorienDataGridBundle::blocks.html.twig`  
After: `'APYDataGridBundle::blocks.html.twig`

Example:

Before: `use Sorien\DataGridBundle\Grid\Source\Entity;`  
After: `use APY\DataGridBundle\Grid\Source\Entity;`

You call safely replace all `Sorien` occurences by `APY`.

## New columns types and filters in annotations

The version 1.0 desn't know the type of the data for the select, sourceselect and range columns.
In 2.0, these columns don't exist because they are not types of data, but types of filter.

#### Select columns

Before: `@Grid\Column(type="select", values={"type1"="Type 1", "type2"="Type 2"})`  
After: `@Grid\Column(type="text", filter="select", selectFrom="values", values={"type1"="Type 1", "type2"="Type 2"})`

See [annotation type attribute](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/columns_configuration/annotations/column_annotation_property.md) for others types.

#### SourceSelect columns

Before: `@Grid\Column(type="sourceselect")`  
After: `@Grid\Column(type="text", filter="select", selectFrom="source")` OR `@Grid\Column(type="text", filter="select", selectFrom="query")`

In 2.0, you don't have to declare a repository method `findDistinctByField($field)` to get your values for the selector.

* `query` means that the selectors of the select filter will be populated by the values found in the current search. If no result is found, they will be populated with all values found in the source.

* `source` means that the selectors of the select filter will be populated by all values found in the source.

#### Range, DateTimeRange and DateRange columns

In 2.0, a operator selector is available. When you select one of the between operators, a new field appears.  
A second input field appears if you have define `input` in the `filter` attribute.

Before: `@Grid\Column(type="range")`  
After: `@Grid\Column(type="text", filter="input")` OR `@Grid\Column(type="text", filter="select")`

Before: `@Grid\Column(type="datetimerange")`  
After: `@Grid\Column(type="datetime", filter="input")` OR `@Grid\Column(type="datetime", filter="select")`

Before: `@Grid\Column(type="daterange")`  
After: `@Grid\Column(type="date", filter="input")` OR `@Grid\Column(type="date", filter="select")`

Range works for the type `number` too.

`@Grid\Column(type="number", filter="input")` OR `@Grid\Column(type="number", filter="filter")`

## Methods renamed

 * Source::setCallBack rename to manipulateRow and manipulateQuery

	Before:

	```
	$source->setCallBack($source::EVENT_PREPARE_ROW, function ($row) {});
	$source->setCallBack($source::EVENT_PREPARE_QUERY, function ($row) {});
	```

	After:

	```
	$source->manipulateRow(function ($row) {});
	$source->manipulateQuery(function ($row) {});
	```

 * Column::setCallBack rename to manipulateRenderCell
 
	Before: `$column->manipulateRenderCell(function ($value, $row, $router) {});`  
	After: `$column->manipulateRenderCell(function ($value, $row, $router) {});`

 * Grid::initFilter rename to setDefaultFilters
 
	Before: `$grid->initFilter(array());`  
	After: `$grid->setDefaultFilters(array());`

 * Grid::initOrder rename to setDefaultOrder
 
	Before: `$grid->initOrder($columnId, $order);`  
	After: `$grid->setDefaultOrder($columnId, $order);`

 * Grid::gridResponse rename to Grid::getGridResponse

	Before: `$grid->gridResponse();`  
	After: `$grid->getGridResponse();`
 
 * GridManager::gridManagerResponse rename to GridManager::getGridManagerResponse

	Before: `$grid->gridManagerResponse();`  
	After: `$grid->getGridManagerResponse();`

## Set data on the source instead of the grid

Before: `$grid->setData($array);`  
After: `$source->setData($array);`

## Pass the grid object to the cell and filter blocks instead of the hash of the grid

Before: `{{ hash }}`  
After: `{{ grid.hash }}`

**And Clear your cache!**
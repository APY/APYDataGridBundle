Manipulate column
=================

You can manipulate the behavior of a column.

## Usage

```php
<?php
...
$grid->setSource($source);

// For auto-completion in your IDE
/* @var $column \APY\DataGridBundle\Grid\Column\Column */
$column = $grid->getColumn('my_column_id');

$column->manipulateRenderCell($callback);

$column->setTitle($title);
...
```

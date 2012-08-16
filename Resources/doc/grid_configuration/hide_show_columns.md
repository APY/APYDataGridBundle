Show and hide columns
=====================

These functions are helpers to manipulate columns.

## Usage

```php
<?php 
...
$grid->setSource($source);

$grid->showColumns($columnIds);

$grid->hideColumns($columnIds);
...
```

## Method parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|columnIds|string or array||List of the identifier of the columns you want to show or hide.|

## Example

```php
<?php 
...
$grid->setSource($source);

// Show columns
$grid->showColumns('column1');

$grid->showColumns(array('column1', 'column2'));

// Hide columns
$grid->hideColumns('column3');

$grid->hideColumns(array('column3', 'column4'));
...
```

---

## Use a visibility mask

You can use a mask to set the visibility of your columns.

#### Examples
```php
<?php 
...
$grid->setSource($source);

//We want to display only A, C and E, setVisibleColumns sets B and D to hidden
$grid->setVisibleColumns(array('A', 'C', 'E'));
...
```

OR

```php
<?php 
...
$grid->setSource($source);

//We want to display only A, C and E, setHiddenColumns sets B and D to hidden
$grid->setHiddenColumns(array('B', 'D'));
...
```
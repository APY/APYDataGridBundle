Set max results
===============

## Usage

```php
<?php
...
$grid->setSource($source);

$grid->setMaxResults($maxResults);
...
```

## Method parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|maxResults|integer|null|Max items in the grid|

## Example

```php
<?php
...
$grid->setSource($source);

$grid->setMaxResults(50);
...
```
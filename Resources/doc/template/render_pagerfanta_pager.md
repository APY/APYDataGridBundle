Render a pagerfanta pager
=========================

## Installation

You have to install the [pagerfanta librairy](https://github.com/whiteoctober/Pagerfanta) in the directory `vendor/pagerfanta/`.

Then add this new librairy to your autoload.php file.

```php
<?php
// app/autoload.php
$loader->registerNamespaces(array(
    // ...
    'APY'              => __DIR__.'/../vendor/bundles',
    'Pagerfanta'       => __DIR__.'/../vendor/pagerfanta/src',
    // ...
));
``` 

## Usage

Override the pager block and call the pagerfanta pager

```php
<?php
...
$grid = $this->get('grid');

return $grid->getGridResponse('MyProjectMyBundle::my_grid.html.twig');
...
```

And the template

```janjo
{% block grid_pager %}
{{ grid_pagerfanta(grid) }}
{% endblock grid_pager %}
```

## grid_pagerfanta function parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|grid|APY/DataGridBundle/Grid/Grid||The grid object|

## Example of css associated with this pager (from [Pagerfanta Github page](https://github.com/whiteoctober/Pagerfanta))

![Pagerfanta screenshot](../images/pagerfanta.png?raw=true)

```css
nav {
    text-align: center;
}
nav a, nav span {
    display: inline-block;
    border: 1px solid blue;
    color: blue;
    margin-right: .2em;
    padding: .25em .35em;
}

nav a {
    text-decoration: none;
}

nav a:hover {
    background: #ccf;
}

nav .dots {
    border-width: 0;
}

nav .current {
    background: #ccf;
    font-weight: bold;
}

nav .disabled {
    border-color: #ccf;
    color: #ccf;
}
```

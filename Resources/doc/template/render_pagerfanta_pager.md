Render a pagerfanta pager
=========================

## Installation

You have to install the [pagerfanta library](https://github.com/whiteoctober/Pagerfanta) in the directory `vendor/pagerfanta/`.

Then add this new library to your autoload.php file.

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

in config.yml:


```
apy_data_grid:
    pagerfanta:
        enable: true    #default false
        view_class: Pagerfanta\View\TwitterBootstrapView #default    Pagerfanta\View\DefaultView
        options:            #all options of pager fanta view constructor
           prev_message : «
           next_message : »

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

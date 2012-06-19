Render a pagerfanta pager
=========================

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
|theme|string|APYDataGridBundle::blocks.html.twig|Template used to render the grid|
|id|string|_none_|Set the identifier of the grid.|
|params|array|array()|Additional parameters passed to each block.|

**Note:** `theme`, `id` and `params` arguments have to be defined only if you call the grid_pagerfanta block before the grid block in your grid template.

## Exemple of css associated with this pager (from [Pagerfanta Github page](https://github.com/whiteoctober/Pagerfanta))

![Pagerfanta screenshot](../images/pagerfanta.png)

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

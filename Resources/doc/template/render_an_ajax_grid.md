Render an ajax grid
===================

You can load the grid with ajax interactions.  
Simply call or extend the template `APYDataGridBundle::blocks_js.jquery.html.twig` instead of `APYDataGridBundle::blocks.html.twig`.  
This template only works with the jQuery Javascript Framework but you can change it to manage this feature with your own Javascript Framework.


## Usage

Before : `{{ grid(data, 'APYDataGridBundle::blocks.html.twig') }}`  
After: `{{ grid(data, 'APYDataGridBundle::blocks_js.jquery.html.twig') }}` 

**Note**: The grid_search twig function doesn't need to extend this same template because its script are already included in the grid template.

#### Exemple

```django
{{ grid_search(data, 'APYDataGridBundle::blocks.html.twig') }}

{{ grid(data, 'APYDataGridBundle::blocks_js.jquery.html.twig') }}
```

**Note**: Pagerfanta have to extend the same template as the grid

```django
{% block grid_pager %}
{{ grid_pagerfanta(grid, 'APYDataGridBundle::blocks_js.jquery.html.twig') }}
{% endblock grid_pager %}
```
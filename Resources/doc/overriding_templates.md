Overriding templates
==================== 

```html
<!-- MyProjectMyBundle::my_grid.html.twig -->

<!-- Second parameter is optional and defines template -->
<!-- Third parameter is optional and defines grid id, like calling $grid->setId() from controller -->
{{ grid(data, 'YourBundle::my_grid_template.html.twig', 'custom_grid_id') }}

```

If you want to override blocks inside current template you can use `_self` parameter
in grid template definition. Current template will automatically extended from base block template

```html
<!-- MyProjectMyBundle::my_grid.html.twig -->
{{ grid(data, _self, 'custom_grid_id') }}

{% block grid %}
    extended grid!
{% endblock %}

```

## Grid theme template

You can override blocks - `grid`, `grid_titles`, `grid_filters`, `grid_rows`, `grid_pager`, `grid_actions`

```html
<!-- MyProjectMyBundle::my_grid_template.html.twig -->

{% extends 'SorienDataGridBundle::blocks.html.twig' %}
{% block grid %}
    extended grid!
{% endblock %}
...
{% block grid_actions %}
    extended grid!
{% endblock %}
```

## Custom cell rendering

syntax is `grid_%grid_id%_column_%column_id%_cell`, 
when no grid id is present you can use `grid_column_%column_id%_cell`

```html
<!-- MyProjectMyBundle::my_grid_template.html.twig -->

{% block grid_column_yourcolumnid_cell %}
<span style="color:#f00">My row id is: {{ row.getPrimaryFieldValue() }}</span>
{% endblock %}
```

## Custom filter rendering

syntax is `grid_%grid_id%_column_%column_id%_filter`, 
when no grid id is present you can use `grid_column_%column_id%_filter`


## Override filter rendering
you can override filter of a specific type by using the  `grid_column_type_%type%_filter`. 
Note that custom filter rendering overrides type rendering. 
 

```html
<!-- MyProjectMyBundle::my_grid_template.html.twig -->

{% block grid_column_yourcolumnname_filter %}
<span style="color:#f00">My custom filter</span>
{% endblock %}
```
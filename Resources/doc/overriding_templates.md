Overriding templates
==================== 

```html
<!-- MyProjectMyBundle::my_grid.html.twig -->

<!-- Second parameter is optional and defines template -->
<!-- Third parameter is optional and defines grid id, like calling $grid->setId() from controller -->
{{ grid(data, 'YourBundle::my_grid_template.html.twig', 'custom_grid_id') }}

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

## Custom cell rendering inside template defined as 2nd argument in twig function `grid`

```html
<!-- MyProjectMyBundle::my_grid_template.html.twig -->

{% block grid_column_yourcolumnid_cell %}
<span style="color:#f00">My row id is: {{ row.getPrimaryFieldValue() }}</span>
{% endblock %}
```

## Custom filter rendering inside template defined as 2nd argument in twig function `grid`

```html
<!-- MyProjectMyBundle::my_grid_template.html.twig -->

{% block grid_column_yourcolumnname_filter %}
<span style="color:#f00">My custom filter</span>
{% endblock %}
```
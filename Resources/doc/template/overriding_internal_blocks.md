Overriding internal blocks
==========================

## External template

If you want to override blocks of the grid you can use a extended template of the grid template.

```twig
<!-- MyProjectMyBundle::my_page_grid.html.twig -->
{% extends 'APYDataGridBundle::blocks.html.twig' %}

{% block grid_pager %}{% endblock %}

{% block grid_column_id_address_filter %}
    {{ column.id }}
{% endblock grid_column_id_address_filter %}
```

This template can then be passed to the grid twig function as second parameter

```twig
<!-- MyProjectMyBundle::my_page.html.twig -->
{{ grid(data, 'MyProjectMyBundle::my_page_grid.html.twig') }}
```

You can also apply the template globaly to your whole app by specifing it in your config.

```yaml
apy_data_grid:
    theme: 'MyProjectMyBundle::my_page_grid.html.twig'
```

## _self template

If you want to override blocks inside the current template you can use the `_self` parameter in grid template definition.
Current template will automatically extended from the base block template (APYDataGridBundle::blocks.html.twig)

```twig
<!-- MyProjectMyBundle::my_grid.html.twig -->
{% extends 'MyProjectMyBundle::layout.html.twig' %}

{% block content %}
    {{ grid(grid, _self) }}
{% endblock content %}

{% block grid_pager %}{% endblock %}

{% block grid_column_id_address_filter %}
    {{ column.id }}
{% endblock grid_column_id_address_filter %}
```
**Note**: This trick works only with extended templates. Otherwises, blocks will be displayed twice.
**Note²**: Blocks have to be define after the call of the grid and outside others blocks.

## Blocks list

These following blocks are already defined and you have access to the grid object named `grid`.
For others blocks, see [cell rendering](cell_rendering.md) and [filter rendering](filter_rendering.md)

 * **grid**
    This block manages the display of the grid.
 * **grid_no_data**
    This block manages the display of the message when no data is found in the source.
 * **grid_no_result**
    This block manages the display of the message when no data is found in the filtered source.
 * **grid_titles**
    This block manages the display of the titles.
 * **grid_filters**
    This block manages the display of the filters of the grid inside the grid.
 * **grid_search**
    This block manages the display of the filters of the grid outside the grid.
 * **grid_rows**
    This block manages the display of the rows.
 * **grid_pager**
    This block calls the three pager blocks: grid_pager_totalcount, grid_pager_selectpage, grid_pager_results_perpage.
 * **grid_pager_totalcount**
    This block manages the display of the total of rows.
 * **grid_pager_selectpage**
    This block manages the display of the internal pager.
 * **grid_pager_results_perpage**
    This block manages the display of the limit selector.
 * **grid_actions**
    This block manages the display of the mass actions selector with its submit button and the selector links.
 * **grid_exports**
    This block manages the display of the export selector with its submit button.
 * **grid_column_actions_cell**
    This block manages the display of the row actions column cell.
 * **grid_column_massaction_cell**
    This block manages the display of the mass action column cell.
 * **grid_column_type_boolean_cell** (or **grid_column_boolean_cell**)
    This block manages the display of the boolean column cell.
 * **grid_column_type_array_cell** (or **grid_column_array_cell**)
    This block manages the display of the array column cell.
 * **grid_column_operator**
    This block manages the display of the operator of a filter.
 * **grid_column_filter_type_input**
    This block manages the display of the input filter cell.
 * **grid_column_filter_type_select**
    This block manages the display of the select filter cell.
 * **grid_column_filter_type_massaction**
    This block manages the display of the mass action filter cell.
 * **grid_column_filter_type_actions**
    This block manages the display of the row actions filter cell.
 * **grid_scripts**
    This block calls the following scripts blocks.
 * **grid_scripts_goto**
    This block contains the javascript function to call an url of the grid.
 * **grid_scripts_reset**
    This block contains the javascript function to call the reset url of the grid.
 * **grid_scripts_previous_page**
    This block contains the javascript function to call the previous page url of the grid.
 * **grid_scripts_next_page**
    This block contains the javascript function to call the next page url of the grid.
 * **grid_scripts_enter_page**
    This block contains the javascript function to call the entered page url of the grid.
 * **grid_scripts_results_per_page**
    This block contains the javascript function to call the limit url of the grid.
 * **grid_scripts_mark_visible**
    This block contains the javascript function to check the visible mass actions checkboxes.
 * **grid_scripts_mark_all**
    This block contains the javascript function to simulate a full selection of the grid.
 * **grid_scripts_switch_operator**
    This block contains the javascript function to manage the operator of filters.
 * **grid_scripts_submit_form**
    This block contains the javascript function to submit a form.
 * **grid_scripts_ajax**
    This is an empty block. It is used for the ajax loading.

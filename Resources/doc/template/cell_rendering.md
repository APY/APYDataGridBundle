Cell rendering
==============

Cell rendering in the grid is handled by specific blocks in your template.  
If this block doesn't exist, the value is displayed without any transformation.  
The following parameters are passed to the block `grid_column_%column_type%_cell`.

## Block parameters

|Parameter|Type|Description|
|:--|:--|:--|
|column|APY/DataGridBundle/Grid/Column/Colomn|The column currently being rendered|
|row|APY/DataGridBundle/Grid/Row|The row of the source being rendered|
|value|mixed|The value of the cell|
|hash|string|Hash of the grid|
|params|array|Additional parameters passed to the grid|

## Overriding block names  (ordered)

 * `grid_%id%_column_%column_id%_cell`
 * `grid_%id%_column_%column_type%_cell`
 * `grid_%id%_column_%column_parent_type%_cell`
 * `grid_column_%column_id%_cell`
 * `grid_column_%column_type%_cell`
 * `grid_column_%column_parent_type%_cell`

**Note**: `.` and `:` characters in mapped field with a DQL aggregate function are replaced by an underscore.

## Examples

#### Search the selected value on click for text columns

```janjo
...
grid(grid, 'MyProjectMyBundle::my_grid_template.html.twig')
...
```

```janjo
<!-- MyProjectMyBundle::my_grid_template.html.twig -->
{% extends 'APYDataGridBundle::blocks.html.twig' %}

{% block grid_column_text_cell %}
<a href="?{{ grid.hash }}[{{ column.id }}][from]={{ value }}">{{ value }}</a>
{% endblock grid_column_text_cell %}
```

#### Use icons for boolean columns with passed additional parameters

```janjo
grid(grid, 'MyProjectMyBundle::my_grid_template.html.twig', '', {'imgDir': 'img/'})
```

```janjo
<!-- MyProjectMyBundle::my_grid_template.html.twig -->
{% extends 'APYDataGridBundle::blocks.html.twig' %}

{% block grid_column_boolean_cell %}
    <img src="{{ assets(params.imgDir ~ value ~ '.jpg')}}" alt="{{ value }}" />
{% endblock grid_column_boolean_cell %}
```


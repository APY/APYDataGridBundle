Cell rendering
==============

Cell rendering in the grid is handled by specific blocks in your template.
If this block doesn't exist, the value is displayed without any transformation.
The following parameters are passed to the block `grid_column_type_%column_type%_cell`.

## Block parameters

|Parameter|Type|Description|
|:--|:--|:--|
|grid|APY/DataGridBundle/Grid/Grid|The grid object|
|column|APY/DataGridBundle/Grid/Column/Colomn|The column currently being rendered|
|row|APY/DataGridBundle/Grid/Row|The row of the source being rendered|
|value|mixed|The value of the cell|
|params|array|Additional parameters passed to the grid|

## Overriding block names (ordered)

You can override the default block `grid_column_type_%column_type%_cell` or use one of these following blocks.  
They are called before the default block.

 * `grid_%id%_column_id_%column_id%_cell`
 * `grid_%id%_column_type_%column_type%_cell`
 * `grid_%id%_column_type_%column_parent_type%_cell`
 * `grid_column_id_%column_id%_cell`
 * `grid_column_type_%column_type%_cell`
 * `grid_column_type_%column_parent_type%_cell`

**Note 1**: It is also possible to name blocks using `..._column_...` instead of `..._column_id_...` and `..._column_type_...`.
However this naming convention is not advised as it is ambiguous. It is only supported for backward compatibility.

**Note 2**: `.` and `:` characters in mapped field with a DQL aggregate function are replaced by an underscore.

## Examples

#### Use icons for boolean columns with passed additional parameters

```janjo
grid(grid, 'MyProjectMyBundle::my_grid_template.html.twig', '', {'imgDir': 'img/'})
```

```janjo
<!-- MyProjectMyBundle::my_grid_template.html.twig -->
{% extends 'APYDataGridBundle::blocks.html.twig' %}

{% block grid_column_type_boolean_cell %}
    <img src="{{ assets(imgDir ~ value ~ '.jpg')}}" alt="{{ value }}" />
{% endblock grid_column_type_boolean_cell %}
```

#### Use the SearchOnclick functionality with the previous block

```janjo
grid(grid, 'MyProjectMyBundle::my_grid_template.html.twig', '', {'imgDir': 'img/'})
```

```janjo
<!-- MyProjectMyBundle::my_grid_template.html.twig -->
{% extends 'APYDataGridBundle::blocks.html.twig' %}

{% block grid_column_type_boolean_cell %}
    {% set value = '<img src="'~assets(imgDir ~ value ~ '.jpg')~'" alt="~value~" />' %}
    {{ block('grid_column_cell') }}
{% endblock grid_column_type_boolean_cell %}
```

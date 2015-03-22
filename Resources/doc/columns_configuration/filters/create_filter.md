Create a filter
===============

If you want another filter than the input and the select filters you can create your own filter.  
You just have to create your template for your filter and call it in the `filter` attribute of a column.

**Note**: The name of your filter is converted to lowercase.

## Example with another input filter:

#### Create the template

This template is the input filter but with a different name.

```janjo
{% block grid_column_filter_type_input2 %}

{# Operator #}
{% set btwOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTW') %}
{% set btweOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_BTWE') %}
{% set isNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNULL') %}
{% set isNotNullOperator = constant('APY\\DataGridBundle\\Grid\\Column\\Column::OPERATOR_ISNOTNULL') %}
{% set op = column.data.operator is defined ? column.data.operator : column.defaultOperator %}

{# Query #}
{% set from = column.data.from is defined ? column.data.from : null %}
{% set to = column.data.to is defined ? column.data.to : null %}

{# Display #}
<span class="grid-filter-input">
    {{ block('grid_column_operator')}}
    <span class="grid-filter-input-query">
        <input type="{{ column.inputType }}" value="{{ from }}" class="grid-filter-input-query-from" name="{{ grid.hash }}[{{ column.id }}][from]" id="{{ grid.hash }}__{{ column.id }}__query__from" {% if submitOnChange is sameas(true) %}onkeypress="if (event.which == 13){this.form.submit();}"{% endif%} {{ ( op == isNullOperator or op == isNotNullOperator ) ? 'style="display: none;" disabled="disabled"' : '' }} />
        <input type="{{ column.inputType }}" value="{{ to }}" class="grid-filter-input-query-to" name="{{ grid.hash }}[{{ column.id }}][to]" id="{{ grid.hash }}__{{ column.id }}__query__to" {% if submitOnChange is sameas(true) %}onkeypress="if (event.which == 13){this.form.submit();}"{% endif%} {{ ( op == btwOperator or op == btweOperator ) ? '': 'style="display: none;" disabled="disabled"' }} />
    </span>
</span>

{% endblock grid_column_filter_type_input2 %}
```

#### Call your new filter

In your column annotation configuration:

```
/*
 * @GRID/Column(filter="input2")
 */
 protected $description;
```

OR in PHP:

```php
<?php
...
$grid->setSource($source);

$grid->getColumn('description')->setFilterType('input2');
...
``` 

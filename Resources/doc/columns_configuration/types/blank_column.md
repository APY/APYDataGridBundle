Blank Column
==============

This column isn't linked to any field of your data but can be filled for the render with the render cell manipulator or a template.  
This column isn't sortable and filterable.

## Annotation
### Inherited Attributes

See [Column annotation for properties](../annotations/column_annotation_property.md).

## Examples
### Manipulator Render in PHP

```php
...
$grid->setSource($source);

// Add a column with a rendering callback
$MyColumn = new BlankColumn(array('id'=>'informations', 'title'=>'informations'));

$MyColumn->manipulateRenderCell(function($value, $row, $router) {
    return $row->getField('column4') . "<br />" . $row->getField('column5');
});

$grid->addColumn($MyColumn);
...
```

### Overriding column block

```php
...
$grid->setSource($source);

$MyColumn = new BlankColumn(array('id'=>'informations', 'title'=>'informations'));

$grid->addColumn($MyColumn);
...
```

```django
{% block grid_column_informations_cell %}
{{ row.field('column4') }}
<br />
{{ row.field('column5') }}
{% endblock grid_column_informations_cell %}
```


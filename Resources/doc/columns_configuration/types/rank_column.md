Rank Column
==============

This column show the rank of a row in the grid with filters.  
This column isn't sortable and filterable.

## Annotation
### Inherited Attributes

See [Column annotation for properties](../annotations/column_annotation_property.md).

**Note**: If you use this column in annotation, values of the column will be overwrited in the render.

### Default id: `rank`

### Default title: `rank`

### Default size: `30`

### Default align: `center`

## PHP

```php
$grid->addColumn(new RankColumn(array('title'=>'track')), 1);
```
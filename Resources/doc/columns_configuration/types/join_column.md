Join Column
===========

Render several **text** columns inside this join column.  
This join column is sortable, filterable and you can also activate the 'search on click' feature.

By default, a disjunction (OR operator) is performed with each column.


## Annotation
#### Inherited Attributes

See [Column annotation for properties](../annotations/column_annotation_property.md).

**Note**: `id` attribute is required.  
**Note²**: The `source` and `isManuallyField` are forced to `true`.

### Additionnal attributes

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|columns|array|empty|The name of the columns|Name of the columns you want to show in this column|

**Note**: Columns listed in the `columns` attributes must be declared too.  

## Example

```php
$grid = $this->get('grid');

$column = new JoinColumn(array('id' => 'my_join_column', 'title' => 'Full name', 'columns' => array('lastname', 'firstname')));

$grid->addColumn($column);
```

Or with a [Class column annotation](columns_configuration/annotations/column_annotation_class.md):

```
/**
 * @ORM\Entity
 * @ORM\Table(name="customer")
 *
 * @GRID\Source(columns="id, my_join_column")
 * @GRID\Column(id="my_join_column", type="join", title="Full name", columns={"lastname", "firstname"})
 */
class Customer
```

## Filter
#### Valid values

Everything.

#### Default Operator: `like`

#### Available Operators

|Operator|Meaning|
|:--|--:|
|eq|Equals|
|neq|Not equal to|
|lt|Lower than|
|lte|Lower than or equal to|
|gt|Greater than|
|gte|Greater than or equal to|
|like|Contains (case insensitive)|
|nlike|Not contain (case insensitive)|
|rlike|Starts with (case insensitive)|
|llike|Ends with (case insensitive)|
|slike|Contains|
|nslike|Not contain|
|rslike|Starts with|
|lslike|Ends with|
|btw|Between exclusive|
|btwe|Between inclusive|
|isNull|Is not defined|
|isNotNull|Is defined|

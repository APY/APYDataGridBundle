Array Column
===========

## Annotation
### Inherited Attributes

See [Column annotation for properties](../annotations/column_annotation_property.md).

### Additionnal attributes

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|separator|string|&lt;br /&gt;||Define the separator of the values of the array.|


## Filter
### Valid values

Everything.

### Default Operator: `like`

### Available Operators

|Operator|Meaning|
|:--|--:|
|eq|Equals|
|neq|Not equal to|
|like|Contains|
|nlike|Not contain|
|isNull|Is not defined|
|isNotNull|Is defined|

**Note**: `Equals` means there is only the filtered value in the array.  
`Contains` means there is the filtered value and maybe another values in the array.
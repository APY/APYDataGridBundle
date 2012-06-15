DateTime Column
===============

If no format is defined, the datetime is converted based on the locale and the time zone.  
If no format and if the Intl extension is not installed, a fallback format is used (Y-m-d H:i:s)

## Annotation
#### Inherited Attributes

See [Column annotation for properties](../annotations/column_annotation_property.md).

#### Additionnal attributes

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|format|string|||Define this attribute if you want to force the format of the displayed value.<br />(e.g. "Y-m-d H:i:s")|

## Filter
#### Valid values

Everything that a DateTime instance can understand.  
Wrong values are ignored.

#### Default Operator: `eq`

#### Available Operators

|Operator|Meaning|
|:--:|:--|
|eq|Equals|
|neq|Not equal to|
|lt|Lower than|
|lte|Lower than or equal to|
|gt|Greater than|
|gte|Greater than or equal to|
|like|Contains|
|nlike|Not contain|
|rlike|Starts with|
|llike|Ends with|
|btw|Between exclusive|
|btwe|Between inclusive|
|isNull|Is not defined|
|isNotNull|Is defined|
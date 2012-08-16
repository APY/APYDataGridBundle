Column Annotation for a class
=============================

The Column annotation for a class allows to add a non-mapped source column.

Example:
```php
<?php
...
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * Add custom columns to the grid
 * @GRID\Column(id="myColumn", size="120", type="text")
 */
class Product
{

}
```

### Available Attributes:

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|id|string|||Mandatory id of the column|
|title|string|%id%||Title of the column|
|size|integer|-1|Number >= -1<br />(-1 means auto resize)|Size of the column|
|type|string|text|text, boolean, date, datetime, array, blank|Type of the column.|
|<i>sortable</i>|boolean|true|true or false|Sets the possibility of sortering of the column|
|<i>filterable</i>|boolean|true|true or false|Sets the possibility of filtering of the column|
|visible|boolean|true|true or false|Sets the visibilty of the column|
|align|string|left|left, right or center|Sets the text alignment with a CSS class|
|role|string||A symfony role|Sets the visiblity of the column to false if the access isn't granted for the defined role|
|groups|string<br />or<br />array|Example: groups="group1",<br/>groups={"group1"}, groups={"group1", "group2"}||Use this attribute to define more than one configuration for an Entity/Document. <br />If no groups is defined, the annotation is attributed for all groups.<br />$source = new Entity('MyProjectMyBundle:MyEntity', 'my_group');|

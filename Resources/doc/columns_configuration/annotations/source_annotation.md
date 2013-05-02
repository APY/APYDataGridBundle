Source Annotation for a class
=============================

The Source annotation for a class allows to configure the grid.  
It's optional if you have declare Column annotation for your properties.

## Example
```php
<?php
...
use APY\DataGridBundle\Grid\Mapping as GRID;

/**
 * @GRID\Source(columns="id, type")
 * @GRID\Source(columns="id, type, date", groups={"admin", "backend"})
 * @GRID\Source(columns="id, type", groups="list", groupBy={"type"})
 */
class Product
{
    protected $id;

    protected $type;
    
    protected $date;
}
```

### Available Attributes:

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|columns|string||Comma column name separated|Define the order and the visibility of columns.<br />The primary key have to be defined in this list.|
|filterable|boolean|false|true or false|Sets the default filterable value of all columns|
|sortable|boolean|false|true or false|Sets the default sortable value of all columns|
|groups|string<br />or<br />array|Example: groups="group1",<br/>groups={"group1"}, groups={"group1", "group2"}||Use this attribute to define more than one configuration for an Entity/Document. <br />If no groups is defined, the annotation is attributed for all groups.<br />$source = new Entity('MyProjectMyBundle:MyEntity', 'my_group');|
|groupBy|string<br />or<br />array|Example: groupBy="property1",<br/>groupBy={"property1"}, groupBy={"property1", "property2"}||Use this attribute to add groupBy fields to the query|

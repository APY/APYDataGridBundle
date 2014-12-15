Column Annotation for a property
================================

The Column annotation for a property allows to add a mapped source column.

#### Example
```php
<?php
...
use APY\DataGridBundle\Grid\Mapping as GRID;
...
class Product
{
    /**
     * @ORM\Column(name="id", type="integer")
     *
     * @GRID\Column(title="my own column name", size="120", type="text")
     * @GRID\Column(title="other name", size="80", type="text", groups={"admin", "backend"})
     * @GRID\Column(visible=false, groups="total")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length="32")
     *
     * @GRID\Column(title="Type", size="120", filterable=false, filter="select",  selectFrom="values", values={"type1"="Type 1","type2"="Type 2"}, groups={"default", "admin", "backend", "total"})
     */
    protected $type;

    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="products")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *
     * @GRID\Column(field="category.name", title="Category Name")
     * @GRID\Column(field="category.children.name", type="array", title="Category Children")
     */
    protected $category;
}
```

## Available Attributes

**If you use the `columns` attribute of the [source annotation](https://github.com/Abhoryo/APYDataGridBundle/blob/master/Resources/doc/columns_configuration/annotations/source_annotation.md), only the columns defined in this attribute can use attributes `visible` and `export` but others attributes like `filterable` and `sortable` will work even if the visible attribute is set to `false`.**

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|title|string|The property name||Title of the column|
|size|integer|-1|Number >= -1|Size of the column (-1 means auto resize)|
|type|string|The ORM/ODM type|text, number, boolean, date, datetime, array|Type of the column.|
|filter|string|input|input, select|Filter type of the column.|
|sortable|boolean|true|true or false|Sets the possibility of sortering of the column|
|filterable|boolean|true|true or false|Sets the possibility of filtering of the column|
|visible|boolean|true|true or false|Sets the visibilty of the column|
|export|boolean|null|null,<br />true or false|Forces the visibilty of the column for exports. A null value means that the defined visibility isn't forced to another value for exports|
|operatorsVisible|boolean|true|true or false|Sets the visibilty of the operators filter|
|operators|array|operators of the column|See [operators](../types/text_column.md#available_operators)|Sets the available operators of the column|
|defaultOperator|string|default operator of the column|See [operators](../types/text_column.md#available_operators)|Sets the default operator of the column|
|primary|boolean|false|true or false|Sets the primary key of the source|
|align|string|left|left, right or center|Sets the text alignment with a CSS class|
|inputType|string|text|See [HTML5 input types](http://w3schools.com/html/html5_form_input_types.asp)|Sets the type of the input filter field|
|role|string||A symfony role|Sets the visiblity of the column to false if the access isn't granted for the defined role|
|groups|string<br />or<br />array|Example: groups="group1",<br />groups={"group1"}, groups={"group1", "group2"}||Use this attribute to define more than one configuration for an Entity/Document. <br />If no groups is defined, the annotation is attributed for all groups.<br />$source = new Entity('MyProjectMyBundle:MyEntity', 'my_group');|
|selectFrom|string|query|query, source, values|How to populate the selector of the select filters of the column.|
|values|array|_none_||For select filters or replace values in the grid|
|searchOnClick|boolean|false|true or false|Sets the possibility to perform a search on the clicked cell (filterable has to be true)|
|safe|string or false|html|false<br />or<br />see [Escape filters](http://twig.sensiolabs.org/doc/filters/escape.html)|Sets the escape filter|
|usePrefixTitle|boolean|true|true or false|Use the prefixTitle of the grid to render title|
**Note 1**: Every attribute has a setter and a getter method.  
**Note 2**: With the `values` attributes, if `type1` is found, the grid displays the value `Type 1`.  
**Note 3**: If operators are not visible, filtering is performed with the default operator.

## Title translation

### Abbreviation

You can use abbreviations to define different titles for the column title and the filter label in the external search box.

Example with the $type variable.
`type` will only be search for the label in the external search box.
For the column title of the grid `type__abbr` will be search and `type` will be search if the abbrevation isn't defined.

### Automatic translation extraction via JMSTranslationBundle

A translation extractor is included that can discover column titles defined in the annotations.  Running
[JMSTranslationBundle](https://github.com/schmittjoh/JMSTranslationBundle) via the command line will automatically
discover and catalogue titles as translation keys.




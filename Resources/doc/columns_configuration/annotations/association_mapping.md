ORM association mapping support with `.` notation
=================================================

Example of an association with multi related fields on the same property.

```php
<?php
...
/**
 * @GRID\Source(columns="id, type, category.children, category.name")
 */
class Product
{
	protected $id;

    protected $type;

    /**
     * @ORM\OneToMany(...)
     *
	 * @GRID\Column(field="category.name", title="Category Name")
	 * @GRID\Column(field="category.children", type="array", title="Category Children")
     */
    protected $category;	
...
}
```

**Important**: With mapped fields, the guess typing is not implemented, you need to explicitly define the type if it's not a text field.

```php
<?php
...
class Category
{
	protected $id;

    protected $name;
	
	protected $children;

    /**
     * @ORM\ManyToOne(...)
     */
    protected $product;	
...
}
```

A column on a mapped field has the same attributes of a normal field and have one another attribute: `field`

### Additionnal Attribute:

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|field|string|||The name of the related field of the property|

**Note**: The default title of a related field is the name of the field.
`@Grid\Column(field="category.name") => title = "category.name"`

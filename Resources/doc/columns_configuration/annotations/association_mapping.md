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
     * @GRID\Column(field="category.firstChild.name", title="Category first child")
     * @GRID\Column(field="category.tags", type="array", title="Category tags")
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

    /**
     * @ORM\OneToOne(...)
     */
    protected $firstChild;

    protected $tags;

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

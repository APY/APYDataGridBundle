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

**Important** Fields are automitically joined with a leftJoin. If you want to use an inner join, you can specify the join type:

```php
<?php
...
class Product
{
    protected $id;

    protected $type;

    /**
     * @ORM\OneToMany(...)
     *
     * @GRID\Column(field="category.name", title="Category Name", joinType="inner")
     */
    protected $category;
...
}
```

A column on a mapped field has the same attributes of a normal field and have two another attribute: `field` and `joinType`


### Additionnal Attribute:

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|field|string|||The name of the related field of the property|
|joinType|string||inner|Specify the join type for the related records (E.G. 'inner' for an inner join|

**Note**: The default title of a related field is the name of the field.
`@Grid\Column(field="category.name") => title = "category.name"`

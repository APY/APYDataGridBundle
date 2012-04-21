Annotations
===========

## Usage - Document or Entity annotations

Entity and Document sources use doctrine annotations for type guessing, for better customization you can use grid mapping annotations

```php
<?php
...
use Sorien\DataGridBundle\Grid\Mapping as GRID;

/**
 * Annotation Test Class
 *
 * @GRID\Source(columns="id, ...")
 * @GRID\Column(id="attached1", size="120", type="text") //add custom column to grid, id has to be specified
 */
class Test
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @GRID\Column(title="my own column name", size="120", type="text", visible=true, source=true, ... )
     */
    private $id;

    /**
     * @var integer $type
     *
     * @ORM\Column(type="string", length="32")
     * @GRID\Column(title="Type", size="120", type="select", visible=true, values={"type1"="Type 1","type2"="Type 2"})
     */
    private $type;

}
```

### Available types for '@GRID\Column' notation

 - id [string] - column id - property name, should by set only if column is defined inside class annotations
 - field [string] - table column /collection name
 - title [string] - own column name
 - size [int] - column width in pixels, default -1, -1 means auto resize
 - type [string] - column type (Date, Range, Select, Text, Boolean)
 - values [array] - options (only Select Column)
 - format [string] - format (only Date Column)
 - sortable [boolean]- turns on or off column sorting
 - filterable [boolean] - turns on or off visibility of column filter
 - source [boolean] - turns on or off column visibility for Source class (if false column will *not* be read from data source)
 - visible [boolean] -  turns on or off column visibility (if false column will be read from data source but not rendered)
 - primary [boolean] - sets column as primary - default is primary key form Entity/Document
 - align [string(left|right|center)] - default left
 - role [string] default null - security role for current column example: role="ROLE_USER"

### Available types for '@GRID\Source' notation

 - columns [string] order of columns in grid 
    - columns are separated by a comma (',')
    - The primary key have to be defined in this list.
    - Use the property name, not the column name. For related fields, use the field name (see example below in a One to Many association).
 - filterable [bool] turns on or off visibility of all columns


### ORM association support with `.` notation

Example of a `Many to One` association

```php
<?php
...
/**
 * @ORM\ManyToOne(targetEntity="Vendors")
 * @ORM\JoinColumn(name="seller_id", referencedColumnName="id")
 *
 * @GRID\Column(field="vendor.name")
 */
private $vendor;
...
```

Example of a `One to Many` or `One to One` association with multi related fields on the same property + order of the related fields

```php
<?php
/**
 * @grid\Source(columns="id, translations.lang, translations.description, reference, translations.name")
 */
class Product {
    /**
     * @ORM\OneToMany(targetEntity="LangProduct", mappedBy="product")
     * 
     * @Grid\Column(field="translations.lang")
     * @Grid\Column(field="translations.name")
     * @Grid\Column(field="translations.description")
     */
    private $translations;    
...
}
```

**Note**: The default title of a related field is the name of the field.
`@Grid\Column(field="translations.lang") => title = "translations.lang"`
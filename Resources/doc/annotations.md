Annotations
===========

## Usage - Document or Entity annotations

Entity and Document source uses doctrine annotations for type guessing, for better customization you can use own annotations

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
     * @GRID\Column(title="my own column name", size="120", type="text", visible=true, ... )
     */
    private $id;
}
```

### Available types for '@GRID\Column' notation

 - id [string] - column id - default is property name, Source overrides it to field name
 - title [string] - own column name
 - size [int] - column width in pixels, default -1, -1 means auto resize
 - type [string] - column type (Date, Range, Select, Text, Boolean)
 - values [array] - options (only Select Column)
 - format [string] - format (only Date Column)
 - sortable [boolean]- turns on or off column sorting
 - filterable [boolean] - turns on or off visibility of column filter
 - source [boolean] - turns on or off column visibility for Source class
 - visible [boolean] -  turns on or off column visibility
 - primary [boolean] - sets column as primary - default is primary key form Entity/Document
 - align [string(left|right|center)] - default left,

### Available types for '@GRID\Source' notation

 - columns [string] order of columns in grid (columns are separated by ",")
 - filterable [bool] turns on or off visibility of all columns


### Many to One association support with `.` notation (just ORM)

```php
<?php
...
/**
 * @ORM\ManyToOne(targetEntity="Vendors")
 * @ORM\JoinColumn(name="vendor", referencedColumnName="id")
 *
 * @GRID\Column(id="vendor.name")
 */
private $vendor;
...
```
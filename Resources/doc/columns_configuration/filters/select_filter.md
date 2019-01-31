Select filter
=============

This type of filter displays a choices selector instead of an input field.  
This selector can be filled by the query result, the source or an array of values.

A second selector is displayed if you select a range oparator (between).

The two selectors are disabled if the `Is defined` operator or the  `Is not defined` operator are selected.


## Annotation

#### Additionnal attributes

|Attribute|Type|Default value|Possible values|Description|
|:--:|:--|:--|:--|:--|
|selectFrom|string|query|query, source or values|How to populate the selector of the select filters of the column.|
|selectMulti|boolean|false|true/false|Set to true for multiple select.
|selectExpanded|boolean|false|true/false|If sets to true, radio buttons or checkboxes (depending on the multiple value) will be rendered instead of a select element.
|values|array|||Define the options values of the selector if selectFrom is set to `values` or if you want to replace values in the grid.|

## selectFrom informations

When you choose a select filter for a column, you have three way to populate your selector:

* `query` means that the selectors of the select filter will be populated by the values found in the current search. If no result is found, they will be populated with all values found in the source.

* `source` means that the selectors of the select filter will be populated by all values found in the source.

* `values` means that the selectors of the select filter will be populated by the values define in the `values` parameter.

#### Examples

From values:

```php
<?php
...
/**
 * @ORM\Column(type="string", length="32")
 *
 * @GRID\Column(filter="select", selectFrom="values", values={"type1"="Type 1","type2"="Type 2"})
 */
protected $type;
...
```

**Note**: With the `values` attributes, if `type1` is found, the grid displays the value `Type 1`. This feature works with other type of filters.

From source:

```php
<?php
...
/**
 * @ORM\Column(type="string", length="32")
 *
 * @GRID\Column(filter="select",  selectFrom="source")
 */
protected $type;
...
``` 

From query (default):

```php
<?php
...
/**
 * @ORM\Column(type="string", length="32")
 *
 * @GRID\Column(filter="select")
 */
protected $type;
...
``` 

**Note**: The selectFrom attribute take the value `source` if the query result are empty.

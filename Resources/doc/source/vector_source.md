Vector source
=============

# Summary
 * [About the Vector source](#about)
 * [Create a Vector](#usage)
 * [Create a Vector without data](#nodata)
 * [Set a primary field](#set_id)
 * [Columns configuration](#columns_configuration)
 * [How the type of each column is guessed ?](#guess)

<a name="about"/>
## About the Vector source

The vector source come handy if you have to handle some data that doesn't match your bundle's entities
or if you don't want to use entities at all because you are working with a lot of data.

In our example we receive this array from a json source:

```php
<?php
...
$books = array(
    array(
        'id' => 1,
        'publisher_id' => 112,
        'title' => 'book1',
        'authors' => array('author1', 'author2'),
        'publication' => '2012-04-06',
        'createDate' => '2012-04-06 22:34:56',
        'pages' => 320,
        'multilanguage' => 1
    ),
    array(
        'id' => 2,
        'publisher_id' => 105,
        'title' => 'book2',
        'authors' => array('author1', 'author3'),
        'publication' => 'Apr. 6, 2012',
        'createDate' => '2012-04-06 10:34:56PM',
        'pages' => 480,
        'multilanguage' => true
    ),
);
...
```

We will see how to plug this array in a Grid, thanks to the Vector source.

<a name="usage"/>
## Create a Vector

Just like any other source all you need is to instanciate the Vector and feed it to the grid.

```php
<?php
use APY\DataGridBundle\Grid\Source\Vector;
...
/* fetch and store data in $books */

$source = new Vector($books);

$grid = $this->get('grid');

$grid->setSource($source);

return $grid->getGridResponse();
...
```

The Vector source treats this array and iterates the 10 first rows to guess the type of each columns.  
It uses the keys of your array to determine the name of the columns. In our case the columns will be: `id`, `publisher_id`, `title`, `authors`, `publication`, `createDate`, `pages` and `multilanguage`.  
The columns can be filtered and ordered.

**Note**: Each column have to be defined for each row.  
**Note²**: Operators `Equals` and `Contains` support regular expression.

<a name="nodata"/>
## Create a Vector without data

In some cases, you want to render a grid without data (With the message No results). To do that you have to pass an array of Column when you instantiate your Vector.
```php
<?php
use APY\DataGridBundle\Grid\Column;
...
$columns = array(
    new Column\NumberColumn(array('id' => 'id', 'field' => 'id', 'source' => true, 'primary' => true, 'title' => 'id')),
    new Column\NumberColumn(array('id' => 'publisher_id', 'field' => 'publisher_id', 'source' => true, 'title' => 'Publication id')),
    new Column\TextColumn(array('id' => 'title', 'field' => 'title', 'source' => true, 'title' => 'Title')),
    new Column\ArrayColumn(array('id' => 'authors', 'field' => 'authors', 'source' => true, 'title' => 'Authors')),
    new Column\DateColumn(array('id' => 'publication', 'field' => 'publication', 'source' => true, 'title' => 'Publication Date', 'format' => 'd/m/Y')),
    new Column\DateTimeColumn(array('id' => 'createDate', 'field' => 'createDate', 'source' => true, 'title' => 'Creation Date', 'format' => 'd/m/Y H:i:s')),
    new Column\NumberColumn(array('id' => 'pages', 'field' => 'pages', 'source' => true, 'title' => 'Number of pages')),
    new Column\BooleanColumn(array('id' => 'multilanguage', 'field' => 'multilanguage', 'source' => true, 'title' => 'Multilanguage')),
);

$source = new Vector(array(), $columns);
...
```

**Note:** Columns are not sourcable and mapped with id by default, you have to define source=true and field=<id> if you want your data mapped on these columns.

<a name="set_id"/>
## Set a primary field

Vector will use the first "column" found as the Primary Field of your grid.  
In our case it will be the column named "id". If you are using action columns, they will use this primary field.  
If you want to use a specific column or set columns as the primary field, use Vector::setId($id).

In our example we could map our actions on the publisher_id.

```php
<?php
...
    $source = new Vector($books);

    $source->setId('publisher_id');
...
```

If your route has multiple Ids you can map them to the vector so that the action columns use them.

```yml
...
books_more:
    pattern: /{id}/{publisher_id}/moreinfo
...
```

```php
<?php
...
$source = new Vector($books);
$source->setId(array('id', 'publisher_id'));

$grid = $this->get('grid');

$grid->setSource($source);

$myRowAction = new RowAction('More Info', 'books_more', false, '_self', array('class' => 'show'));
$grid->addRowAction($myRowAction);
...
```

It's equal to:

```php
<?php
...
$source = new Vector($books);

$grid = $this->get('grid');

$grid->setSource($source);

$myRowAction = new RowAction('More Info', 'books_more', false, '_self', array('class' => 'show'));
$source->setRouteParameter(array('id', 'publisher_id'));
$grid->addRowAction($myRowAction);
...
```

<a name="columns_configuration"/>
## Columns Configuration

With a vector source you can't change the type of the column but you can change others parameters:

```php
<?php
...
$source = new Vector($books);

$grid = $this->get('grid');

$grid->setSource($source);

$grid->getColumn('id')->setFilterable(false);

$grid->getColumn('authors')
    ->setFilterType('select')
    ->setSelectFrom('query')
    ->setSortable(false);

return $grid->getGridResponse();
...
```

<a name="columns_configuration_2"/>
You can also do that using an array of Column:
```php
<?php
use APY\DataGridBundle\Grid\Column;
...
$columns = array(
    new Column\NumberColumn(array('id' => 'id', 'field' => 'id', 'filterable' => true, 'source' => true)),
    new Column\ArrayColumn(array('id' => 'authors', 'field' => 'authors', 'source' => true, 'filter' => 'select', 'selectFrom' => 'query', 'sortable' => false))
);

$source = new Vector($books, $columns);

$grid = $this->get('grid');

$grid->setSource($source);

return $grid->getGridResponse();
...
```

<a name="guess"/>
## How the type of each column is guessed ?
When we use a Vector source, the type of each column composing our grid will be guessed. Here is how it works:
- if we only have data, the type is guessed parsing the 10 first lines of our data.
- if we have data and an array of Column (see [here](#columns_configuration_2)), the type is guessed parsing the 10 first lines of our data, when the column is not in our array of Column.
- if we only have an array of Column, we only use this array to return columns


## Missing features

* Mapped fields
* GroupBy
* Aggregate DQL functions

## Unapplicable features

* Groups annnotation

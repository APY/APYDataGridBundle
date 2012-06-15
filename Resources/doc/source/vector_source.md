Vector source
=============

# Summary
 * [About the Vector source](#about)
 * [Create a Vector](#usage)
 * [Set a primary field](#set_id)
 * [Columns configuration](#columns_configuration)

<a name="about"/>
## About the Vector source

The vector source come handy if you have to handle some data that doesn't match your bundle's entities
or if you don't want to use entities at all because you are working with a lot of data.

In our exemple we receive this array from a json source:

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

<a name="set_id"/>
## Set a primary field

Vector will use the first "column" found as the Primary Field of your grid.  
In our case it will be the column named "id". If you are using action columns, they will use this primary field.  
If you want to use a specific column or set columns as the primary field, use Vector::setId($id).

In our exemple we could map our actions on the publisher_id.

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
$source->setId(array('id', 'author_id'));

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
$source->setRouteParameter(array('id', 'author_id'));
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
	->setSort(false);

return $grid->getGridResponse();
...
```

## Missing features

* Mapped fields
* GroupBy
* DQL functions
* Change type of a column

## Unapplicable features

* Groups annnotation


Vector source configuration
===========
# Summary
 * [About the Vector source](#about)
 * [Create a Vector](#usage)
 * [Set a primary field](#set_id)

<a name="about"/>
## About the Vector source

The vector source come handy if you have to handle some data that doesn't match your bundle's entities
or if you don't want to use entities at all because you are working with a lot of data.

In our exemple we receive this array from a json source:

```php
<?php

$books = array(
    array(
        'id'=>1,
        'title'=>'book1',
        'author_id'=>12,
    ),
    array(
        'id'=>2,
        'title'=>'book2',
        'author_id'=>56,
    ),
    [...]
);
```

we will see how to plug this array in a Grid, thanks to the Vector source

<a name="usage"/>
## Create a Vector

Just like any other source all you need is to instanciate the Vector and feed it to the grid.

```php
<?php

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Vector source
 */
use Sorien\DataGridBundle\Grid\Source\Vector;
use Sorien\DataGridBundle\Grid\Column\ActionsColumn;

/**
 * Book Grid controller.
 */
class BookGridController extends Controller
{

    /**
     * Lists all Books 
     */
    public function indexAction()
    {
        /* FETCHES THE DATA and stores it in $books */

        $source = new Vector($books);

        $grid = $this->get('grid');

        $grid->setLimits(array(5 => '5', 10 => '10', 15 => '15'));

        $grid->setSource($source);

        return $this->render('BooksBundle::index.html.twig', array(
            'data' => $grid
        ));
    }

```

The Vector source treats all of your data as a strings and feed them to TextColumns.
The columns can be filtered and ordered.
It uses the keys of your array to determine the name of the columns, in our case the columns will be:
    id, title, author_id

Vector will use the first "column" it finds as the Primary Field of your grid.

In our case it will be the column named "id". If you are using action columns, they will use this primary field.


<a name="set_id"/>
## Set a primary field

If you want to use a specific column or set of columns as the primary field, use setId($id)

In our exemple we could want to map our actions on the author_id.

```php
<?php

    $source = new Vector($books);
    $source->setId('author_id');
```

If your route has multiple Ids you can map them to the vector so that the action columns use them.

```yml
books:
    pattern: /{id}/{author_id}/moreinfo
```

```php
<?php
    $source = new Vector($books);
    $source->setId(array('id','author_id'));
```

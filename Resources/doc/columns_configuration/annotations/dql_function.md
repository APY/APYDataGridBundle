DQL function notation with association mapping (Only with an Entity source)
===========================================================================

When you have some related fields, you can perform some aggregate DQL functions.

Notation: `<property>.<field_id>:<aggregate_function>`

You have 5 basic aggregate functions: `count`, `avg`, `min`, `max` and `sum` but you can also use other DQL defined functions like the `GroupConcat` or `CountIf` DQL function if you install it. ([Source](https://github.com/beberlei/DoctrineExtensions/blob/master/lib/DoctrineExtensions/Query/Mysql/))


```php
<?php
...
/**
 * @GRID\Source(columns="id, sales.id:count, sales.price:avg, sales.price:sum")
 */
class Article {
...
	protected $id;
	
    /**
     * @ORM\OneToMany(...)
     * 
     * @Grid\Column(field="sales.id:count", title="Number of sales")
     * @Grid\Column(field="sales.price:avg", title="Average price")
     * @Grid\Column(field="sales.price:min", title="Minimum price")
	 * @Grid\Column(field="sales.price:max", title="Maximum price")
	 * @Grid\Column(field="sales.price:sum", title="Profit")
     */
    protected $sales;    
...
}
```

```php
<?php
...
class Sale
{
	protected $id;

    protected $price;

    /**
     * @ORM\ManyToOne(...)
     */
    protected $article;	
...
}
```

**Note**: When a function notation is detected, a groupBy is automatically performed on the primary field of the parent entity if no groupBy attribute is defined in the Source annotation.

**Warning**: Doctrine have a limitation. When you filter on a column with a DQL function, a HAVING clause is added to the query.  
Example: `SELECT _p.member_id, COUNT(_p) FROM Photo _p GROUP BY _p.member_id HAVING COUNT(_p) > 3`  
But the HAVING clause supports DQL function only with comparison operator (=, <, <=, <>, >, >=, !=).  
Due to this limitation, the selector of operators will displayed with only the supported operators.
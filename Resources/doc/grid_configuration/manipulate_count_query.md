Manipulating the count query builder
============================

The grid requires the total number of results (COUNT (...)). The grid clones the source QueryBuilder and wraps it with a COUNT DISTINCT clause.
If you use a lot of aggregation in the source queryBuilder you may encounter performance problems.
You can manipulate the query before it is processed to remove useless fields for the COUNT clause.

## 1. Using a callback

```php
<?php
...
$source->manipulateCountQuery($callback);

$grid->setSource($source);
...
```

### Method Source::manipulateCountQuery parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|callback|[\Closure](http://php.net/manual/en/functions.anonymous.php) or [callable](http://php.net/manual/en/language.types.callable.php)|null|Callback to manipulate the query. Null means no callback.|

### Callback parameters

|parameter|Type|Description|
|:--:|:--|:--|:--|:--|
|queryBuilder|instance of QueryBuilder|The QueryBuilder instance before its execution (clone of the source QueryBuilder)|

### Examples

```php
<?php
...
$source->manipulateCountQuery(
    function ($queryBuilder)
    {
        $queryBuilder->resetDQLPart('select');
    }
);

$grid->setSource($source);
...
```

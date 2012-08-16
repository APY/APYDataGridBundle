Manipulate the query builder
============================

You can set a callback to manipulate the query builder before its execution.

## Usage

```php
<?php
...
$source->manipulateQuery($callback);

$grid->setSource($source);
...
```

## Method Source::manipulateQuery parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|callback|[\Closure](http://php.net/manual/en/functions.anonymous.php) or [callable](http://php.net/manual/en/language.types.callable.php)|null|Callback to manipulate the query. Null means no callback.|

## Callback parameters

|parameter|Type|Description|
|:--:|:--|:--|:--|:--|
|query|instance of QueryBuilder|The QueryBuilder instance before its execution|

## Examples

```php
<?php
...
$source->manipulateQuery(
    function ($query)
    {
        $query->resetDQLPart('orderBy');
    }
);

$grid->setSource($source);
...
```

If you want to pass some context parameters:

```php
<?php
...
$tableAlias = $source::TABLE_ALIAS;

$source->manipulateQuery(
    function ($query) use ($tableAlias)
    {
        $query->andWhere($tableAlias . '.active = 1');
    }
);

$grid->setSource($source);
...
```

**Warning**: You must use "andWhere" instead of "Where" statement otherwise column filtering won't work. Same thing about the order and the group (addOrder, addGroup).

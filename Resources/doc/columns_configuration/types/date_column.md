Date Column
===========

The Date Column extends the [DateTime Column](datetime_column.md) and the default fallback format is `Y-m-d`.

With this column, the time part of a datetime value is ignored when you filter the column.  
So, if you filter the column with the value `2012-04-26` or `2012-04-26 12:23:45` and with the operator `=`, the query will be `date >= '2012-04-26 0:00:00' AND date <= '2012-04-26 23:59:59'`.
**Note**: We don't use the between operator because its behavior isn't the same for each database.


## Annotation

See [DateTime Column](datetime_column.md#annotation).

## Filter

See [DateTime Column](datetime_column.md#filter).

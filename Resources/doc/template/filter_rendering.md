Filter rendering
================

Filter rendering in the grid is handled by specific blocks in your template.  
The following parameters are passed to the block `grid_column_type_%column_type%_filter`:

## Block parameters

|Parameter|Type|Description|
|:--|:--|:--|
|grid|APY/DataGridBundle/Grid/Grid|The grid object|
|column|APY/DataGridBundle/Grid/Column/Colomn|The column currently being rendered|
|submitOnChange|boolean|For select filters|
|params|array|Additional parameters passed to the grid|

## Overriding block names (ordered)

You can override the default block `grid_column_type_%column_type%_filter` or use one of these following blocks.  
They are called before the default block.

 * `grid_%id%_column_id_%column_id%_filter`
 * `grid_%id%_column_type_%column_type%_filter`
 * `grid_%id%_column_type_%column_parent_type%_filter`
 * `grid_%id%_column_filter_type_%column_filter_type%`
 * `grid_column_id_%column_id%_filter`
 * `grid_column_type_%column_type%_filter`
 * `grid_column_type_%column_parent_type%_filter`
 * `grid_column_filter_type_%column_filter_type%`

**Note 1**: It is also possible to name blocks using `..._column_...` instead of `..._column_id_...`.
However this naming convention is not advised as it is ambiguous. It is only supported for backward compatibility.

**Note 2**: `.` and `:` characters in mapped field with a DQL aggregate function are replaced by an underscore.

## Examples

See [Create a filter](../columns_configuration/filters/create_filter.md)

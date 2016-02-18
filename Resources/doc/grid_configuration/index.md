# Grid Configuration with PHP

#### [Set the identifier of the grid](set_grid_identifier.md)
You can set the identifier of a grid to manage easily the grid with css and javascript for instance.

#### [Set the persistence of the grid](set_grid_persistence.md)
By default, filters, page and order are reset when you quit the page where your grid is. If you set to true the persistence, its parameters are kept until you close your web browser or you kill the session cookie yourself.

#### [Pagination](set_limits.md)
Define the selector of the number of items per page

#### [Set max results](set_max_results.md)
Set max results.

#### [Add column](add_column.md)
You can add a column to the grid. You can fill it with the row manipulator, in your template or tell the grid what field the column will be mapped.

#### [Add row action](add_row_action.md)
A row action is an action performed on the current row. It's represented by a route to a controller with the identifier of the row.

#### [Add multiple row actions columns](add_actions_column.md)
You can create other columns of row actions and choose the position of these ones.

#### [Add mass action](add_mass_action.md)
A mass action is like a row action but over many lines at the same time.

#### [Add a native delete mass action](add_delete_mass_action.md)
This mass action calls the delete method of the source.

#### [Add export](add_export.md)


#### [Manipulate rows data](manipulate_rows_data.md)
You can set a callback to manipulate the row of the grid.  

#### [Manipulate column render cell](manipulate_column_render_cell.md)
You can set a callback to manipulate the render of a cell. 

#### [Manipulate the source query](manipulate_query.md)
The Entity Source provides two ways of manipulating the query which is used for generating the grid.

#### [Manipulate the count query (source Entity only)](manipulate_count_query.md)
The grid requires the total number of results (COUNT (...)). The grid clones the source QueryBuilder and wraps it with a COUNT DISTINCT clause.

#### [Manipulate columns](manipulate_column.md)
You can manipulate the behavior of a column.

#### [Manipulate row action rendering](manipulate_row_action_rendering.md)
You can set a callback to manipulate the rendering of an action.

#### [Hide or show columns](hide_show_columns.md)
These functions are helpers to manipulate columns.

#### [Set columns order](set_columns_order.md)
You can already define the order of the columns with the columns option of the Source annotation.  

#### [Set a default page](set_default_page.md)
You can define a default page. This page will be used on each new session of the grid.

#### [Set a default order](set_default_order.md)
You can define a default order. This order will be used on each new session of the grid.

#### [Set a default items per page](set_default_limit.md)
You can define a default limit. This limit will be used on each new session of the grid.

#### [Set default filters](set_default_filters.md)
You can define default filters. These values will be used on each new session of the grid.

#### [Set permanent filters](set_permanent_filters.md)
You can define permanent filters. These values will be used every time and the filter part will be disable for columns which have a permanent filter.

#### [Set the no data message](set_no_data_message.md)
When you render a grid with no data in the source, the grid isn't displayed and a no data message is displayed.

#### [Set the no result message](set_no_result_message.md)
When you render a grid with no result after a filtering, a no result message is displayed in a unique row.

#### [Always show the grid](always_show_grid.md)
When you render a grid with no data in the source, the grid isn't displayed and a no data message is displayed.

#### [Set a title prefix](set_prefix_titles.md)
You can define a prefix title for all columns of the grid.

#### [Set the size of the actions colum](set_size_actions_column.md)
Set the size of the actions column.

#### [Set the title of the actions colum](set_title_actions_column.md)
Set the title of the actions column.

#### [Set data to avoid calling the database](set_data.md)
You can use fetched data to avoid unnecessary queries.

#### [Grid Response helper](grid_response.md)
The getGridResponse method is an helper which manage the redirection, export and the rendering of the grid.  

#### [Multi Grid manager](multi_grid_manager.md)
Handle multiple grids on the same page.

#### [Set the route of the grid](set_grid_route.md)
The route of a grid is automatically retrieved from the request. But when you render a controller which contains a grid from twig, the route cannot be retrieved so you have to define it.

#### [Working Example](working_example.md)

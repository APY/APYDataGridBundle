`7 May 2012`

 * Fix bug - Templates are not reset for each grid displayed
 * Fix bug - Count of selected visible rows when filters are displayed
 * Fix bug - params not initialised to an empty array in twig extension
 * Fix bug - Pager not visible when only one limit is defined
 * Fix bug - Set false to the filterable attribute of an ActionColumn
 * Add a way to pass attributes to RowActions

`5 May 2012`

 * Allow extended Grid class

`3 May 2012`

 * Add groupBy support on Source annotation

`2 May 2012`

 * Don't throw no data message when the grid is filtered
 * If noDataMessage = false, the grid is display empty with the noResultMessage
 * Add a no result message when a filtered grid has no result
 * Add last-column and last-row classes because the css :last-child isn't support by IE7
 * Fix input page setter (Doesn't work when you have a form before the grid)
 * Set to the first page if this is an order, limit, mass action or filter request

`1 May 2012`

 * [BC Break] Show a message instead of an empty grid
 * Add a way to pass twig variables to override blocks
 
`30 Apr 2012`

 * Add array Column for Entity sources
 * Fix createHash when you define a id for the grid
 * [BC BREAK] Don't use previous data if the client leave the page during the session. You can turn on the persistence if you want the opposite.
 * Fix initFilters method
 * Add initOrder method
 * Add show and hide columns methods
 * Fix render bloc ids and manipulate row field ids with mapping fields.
 
`29 Apr 2012`

 * Add Vector source
 * Add visible columns mask Helpers
 * Add support for additionnal route parameters on row actions

`23 Apr 2012`

 * Add groups support in annotation + doc

`21 Apr 2012`

 * Improve configuration doc
 * Set the default title of an associated field
 * Add support for multi columns on the same JoinColumn property + doc
 * Add groupBy feature with aggregate dql functions support in mapping association + doc

`20 Apr 2012`

 * add initFilters feature
 * fix custom actions column
 * fix exception not found with setData on the grid

`13 Apr 2012`

 * japanese translations

`12 Apr 2012`

 * applying filter now reloads grid to first page
 * new SourceSelect column type

`9 Apr 2012`

 * fix broken confirmation message
 * polish translations

`8 Apr 2012`

 * as far as symfony 2.0 lacks of some important features and 2.1dev is heavily unstable with too many changes every day (which causes too many problems form my company) i've decided to stop using symfony2 for my future projects it basically means that i'll no longer support this bundle, Abhoryo and nurikabe have rights to merge any PR for this bundle  


`20 Oct 2011`

 * new param for column called `role` * column is visible only when access is granted by security.context
 * removed column->hide() and column->show() and added column->isVisible() and column->setVisible(bool)

`19 Oct 2011`

 * when rendering template calling `prepare` function is not needed
 * removed custom route for filtering and ordering grid columns
 * added events to modify source data query building and data hydration
 * added column->setAlign('left/right/center')
 * support relations in Entity source
 * support custom mapping divers
 * form type guesser from Source column
 * own columns can be added by annotations
 * possible registrations of custom column types in container * look at Resources/Config/services
 * grid need to be created as service
 * annotations
 * ODM support
`30 Apr 2012`

 * Add array Column for Entity sources
 * Fix createHash when you define a id for the grid

`29 Apr 2012`

 * Add Vector source
 * Add visible columns Helper
 * Add support for additionnal route parameters  on row actions

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
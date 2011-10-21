beta 1:

 20. Oct 2011

 - new param for column called `role` - column is visible only when access is granted by security.context
 - removed column->hide() and column->show() and added column->isVisible() and column->setVisible(bool)

 19. Oct 2011

 - when rendering template calling `prepare` function is not needed
 - removed custom route for filtering and ordering grid columns
 - added events to modify source data query building and data hydration
 - added column->setAlign('left/right/center')
 - support relations in Entity source
 - support custom mapping divers
 - form type guesser from Source column
 - own columns can be added by annotations
 - possible registrations of custom column types in container - look at Resources/Config/services
 - grid need to be created as service
 - annotations
 - ODM support
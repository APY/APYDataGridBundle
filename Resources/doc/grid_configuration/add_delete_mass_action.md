Add a native delete mass action
===============================

This mass action calls the delete method of the source.
It doesn't work with the `Select All` option, only the visible rows can be delete with this simple mass action.

## Example
```php
<?php
use APY\DataGridBundle\Grid\Action\DeleteMassAction;
...
$grid->setSource($source);

$grid->addMassAction(new DeleteMassAction());
...
```

**Notes**: The primary field of the grid must be the same that the primary key of your source.
Don't use this mass action with the 'one' Entity or Document of a one-to-many relation.

Set the default items per page of the grid
==========================================

You can define a default limit. This limit will be used on each new session of the grid.  
If no default limit is defined, the grid take the first limit found in the limits values.  

## Example
```php
<?php
...
// Set the source
$grid->setSource($source);

// Set the limits
$grid->setLimits(array(5, 10, 15));

// Set the default limit
$grid->setDefaultLimit(15);
...
```

**Note**: The default limit must be positive and found in limits values.
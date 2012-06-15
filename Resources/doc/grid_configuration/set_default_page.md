Set the default page of the grid
===================================

You can define a default page. This page will be used on each new session of the grid.
If the default page is greater than the number of page, the page is set to 1.

## Exemple
```php
<?php
...
// Set the default page
$grid->setPage(4);

// Set the source
$grid->setSource($source);
...
```

**Note**: This parameter must be defined before the source.
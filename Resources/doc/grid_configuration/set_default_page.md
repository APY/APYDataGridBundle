Set the default page of the grid
===================================

You can define a default page. This page will be used on each new session of the grid.  
If the default page is greater than the number of page, the page is set to 1.

## Example
```php
<?php
...
// Set the source
$grid->setSource($source);

// Set the default page
$grid->setDefaultPage(4);
...
```
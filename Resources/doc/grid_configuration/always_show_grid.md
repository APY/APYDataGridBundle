Always show the grid when no data is found
==========================================

When you render a grid with no data in the source, the grid isn't displayed and a no data message is displayed.  
See [Set no data message](set_no_data_message.md).

With this same method, you can also tell the bundle to always display the grid with no row.

```php
<?php
...
$grid->setSource($source);

$grid->setNoDataMessage(false);
...
```

## Set the default no data message in your config.yml
```yml
apy_data_grid:
    no_data_message: false
```
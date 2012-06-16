PHPExcel installation
=====================

1. Download the library from the [PHPExcel website](http://phpexcel.codeplex.com/releases/).

1. Unzip the archive and put the `lib` directory in `vendor/phpexcel/` directory.

1. Register the prefix in the file `app/autoload.php`.

```php
<?php
...
$loader->registerPrefixes(array(
    'Twig_Extensions_' => __DIR__.'/../vendor/twig-extensions/lib',
    'Twig_'            => __DIR__.'/../vendor/twig/lib',
    ...
    'PHPExcel'         => __DIR__.'/../vendor/phpexcel/lib/Classes',
));
...
```

1. Clear your cache.
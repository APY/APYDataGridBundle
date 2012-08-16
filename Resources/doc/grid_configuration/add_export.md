Add an export
=============

## Usage
```php
<?php
use APY\DataGridBundle\Grid\Action\RowAction;
...
$grid->setSource($source);

$grid->addExport($export);
...
```

## Example
```php
<?php
use  APY\DataGridBundle\Grid\Export\XmlExport;
...
$grid->setSource($source);

$grid->addExport(new XMLExport('XML Export', 'export'));
...
```

See the [Export](../export/) chapter for additionnal information
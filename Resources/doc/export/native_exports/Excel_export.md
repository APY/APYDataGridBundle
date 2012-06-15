Excel export
============

**Warning**: This export produces a warning with new Office Excel.

File extension = `xls`
Mime type = `application/vnd.ms-excel`

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Export\ExcelExport; 
...
$grid->addExport(new ExcelExport($title, $fileName, $params, $charset));

$grid->setSource($source);
...
```

**Note**: This parameter must be defined before the source.

#### ExcelExport::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|title|string||Title of the export in the selector.|
|fileName|string|export|Name of the export file without the extension.|
|params|array|array()|Additionnal parameters.|
|charset|string|UTF-8|Charset to convert the ouput of the export.|

## Additional parameters for the export

_None_

## Exemple
```php
<?php
...
use APY\DataGridBundle\Grid\Export\ExcelExport; 
...
$grid->addExport(new ExcelExport('Excel Export'));

$grid->setSource($source);
...
```

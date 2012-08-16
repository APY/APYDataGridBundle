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
$grid->setSource($source);

$grid->addExport(new ExcelExport($title, $fileName, $params, $charset, $role));
...
```

#### ExcelExport::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|title|string||Title of the export in the selector.|
|fileName|string|export|Name of the export file without the extension.|
|params|array|array()|Additionnal parameters.|
|charset|string|UTF-8|Charset to convert the ouput of the export.|
|role|mixed|null|Don't add this export if the access isn't granted for the defined role(s)|

## Additional parameters for the export

_None_

## Example
```php
<?php
...
use APY\DataGridBundle\Grid\Export\ExcelExport;
...
$grid->setSource($source);

$grid->addExport(new ExcelExport('Excel Export'));
...
```

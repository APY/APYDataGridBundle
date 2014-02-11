Delimiter-Separated Values export
=================================

File extension = _none_
Mime type = `application/octet-stream`
Delimiter = _none_

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Export\DSVExport;
...
$grid->setSource($source);

$grid->addExport(new DSVExport($title, $fileName, $params, $charset, $role));
...
```

#### DSVExport::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|title|string||Title of the export in the selector.|
|fileName|string|export|Name of the export file without the extension.|
|params|array|array()|Additionnal parameters (Delimiter and BOM).|
|charset|string|UTF-8|Charset to convert the ouput of the export.|
|role|mixed|null|Don't add this export if the access isn't granted for the defined role(s)|

## Additional parameters for the export

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|delimiter|string|__empty__|The delimiter of csv columns.|

## Examples
```php
<?php
...
use APY\DataGridBundle\Grid\Export\DSVExport;
...
$grid->setSource($source);

$exporter = new DSVExport('DSV Export with , without BOM characters', 'export', array('delimiter' => ',', 'withBOM' => false));
$exporter->setFileExtension('csv');
$exporter->setMimeType('text/comma-separated-values');

$grid->addExport($exporter);
...
```

OR

```php
<?php
...
use APY\DataGridBundle\Grid\Export\DSVExport;
...
$grid->setSource($source);

$exporter = new DSVExport('DSV Export with ,');
$exporter->setDelimiter(',');
$exporter->setWithBOM(false);
$exporter->setFileName('export');
$exporter->setFileExtension('csv');
$exporter->setMimeType('text/comma-separated-values');

$grid->addExport($exporter);
...
```

Output:

```
Book name<delimiter>page<delimiter>
This summer<delimiter>300<delimiter>
"Sea<delimiter> sex and sun"<delimiter>300<delimiter>
"He said ""Hello world!"""<delimiter>550<delimiter>
```
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

$grid->addExport(new DSVExport($title, $fileName, $params, $charset));
...
```

#### DSVExport::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|title|string||Title of the export in the selector.|
|fileName|string|export|Name of the export file without the extension.|
|params|array|array()|Additionnal parameters.|
|charset|string|UTF-8|Charset to convert the ouput of the export.|

## Additional parameters for the export

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|delimiter|string|__empty__|The delimiter of csv columns.|

## Exemples
```php
<?php
...
use APY\DataGridBundle\Grid\Export\DSVExport; 
...
$grid->setSource($source);

$grid->addExport(new DSVExport('DSV Export with ,', 'export', array('delimiter' => ',')));
$grid->setFileExtension('csv');
$grid->setMimeType('text/comma-separated-values');
...
```

OR

```php
<?php
...
use APY\DataGridBundle\Grid\Export\DSVExport; 
...
$grid->setSource($source);

$grid->addExport(new DSVExport('DSV Export with ,'));
$grid->setDelimiter(',');
$grid->setFileName('export');
$grid->setFileExtension('csv');
$grid->setMimeType('text/comma-separated-values');
...
```

Output:

```
Book name<delimiter>page<delimiter>
This summer<delimiter>300<delimiter>
"Sea<delimiter> sex and sun"<delimiter>300<delimiter>
"He said ""Hello world!"""<delimiter>550<delimiter>
```
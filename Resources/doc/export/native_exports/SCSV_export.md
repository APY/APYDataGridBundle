Semi-Colon-Separated Values export
=================================

File extension = `csv`  
Mime type = `text/comma-separated-values`  
Delimiter = `;`

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Export\SCSVExport; 
...
$grid->addExport(new SCSVExport($title, $fileName, $params, $charset));

$grid->setSource($source);
...
```

**Note**: This parameter must be defined before the source.

#### SCSVExport::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|title|string||Title of the export in the selector.|
|fileName|string|export|Name of the export file without the extension.|
|params|array|array()|Additionnal parameters.|
|charset|string|UTF-8|Charset to convert the ouput of the export.|

## Additional parameters for the export

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|delimiter|string|;|The delimiter of csv columns.|

## Exemple
```php
<?php
...
use APY\DataGridBundle\Grid\Export\DSVExport; 
...
$grid->addExport(new SCSVExport('SCCSV Export'));

$grid->setSource($source);
...
```

Output:

```
Book name;page;
This summer;300;
"Sea; sex and sun";300;
"He said ""Hello world!""";550;
```

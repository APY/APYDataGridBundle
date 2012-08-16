Comma-Separated Values export
=================================

File extension = `csv`
Mime type = `text/comma-separated-values`
Delimiter = `,`

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Export\CSVExport;
...
$grid->setSource($source);

$grid->addExport(new CSVExport($title, $fileName, $params, $charset, $role));
...
```

#### CSVExport::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|title|string||Title of the export in the selector.|
|fileName|string|export|Name of the export file without the extension.|
|params|array|array()|Additionnal parameters.|
|charset|string|UTF-8|Charset to convert the ouput of the export.|
|role|mixed|null|Don't add this export if the access isn't granted for the defined role(s)|

## Additional parameters for the export

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|delimiter|string|,|The delimiter of csv columns.|

## Example
```php
<?php
...
use APY\DataGridBundle\Grid\Export\CSVExport;
...
$grid->setSource($source);

$grid->addExport(new CSVExport('CSV Export'));
...
```

Output:

```
Book name,page,
This summer,300,
"Sea, sex and sun",300,
"He said ""Hello world!""",550,
```

JSON export
===========

File extension = `json`
Mime type = `application/octet-stream`

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Export\JSONExport;
...
$grid->setSource($source);

$grid->addExport(new JSONExport($title, $fileName, $params, $charset, $role));
...
```

#### JSONExport::__construct parameters

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
use APY\DataGridBundle\Grid\Export\JSONExport;
...
$grid->setSource($source);

$grid->addExport(new JSONExport('JSON Export'));
...
```

Output:
```
{"titles":{"text":"Text","datetime":"Datetime"},"rows":[{"text":"abcde","datetime":"Jun 14, 2012 12:01:16 AM"},{"text":"bcdef","datetime":"Jun 14, 2012 12:01:16 AM"}]}
```

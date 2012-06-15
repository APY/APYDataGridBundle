XML export
==========

File extension = `xml`
Mime type = `application/octet-stream`

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Export\XMLExport; 
...
$grid->addExport(new XMLExport($title, $fileName, $params, $charset));

$grid->setSource($source);
...
```

**Note**: This parameter must be defined before the source.

#### XMLExport::__construct parameters

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
use APY\DataGridBundle\Grid\Export\XMLExport; 
...
$grid->addExport(new XMLExport('XML Export'));

$grid->setSource($source);
...
```

Output:
```xml
<?xml version="1.0"?>
<grid>
	<titles>
		<text><![CDATA[text]]></text>
		<datetime><![CDATA[datetime]]></datetime>
	</titles>
	<rows>
		<row>
			<text><![CDATA[abcde]]></text>
			<datetime><![CDATA[Jun 14, 2012 12:01:16 AM]]></datetime>
		</row>
		<row>
			<text><![CDATA[abcde]]></text>
			<datetime><![CDATA[Jun 14, 2012 12:01:16 AM]]></datetime>
		</row>
	</rows>
</grid>
```

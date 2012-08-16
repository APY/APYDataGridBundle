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
$grid->setSource($source);

$grid->addExport(new XMLExport($title, $fileName, $params, $charset, $role));
...
```

#### XMLExport::__construct parameters

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
use APY\DataGridBundle\Grid\Export\XMLExport;
...
$grid->setSource($source);

$grid->addExport(new XMLExport('XML Export'));
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

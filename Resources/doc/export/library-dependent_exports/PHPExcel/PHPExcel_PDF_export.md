PHPExcel simple PDF export (Not working!)
=========================================

File extension = `pdf`
Mime type = `application/pdf`

**Note**: This export is limited to 52 columns.

## Usage
```php
<?php
...
use APY\DataGridBundle\Grid\Export\PHPExcelMPDFExport;
...
$grid->setSource($source);

$grid->addExport(new PHPExcelMPDFExport($title, $fileName, $params, $charset, $role));
...
```

#### PHPExcelPDFExport::__construct parameters

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
$grid->setSource($source);

$grid->addExport(new PHPExcelPDFExport('Simple PDF Export'));
...
```

## Configure the export

This export provides the object `objPHPExcel`. You can manipulate this PHPExcel object.
See the ducmentation `PHPExcel developer documentation.doc` on the [official website](http://phpexcel.codeplex.com/)


```php
<?php
...
$grid->setSource($source);

$export = new PHPExcelPDFExport('Simple PDF Export');

$export->objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
$export->objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
$export->objPHPExcel->getProperties()->setTitle("Office Test Document");
$export->objPHPExcel->getProperties()->setSubject("Office Test Document");
$export->objPHPExcel->getProperties()->setDescription("Test document for Office, generated using PHP classes.");
$export->objPHPExcel->getProperties()->setKeywords("office php");
$export->objPHPExcel->getProperties()->setCategory("Test result file");

$export->objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$export->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$export->objPHPExcel->getActiveSheet()->getPageSetup()->setScale(50);

$grid->addExport(export);
...
```

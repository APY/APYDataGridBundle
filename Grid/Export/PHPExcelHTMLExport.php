<?php

namespace APY\DataGridBundle\Grid\Export;

use PhpOffice\PhpSpreadsheet\Writer\Html;

class PHPExcelHTMLExport extends PHPExcelExport
{
    protected $fileExtension = "html";
    protected $mimeType = "text/html";

    protected function getWriter(): Html
    {
        $writer = new Html($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}
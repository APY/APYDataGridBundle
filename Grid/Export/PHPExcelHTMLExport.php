<?php

namespace APY\DataGridBundle\Grid\Export;

/**
 * PHPExcel HTML Export.
 */
class PHPExcelHTMLExport extends PHPExcel5Export
{
    protected ?string $fileExtension = 'html';

    protected string $mimeType = 'text/html';

    protected function getWriter(): \PHPExcel_Writer_HTML
    {
        $writer = new \PHPExcel_Writer_HTML($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}

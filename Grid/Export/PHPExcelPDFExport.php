<?php

namespace APY\DataGridBundle\Grid\Export;

/**
 * PHPExcel PDF Export.
 */
class PHPExcelPDFExport extends PHPExcel5Export
{
    protected ?string $fileExtension = 'pdf';

    protected string $mimeType = 'application/pdf';

    protected function getWriter(): \PHPExcel_Writer_PDF
    {
        // $this->objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        // $this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        // $this->objPHPExcel->getActiveSheet()->getPageSetup()->setScale(50);
        $writer = new \PHPExcel_Writer_PDF($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);
        // $writer->setSheetIndex(0);
        // $writer->setPaperSize("A4");
        $writer->writeAllSheets();

        return $writer;
    }
}

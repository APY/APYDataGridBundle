<?php

namespace APY\DataGridBundle\Grid\Export;


use PhpOffice\PhpSpreadsheet\Writer\Pdf\Mpdf;

class PHPExcelMPDFExport extends PHPExcelPDFExport
{
    protected function getWriter(): Mpdf
    {
        $writer = new Mpdf($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}
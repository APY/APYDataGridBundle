<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Export;

/**
 * PHPExcel PDF Export
 */
class PHPExcelPDFExport extends PHPExcel5Export
{
    protected $fileExtension = 'pdf';

    protected $mimeType = 'application/pdf';

    protected function getWriter()
    {
        //$this->objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        //$this->objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        //$this->objPHPExcel->getActiveSheet()->getPageSetup()->setScale(50);
        $writer = new \PHPExcel_Writer_PDF($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);
        //$writer->setSheetIndex(0);
        //$writer->setPaperSize("A4");
        $writer->writeAllSheets();

        return $writer;
    }
}

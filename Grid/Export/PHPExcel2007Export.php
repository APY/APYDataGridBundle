<?php

namespace APY\DataGridBundle\Grid\Export;

/**
 * PHPExcel 2007 Export.
 */
class PHPExcel2007Export extends PHPExcel5Export
{
    protected ?string $fileExtension = 'xlsx';

    protected string $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    protected function getWriter(): \PHPExcel_Writer_Excel2007
    {
        $writer = new \PHPExcel_Writer_Excel2007($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}

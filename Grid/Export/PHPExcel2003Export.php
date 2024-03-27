<?php

namespace APY\DataGridBundle\Grid\Export;

/**
 * PHPExcel_Excel 2003 Export (.xlsx).
 */
class PHPExcel2003Export extends PHPExcel2007Export
{
    protected function getWriter(): \PHPExcel_Writer_Excel2007
    {
        $writer = parent::getWriter();
        $writer->setOffice2003Compatibility(true);

        return $writer;
    }
}

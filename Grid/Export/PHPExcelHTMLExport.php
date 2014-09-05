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
 * PHPExcel HTML Export
 */
class PHPExcelHTMLExport extends PHPExcel5Export
{
    protected $fileExtension = 'html';

    protected $mimeType = 'text/html';

    protected function getWriter()
    {
        $writer = new \PHPExcel_Writer_HTML($this->objPHPExcel);
        $writer->setPreCalculateFormulas(false);

        return $writer;
    }
}

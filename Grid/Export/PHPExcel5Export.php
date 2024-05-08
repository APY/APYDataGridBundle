<?php

/**
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Export;

use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

/**
 * PHPExcel 5 Export (97-2003) (.xls)
 * 52 columns maximum.
 */
class PHPExcel5Export extends PHPExcelExport
{
    protected $fileExtension = 'xls';

    protected $mimeType = 'application/vnd.ms-excel';

    protected function getWriter(): IWriter
    {
        return new Xls($this->objPHPExcel);
    }
}

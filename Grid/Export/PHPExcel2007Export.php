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

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * PHPExcel 2007 Export.
 */
class PHPExcel2007Export extends PHPExcelExport
{
    protected $fileExtension = 'xlsx';

    protected $mimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    protected function getWriter(): Xlsx
    {
        return new Xlsx($this->objPHPExcel);
    }
}

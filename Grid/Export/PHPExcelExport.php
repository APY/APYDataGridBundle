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

use APY\DataGridBundle\Grid\Grid;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\IWriter;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

/**
 * Excel
 */
class PHPExcelExport extends Export
{
    protected $fileExtension = 'xls';

    protected $mimeType = 'application/vnd.ms-excel';

    protected Spreadsheet $objPHPExcel;

    public function __construct($title, $fileName = 'export', $params = [], $charset = 'UTF-8', $role = null)
    {
        parent::__construct($title, $fileName, $params, $charset, $role);
        $this->objPHPExcel = new Spreadsheet;
    }

    public function computeData(Grid $grid): void
    {

        $data = $this->getFlatGridData($grid);

        $row = 1;
        foreach ($data as $line) {
            $column = 'A';
            foreach ($line as $cell) {
                $this->objPHPExcel->getActiveSheet()->SetCellValue($column . $row, $cell);

                ++$column;
            }
            ++$row;
        }

        $objWriter = $this->getWriter();

        ob_start();

        $objWriter->save('php://output');

        $this->content = ob_get_contents();

        ob_end_clean();
    }

    protected function getWriter(): IWriter
    {
        return new Xls($this->objPHPExcel);
    }
}
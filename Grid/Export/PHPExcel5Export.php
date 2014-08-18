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
 * PHPExcel 5 Export (97-2003) (.xls)
 * 52 columns maximum
 */
class PHPExcel5Export extends Export
{
    protected $fileExtension = 'xls';

    protected $mimeType = 'application/vnd.ms-excel';

    public $objPHPExcel;

    public function __construct($tilte, $fileName = 'export', $params = array(), $charset = 'UTF-8')
    {
        $this->objPHPExcel =  new \PHPExcel();

        parent::__construct($tilte, $fileName, $params, $charset);
    }

    public function computeData($grid)
    {
        $data = $this->getFlatGridData($grid);

        $row = 1;
        foreach ($data as $line) {
            $column = 'A';
            foreach ($line as $cell) {
                $this->objPHPExcel->getActiveSheet()->SetCellValue($column.$row, $cell);

                $column++;
            }
            $row++;
        }

        $objWriter = $this->getWriter();

        ob_start();

        $objWriter->save("php://output");

        $this->content = ob_get_contents();

        ob_end_clean();
    }

    protected function getWriter()
    {
        return new \PHPExcel_Writer_Excel5($this->objPHPExcel);
    }
}

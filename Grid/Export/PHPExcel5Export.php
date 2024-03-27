<?php

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\GridInterface;

/**
 * PHPExcel 5 Export (97-2003) (.xls)
 * 52 columns maximum.
 */
class PHPExcel5Export extends Export
{
    protected ?string $fileExtension = 'xls';

    protected string $mimeType = 'application/vnd.ms-excel';

    public \PHPExcel $objPHPExcel;

    public function __construct(string $title, string $fileName = 'export', array $params = [], string $charset = 'UTF-8')
    {
        $this->objPHPExcel = new \PHPExcel();

        parent::__construct($title, $fileName, $params, $charset);
    }

    public function computeData(GridInterface $grid): void
    {
        $data = $this->getFlatGridData($grid);

        $row = 1;
        foreach ($data as $line) {
            $column = 'A';
            foreach ($line as $cell) {
                $this->objPHPExcel->getActiveSheet()->SetCellValue($column.$row, $cell);

                ++$column;
            }
            ++$row;
        }

        $objWriter = $this->getWriter();

        \ob_start();

        $objWriter->save('php://output');

        $this->content = \ob_get_clean();
    }

    protected function getWriter(): \PHPExcel_Writer_Excel5
    {
        return new \PHPExcel_Writer_Excel5($this->objPHPExcel);
    }
}

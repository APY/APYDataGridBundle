<?php

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\GridInterface;

/**
 * Excel (This export produces a warning with new Office Excel).
 */
class ExcelExport extends Export
{
    protected ?string $fileExtension = 'xls';

    protected string $mimeType = 'application/vnd.ms-excel';

    public function computeData(GridInterface $grid): void
    {
        $data = $this->getGridData($grid);

        $this->content = '<table border=1>';
        if (isset($data['titles'])) {
            $this->content .= '<tr>';
            foreach ($data['titles'] as $title) {
                $this->content .= \sprintf('<th>%s</th>', \htmlentities($title, \ENT_QUOTES));
            }
            $this->content .= '</tr>';
        }

        foreach ($data['rows'] as $row) {
            $this->content .= '<tr>';
            foreach ($row as $cell) {
                $this->content .= \sprintf('<td>%s</td>', \htmlentities($cell, \ENT_QUOTES));
            }
            $this->content .= '</tr>';
        }

        $this->content .= '</table>';
    }
}

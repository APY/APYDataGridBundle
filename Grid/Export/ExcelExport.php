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
 * Excel (This export produces a warning with new Office Excel)
 */
class ExcelExport extends Export
{
    protected $fileExtension = 'xls';

    protected $mimeType = 'application/vnd.ms-excel';

    public function computeData($grid)
    {
        $data = $this->getGridData($grid);

        $this->content = '<table border=1>';
        if (isset($data['titles'])) {
            $this->content .= '<tr>';
            foreach ($data['titles'] as $title) {
                $this->content .= sprintf("<th>%s</th>", htmlentities($title, ENT_QUOTES));
            }
            $this->content .= '</tr>';
        }

        foreach ($data['rows'] as $row) {
            $this->content .= '<tr>';
            foreach ($row as $cell) {
                $this->content .= sprintf("<td>%s</td>", htmlentities($cell, ENT_QUOTES));
            }
            $this->content .= '</tr>';
        }

        $this->content .= '</table>';
    }
}

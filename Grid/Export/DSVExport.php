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
 *
 * Delimiter-Separated Values
 *
 */
class DSVExport extends Export
{
    protected $fileExtension = null;

    protected $mimeType = 'application/octet-stream';

    protected $delimiter = '';

    protected $withBOM = true;

    public function __construct($title, $fileName = 'export', $params = array(), $charset = 'UTF-8')
    {
        $this->delimiter = isset($params['delimiter']) ? $params['delimiter'] : $this->delimiter;
        $this->withBOM = isset($params['withBOM']) ? $params['withBOM'] : $this->withBOM;

        parent::__construct($title, $fileName, $params, $charset);
    }

    public function computeData($grid)
    {
        $data = $this->getFlatGridData($grid);

        // Array to dsv
        $outstream = fopen("php://temp", 'r+');

        foreach ($data as $line) {
            fputcsv($outstream, $line, $this->delimiter, '"');
        }

        rewind($outstream);

        $content = $this->withBOM ? "\xEF\xBB\xBF" : '';

        while (($buffer = fgets($outstream)) !== false) {
            $content .= $buffer;
        }

        fclose($outstream);

        $this->content = $content;
    }

    /**
     * get delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * set delimiter
     *
     * @param string $separator
     *
     * @return self
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * get BOM setting
     *
     * @return string
     */
    public function getWithBOM()
    {
        return $this->withBOM;
    }

    /**
     * set BOM setting
     *
     * @param string $withBOM
     *
     * @return self
     */
    public function setWithBOM($withBOM)
    {
        $this->withBOM = $withBOM;

        return $this;
    }
}

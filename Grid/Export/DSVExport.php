<?php

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\GridInterface;

/**
 * Delimiter-Separated Values.
 */
class DSVExport extends Export
{
    protected ?string $fileExtension = null;

    protected string $delimiter = '';

    protected bool $withBOM = true;

    public function __construct(string $title, string $fileName = 'export', array $params = [], string $charset = 'UTF-8')
    {
        $this->setDelimiter($params['delimiter'] ?? $this->delimiter)
            ->setWithBOM($params['withBOM'] ?? $this->withBOM);

        parent::__construct($title, $fileName, $params, $charset);
    }

    public function computeData(GridInterface $grid): void
    {
        $data = $this->getFlatGridData($grid);

        // Array to dsv
        $outstream = \fopen('php://temp', 'r+b');

        foreach ($data as $line) {
            \fputcsv($outstream, $line, $this->getDelimiter(), '"');
        }

        \rewind($outstream);

        $content = $this->getWithBOM() ? "\xEF\xBB\xBF" : '';

        while (($buffer = \fgets($outstream)) !== false) {
            $content .= $buffer;
        }

        \fclose($outstream);

        $this->content = $content;
    }

    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    public function setDelimiter(string $delimiter): static
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function getWithBOM(): bool
    {
        return $this->withBOM;
    }

    public function setWithBOM(bool $withBOM): static
    {
        $this->withBOM = $withBOM;

        return $this;
    }
}

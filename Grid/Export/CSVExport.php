<?php

namespace APY\DataGridBundle\Grid\Export;

/**
 * Comma-Separated Values.
 */
class CSVExport extends DSVExport
{
    protected ?string $fileExtension = 'csv';

    protected string $mimeType = 'text/comma-separated-values';

    protected string $delimiter = ',';
}

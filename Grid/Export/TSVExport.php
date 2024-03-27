<?php

namespace APY\DataGridBundle\Grid\Export;

/**
 * Tab-Separated Values.
 */
class TSVExport extends DSVExport
{
    protected ?string $fileExtension = 'tsv';

    protected string $mimeType = 'text/tab-separated-values';

    protected string $delimiter = "\t";
}

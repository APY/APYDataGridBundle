<?php

namespace APY\DataGridBundle\Grid\Export;

/**
 * Semi-Colon-Separated Values.
 */
class SCSVExport extends CSVExport
{
    protected string $delimiter = ';';
}

<?php

namespace APY\DataGridBundle\Grid\Export;

use APY\DataGridBundle\Grid\GridInterface;
use Symfony\Component\HttpFoundation\Response;

interface ExportInterface
{
    /**
     * function call by the grid to fill the content of the export.
     */
    public function computeData(GridInterface $grid): void;

    public function getResponse(): Response;

    public function getTitle(): string;

    public function getRole(): ?string;
}

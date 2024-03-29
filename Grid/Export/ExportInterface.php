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

use APY\DataGridBundle\Grid\Grid;
use Symfony\Component\HttpFoundation\Response;

interface ExportInterface
{
    /**
     * function call by the grid to fill the content of the export.
     *
     * @param Grid $grid The grid
     */
    public function computeData(Grid $grid);

    /**
     * Get the export Response.
     *
     * @return Response
     */
    public function getResponse(): Response;

    /**
     * Get the export title.
     *
     * @return string
     */
    public function getTitle(): string;

    /**
     * Get the export role.
     *
     * @return mixed
     */
    public function getRole();
}

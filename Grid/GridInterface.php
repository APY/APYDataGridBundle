<?php
namespace APY\DataGridBundle\Grid;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface GridInterface
 *
 * @package Grid
 * @author  Quentin Ferrer
 */
interface GridInterface
{
    /**
     * Initializes the grid.
     *
     * @return $this
     */
    public function initialize();

    /**
     * Handles filters, sorts, exports, ... .
     *
     * @param Request $request The request
     *
     * @return void
     */
    public function handleRequest(Request $request);
}

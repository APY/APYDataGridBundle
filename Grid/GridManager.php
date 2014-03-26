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

namespace APY\DataGridBundle\Grid;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class GridManager implements \IteratorAggregate, \Countable
{
    protected $container;

    protected $grids;

    protected $routeUrl = null;

    protected $exportGrid = null;

    protected $massActionGrid = null;

    public function __construct($container)
    {
        $this->container = $container;
        $this->grids = new \SplObjectStorage();
    }

    public function getIterator()
    {
        return $this->grids;
    }

    public function count()
    {
        return $this->grids->count();
    }

    /**
     * @param mixed $id
     * @return Grid
     */
    public function createGrid($id = null)
    {
        $grid = $this->container->get('grid');

        if ($id !== null) {
            $grid->setId($id);
        }

        $this->grids->attach($grid);

        return $grid;
    }

    public function isReadyForRedirect()
    {
        if ($this->grids->count() == 0) {
            throw new \RuntimeException('No grid has been added to the manager.');
        }

        $checkHash = array();

        $isReadyForRedirect = false;
        $this->grids->rewind();

        // Route url is the same for all grids
        if ($this->routeUrl === null) {
            $grid = $this->grids->current();
            $this->routeUrl = $grid->getRouteUrl();
        }

        while ($this->grids->valid()) {
            $grid = $this->grids->current();

            if ($grid->isReadyForRedirect()) {
                $isReadyForRedirect = true;
            }

            if (in_array($grid->getHash(), $checkHash)) {
                throw new \RuntimeException('Some grids seem similar. Please set an Indentifier for your grids.');
            }

            $checkHash[] = $grid->getHash();

            $this->grids->next();
        }

        return $isReadyForRedirect;
    }

    public function isReadyForExport()
    {
        if ($this->grids->count() == 0) {
            throw new \RuntimeException('No grid has been added to the manager.');
        }

        $checkHash = array();

        $this->grids->rewind();
        while ($this->grids->valid()) {
            $grid = $this->grids->current();

            if (in_array($grid->getHash(), $checkHash)) {
                throw new \RuntimeException('Some grids seem similar. Please set an Indentifier for your grids.');
            }

            $checkHash[] = $grid->getHash();

            if ($grid->isReadyForExport()) {
                $this->exportGrid = $grid;

                return true;
            }

            $this->grids->next();
        }

        return false;
    }

    public function isMassActionRedirect()
    {
        $this->grids->rewind();
        while ($this->grids->valid()) {
            $grid = $this->grids->current();

            if ($grid->isMassActionRedirect()) {
                $this->massActionGrid = $grid;

                return true;
            }

            $this->grids->next();
        }

        return false;
    }

    /**
     * Renders a view.
     *
     * @param string|array $param1 The view name or an array of parameters to pass to the view
     * @param string|array $param1 The view name or an array of parameters to pass to the view
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function getGridManagerResponse($param1 = null, $param2 = null, Response $response = null)
    {
        $isReadyForRedirect = $this->isReadyForRedirect();

        if ($this->isReadyForExport()) {
            return $this->exportGrid->getExportResponse();
        }

        if ($this->isMassActionRedirect()) {
            return $this->massActionGrid->getMassActionResponse();
        }

        if ($isReadyForRedirect) {
            return new RedirectResponse($this->getRouteUrl());
        } else {
            if (is_array($param1) || $param1 === null) {
                $parameters = (array) $param1;
                $view = $param2;
            } else {
                $parameters = (array) $param2;
                $view = $param1;
            }

            $i = 1;
            $this->grids->rewind();
            while ($this->grids->valid()) {
                $parameters = array_merge(array('grid'.$i => $this->grids->current()), $parameters);
                $this->grids->next();
                $i++;
            }

            if ($view === null) {
                return $parameters;
            }

            return $this->container->get('templating')->renderResponse($view, $parameters, $response);
        }
    }

    public function getRouteUrl()
    {
        return $this->routeUrl;
    }

    public function setRouteUrl($routeUrl)
    {
        $this->routeUrl = $routeUrl;
    }
}

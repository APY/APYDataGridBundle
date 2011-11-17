<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid;

use Symfony\Component\HttpFoundation\RedirectResponse;

class GridManager implements \IteratorAggregate, \Countable
{
    private $container;

    /**
     * @var Grids[]
     */
    private $grids;

    private $routeUrl = null;

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

    public function createGrid()
    {
        $grid = $this->container->get('grid');

        // route url is the same for all grids
        if (is_null($this->routeUrl)) {
            $this->routeUrl = $grid->getRouteUrl();
        }

        $this->grids->attach($grid);

        return $grid;
    }

    public function isReadyForRedirect()
    {
        if ($this->grids->count()==0) {
            throw new \RuntimeException('No grid has been added to the manager.');
        }

        $checkHash = array();

        $this->grids->rewind();
        while($this->grids->valid()) {
            /* @var $grid Sorien\DataGridBundle\Grid\Grid */
            $grid = $this->grids->current();

            if (in_array($grid->getHash(), $checkHash))
            {
                throw new \RuntimeException('Some grids seem similar. Please set an Indentifier for your grids.');
            }
            else {
                $checkHash[] = $grid->getHash();
            }

            if ($grid->isReadyForRedirect()){
                return true;
            }

            $this->grids->next();
        }

        return false;
    }

    /**
     * Renders a view.
     *
     * @param array    $parameters An array of parameters to pass to the view
     * @param string   $view The view name
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function gridManagerResponse(array $parameters = array(), $view = null,  Response $response = null)
    {
        if ($this->isReadyForRedirect())
        {
            return new RedirectResponse($this->getRouteUrl());
        }
        else
        {
            if (is_null($view)) {
                return $parameters;
            }
            else {
                return $this->container->get('templating')->renderResponse($view, $parameters, $response);
            }
        }
    }

    public function getRouteUrl()
    {
        return $this->routeUrl;
    }
}

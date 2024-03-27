<?php

namespace APY\DataGridBundle\Grid;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class GridManager implements \IteratorAggregate, \Countable
{
    protected \SplObjectStorage $grids;

    protected mixed $routeUrl = null;

    protected mixed $exportGrid = null;

    protected mixed $massActionGrid = null;

    public const NO_GRID_EX_MSG = 'No grid has been added to the manager.';

    public const SAME_GRID_HASH_EX_MSG = 'Some grids seem similar. Please set an Indentifier for your grids.';

    public function __construct(private readonly Environment $twig)
    {
        $this->grids = new \SplObjectStorage();
    }

    public function getIterator(): \Traversable
    {
        return $this->grids;
    }

    public function count(): int
    {
        return $this->grids->count();
    }

    public function createGrid(GridInterface $grid, mixed $id = null): Grid
    {
        if (null !== $id) {
            $grid->setId($id);
        }

        $this->grids->attach($grid);

        return $grid;
    }

    public function isReadyForRedirect(): bool
    {
        if (0 === $this->grids->count()) {
            throw new \RuntimeException(self::NO_GRID_EX_MSG);
        }

        $checkHash = [];

        $isReadyForRedirect = false;
        $this->grids->rewind();

        // Route url is the same for all grids
        if (null === $this->routeUrl) {
            $grid = $this->grids->current();
            $this->routeUrl = $grid->getRouteUrl();
        }

        while ($this->grids->valid()) {
            $grid = $this->grids->current();

            if ($grid->isReadyForRedirect()) {
                $isReadyForRedirect = true;
            }

            if (\in_array($grid->getHash(), $checkHash, true)) {
                throw new \RuntimeException(self::SAME_GRID_HASH_EX_MSG);
            }

            $checkHash[] = $grid->getHash();

            $this->grids->next();
        }

        return $isReadyForRedirect;
    }

    public function isReadyForExport(): bool
    {
        if (0 === $this->grids->count()) {
            throw new \RuntimeException(self::NO_GRID_EX_MSG);
        }

        $checkHash = [];

        $this->grids->rewind();
        while ($this->grids->valid()) {
            $grid = $this->grids->current();

            if (\in_array($grid->getHash(), $checkHash, true)) {
                throw new \RuntimeException(self::SAME_GRID_HASH_EX_MSG);
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

    public function isMassActionRedirect(): bool
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
     * @param array|string|null $param1 The view name or an array of parameters to pass to the view
     * @param array|string|null $param2 The view name or an array of parameters to pass to the view
     *
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function getGridManagerResponse(array|string $param1 = null, array|string $param2 = null): Response|array
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
        }

        if (\is_array($param1) || null === $param1) {
            $parameters = (array) $param1;
            $view = $param2;
        } else {
            $parameters = (array) $param2;
            $view = $param1;
        }

        $i = 1;
        $this->grids->rewind();
        while ($this->grids->valid()) {
            $parameters = \array_merge(['grid'.$i => $this->grids->current()], $parameters);
            $this->grids->next();
            ++$i;
        }

        if (null === $view) {
            return $parameters;
        }

        $content = $this->twig->render($view, $parameters);

        return new Response($content);
    }

    public function getRouteUrl()
    {
        return $this->routeUrl;
    }

    public function setRouteUrl($routeUrl): void
    {
        $this->routeUrl = $routeUrl;
    }

    public function getMassActionGrid()
    {
        return $this->massActionGrid;
    }

    public function getExportGrid()
    {
        return $this->exportGrid;
    }
}

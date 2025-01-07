<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\InvalidArgumentException;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class GridBuilder extends GridConfigBuilder implements GridBuilderInterface
{
    private GridFactoryInterface $factory;

    /**
     * @var Column[]
     */
    private array $columns = [];

    public function __construct(
        private readonly RouterInterface $router,
        private readonly AuthorizationCheckerInterface $checker,
        private readonly ManagerRegistry $doctrine,
        private readonly Manager $manager,
        private readonly HttpKernelInterface $kernel,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        GridFactoryInterface $factory,
        string $name,
        array $options = [],
    ) {
        parent::__construct($name, $options);

        $this->factory = $factory;
    }

    public function add(string $name, Column|string $type, array $options = []): GridBuilderInterface|static
    {
        if (!$type instanceof Column) {
            $type = $this->factory->createColumn($name, $type, $options);
        }

        $this->columns[$name] = $type;

        return $this;
    }

    public function get(string $name): Column
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(\sprintf('The column with the name "%s" does not exist.', $name));
        }

        return $this->columns[$name];
    }

    public function has(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    public function remove(string $name): GridBuilderInterface|static
    {
        unset($this->columns[$name]);

        return $this;
    }

    public function getGrid(): Grid
    {
        $config = $this->getGridConfig();

        $grid = new Grid($this->router, $this->checker, $this->doctrine, $this->manager, $this->kernel, $this->twig, $this->requestStack, $config->getName(), $config);

        foreach ($this->columns as $column) {
            $grid->addColumn($column);
        }

        if (!empty($this->actions)) {
            foreach ($this->actions as $actions) {
                foreach ($actions as $action) {
                    $grid->addRowAction($action);
                }
            }
        }

        $grid->initialize();

        return $grid;
    }
}

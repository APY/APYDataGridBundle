<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Source\Source;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

readonly class GridFactory implements GridFactoryInterface
{
    public function __construct(
        private RouterInterface $router,
        private AuthorizationCheckerInterface $checker,
        private ManagerRegistry $doctrine,
        private Manager $manager,
        private HttpKernelInterface $kernel,
        private Environment $twig,
        private RequestStack $requestStack,
        private GridRegistryInterface $registry,
    ) {
    }

    public function create(GridTypeInterface|string|null $type = null, ?Source $source = null, array $options = []): Grid
    {
        return $this->createBuilder($type, $source, $options)->getGrid();
    }

    public function createBuilder(GridTypeInterface|string|null $type = 'grid', ?Source $source = null, array $options = []): GridBuilder
    {
        $type = $this->resolveType($type);
        $options = $this->resolveOptions($type, $source, $options);

        $builder = new GridBuilder($this->router, $this->checker, $this->doctrine, $this->manager, $this->kernel, $this->twig, $this->requestStack, $this, $type->getName(), $options);
        $builder->setType($type);

        $type->buildGrid($builder, $options);

        return $builder;
    }

    public function createColumn(string $name, Column|string $type, array $options = []): Column
    {
        if (!$type instanceof Column) {
            $column = clone $this->registry->getColumn($type);

            $column->__initialize(\array_merge([
                'id' => $name,
                'title' => $name,
                'field' => $name,
                'source' => true,
            ], $options));
        } else {
            $column = $type;
            $column->setId($name);
        }

        return $column;
    }

    private function resolveType(GridTypeInterface|string $type): GridTypeInterface
    {
        if (!$type instanceof GridTypeInterface) {
            $type = $this->registry->getType($type);
        }

        return $type;
    }

    private function resolveOptions(GridTypeInterface $type, ?Source $source = null, array $options = []): array
    {
        $resolver = new OptionsResolver();

        $type->configureOptions($resolver);

        if (null !== $source && !isset($options['source'])) {
            $options['source'] = $source;
        }

        return $resolver->resolve($options);
    }
}

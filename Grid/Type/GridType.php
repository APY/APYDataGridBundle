<?php

namespace APY\DataGridBundle\Grid\Type;

use APY\DataGridBundle\Grid\AbstractType;
use APY\DataGridBundle\Grid\GridBuilder;
use APY\DataGridBundle\Grid\Source\Source;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GridType extends AbstractType
{
    public function buildGrid(GridBuilder $builder, array $options = []): void
    {
        $builder
            ->setRoute($options['route'])
            ->setRouteParameters($options['route_parameters'])
            ->setPersistence($options['persistence'])
            ->setPage($options['page'])
            ->setMaxResults($options['max_results'])
            ->setMaxPerPage($options['max_per_page'])
            ->setFilterable($options['filterable'])
            ->setSortable($options['sortable'])
            ->setSortBy($options['sort_by'])
            ->setOrder($options['order'])
            ->setGroupBy($options['group_by']);

        if (!empty($options['source'])) {
            $builder->setSource($options['source']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'source' => null,
            'group_by' => null,
            'sort_by' => null,
            'order' => 'asc',
            'page' => 1,
            'route' => '',
            'route_parameters' => [],
            'persistence' => false,
            'max_per_page' => 10,
            'max_results' => null,
            'filterable' => true,
            'sortable' => true,
        ]);

        $allowedTypes = [
            'source' => ['null', Source::class],
            'group_by' => ['null', 'string', 'array'],
            'route_parameters' => 'array',
            'persistence' => 'bool',
            'filterable' => 'bool',
            'sortable' => 'bool',
        ];
        $allowedValues = [
            'order' => ['asc', 'desc'],
        ];
        foreach ($allowedTypes as $option => $types) {
            $resolver->setAllowedTypes($option, $types);
        }

        foreach ($allowedValues as $option => $values) {
            $resolver->setAllowedValues($option, $values);
        }
    }

    public function getName(): string
    {
        return 'grid';
    }
}

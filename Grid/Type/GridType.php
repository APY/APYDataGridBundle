<?php
namespace APY\DataGridBundle\Grid\Type;

use APY\DataGridBundle\Grid\AbstractType;
use APY\DataGridBundle\Grid\GridBuilder;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class for all grids type.
 *
 * @package APY\DataGridBundle
 * @author  Quentin Ferrer
 */
class GridType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildGrid(GridBuilder $builder, array $options = array())
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

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'source'           => null,
            'group_by'         => null,
            'sort_by'          => null,
            'order'            => 'asc',
            'page'             => 1,
            'route'            => '',
            'route_parameters' => array(),
            'persistence'      => false,
            'max_per_page'     => 10,
            'max_results'      => null,
            'filterable'       => true,
            'sortable'         => true,
        ));

        $resolver->setAllowedTypes('source', array('null', 'APY\DataGridBundle\Grid\Source\Source'));
        $resolver->setAllowedTypes('group_by', array('null', 'string', 'array'));
        $resolver->setAllowedTypes('route_parameters', 'array');
        $resolver->setAllowedTypes('persistence', 'bool');
        $resolver->setAllowedTypes('filterable', 'bool');
        $resolver->setAllowedTypes('sortable', 'bool');

        $resolver->setAllowedValues('order', array('asc', 'desc'));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'grid';
    }
}

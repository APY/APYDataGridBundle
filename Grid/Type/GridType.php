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

        $allowedTypes = array(
            'source' => array('null', 'APY\DataGridBundle\Grid\Source\Source'),
            'group_by' => array('null', 'string', 'array'),
            'route_parameters' => 'array',
            'persistence' => 'bool',
            'filterable' => 'bool',
            'sortable' => 'bool',
        );
        $allowedValues = array(
            'order' => array('asc', 'desc'),
        );
        if (method_exists($resolver, 'setDefault')) {
            // Symfony 2.6.0 and up
            foreach ($allowedTypes as $option => $types) {
                $resolver->setAllowedTypes($option, $types);
            }

            foreach ($allowedValues as $option => $values) {
                $resolver->setAllowedValues($option, $values);
            }
        } else {
            $resolver->setAllowedTypes($allowedTypes);
            $resolver->setAllowedValues($allowedValues);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'grid';
    }
}

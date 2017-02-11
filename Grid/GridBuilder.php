<?php

namespace APY\DataGridBundle\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\InvalidArgumentException;
use APY\DataGridBundle\Grid\Exception\UnexpectedTypeException;
use Symfony\Component\DependencyInjection\Container;

/**
 * A builder for creating Grid instances.
 *
 * @author  Quentin Ferrer
 */
class GridBuilder extends GridConfigBuilder implements GridBuilderInterface
{
    /**
     * The container.
     *
     * @var Container
     */
    private $container;

    /**
     * The factory.
     *
     * @var GridFactoryInterface
     */
    private $factory;

    /**
     * Columns of the grid builder.
     *
     * @var Column[]
     */
    private $columns = [];

    /**
     * Constructor.
     *
     * @param Container            $container The service container
     * @param GridFactoryInterface $factory   The grid factory
     * @param string               $name      The name of the grid
     * @param array                $options   The options of the grid
     */
    public function __construct(Container $container, GridFactoryInterface $factory, $name, array $options = [])
    {
        parent::__construct($name, $options);

        $this->container = $container;
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function add($name, $type, array $options = [])
    {
        if (!$type instanceof Column) {
            if (!is_string($type)) {
                throw new UnexpectedTypeException($type, 'string, APY\DataGridBundle\Grid\Column\Column');
            }

            $type = $this->factory->createColumn($name, $type, $options);
        }

        $this->columns[$name] = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function get($name)
    {
        if (!$this->has($name)) {
            throw new InvalidArgumentException(sprintf('The column with the name "%s" does not exist.', $name));
        }

        $column = $this->columns[$name];

        return $column;
    }

    /**
     * {@inheritdoc}
     */
    public function has($name)
    {
        return isset($this->columns[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($name)
    {
        unset($this->columns[$name]);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGrid()
    {
        $config = $this->getGridConfig();

        $grid = new Grid($this->container, $config->getName(), $config);

        foreach ($this->columns as $column) {
            $grid->addColumn($column);
        }

        if (!empty($this->actions)) {
            foreach ($this->actions as $columnId => $actions) {
                foreach ($actions as $action) {
                    $grid->addRowAction($action);
                }
            }
        }

        $grid->initialize();

        return $grid;
    }
}

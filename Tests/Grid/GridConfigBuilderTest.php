<?php

namespace APY\DataGridBundle\Grid\Tests;

use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\GridConfigBuilder;
use APY\DataGridBundle\Grid\GridTypeInterface;
use APY\DataGridBundle\Grid\Source\Source;
use PHPUnit\Framework\TestCase;

class GridConfigBuilderTest extends TestCase
{
    /** @var string */
    private $name = 'foo';

    /** @var array */
    private $options = ['foo' => 'foo', 'bar' => 'bar'];

    /** @var GridConfigBuilder */
    private $gridConfigBuilder;

    public function testGetName()
    {
        $this->assertEquals($this->name, $this->gridConfigBuilder->getName());
    }

    public function testSetSource()
    {
        $source = $this->createMock(Source::class);
        $this->gridConfigBuilder->setSource($source);

        $this->assertAttributeSame($source, 'source', $this->gridConfigBuilder);
    }

    public function testGetSource()
    {
        $source = $this->createMock(Source::class);
        $this->gridConfigBuilder->setSource($source);

        $this->assertSame($source, $this->gridConfigBuilder->getSource());
    }

    public function testSetType()
    {
        $type = $this->createMock(GridTypeInterface::class);
        $this->gridConfigBuilder->setType($type);

        $this->assertAttributeSame($type, 'type', $this->gridConfigBuilder);
    }

    public function testGetType()
    {
        $type = $this->createMock(GridTypeInterface::class);
        $this->gridConfigBuilder->setType($type);

        $this->assertSame($type, $this->gridConfigBuilder->getType());
    }

    public function testSetRoute()
    {
        $route = 'vendor.bundle.foo_route';
        $this->gridConfigBuilder->setRoute($route);

        $this->assertAttributeEquals($route, 'route', $this->gridConfigBuilder);
    }

    public function testGetRoute()
    {
        $route = 'vendor.bundle.foo_route';
        $this->gridConfigBuilder->setRoute($route);

        $this->assertEquals($route, $this->gridConfigBuilder->getRoute());
    }

    public function testSetRouteParameters()
    {
        $routeParams = ['foo' => 'foo', 'bar' => 'bar'];
        $this->gridConfigBuilder->setRouteParameters($routeParams);

        $this->assertAttributeEquals($routeParams, 'routeParameters', $this->gridConfigBuilder);
    }

    public function testGetRouteParameters()
    {
        $routeParams = ['foo' => 'foo', 'bar' => 'bar'];
        $this->gridConfigBuilder->setRouteParameters($routeParams);

        $this->assertEquals($routeParams, $this->gridConfigBuilder->getRouteParameters());
    }

    public function testSetPersistence()
    {
        $persistence = true;
        $this->gridConfigBuilder->setPersistence($persistence);

        $this->assertAttributeEquals($persistence, 'persistence', $this->gridConfigBuilder);
    }

    public function testIsPersited()
    {
        $persisted = false;
        $this->gridConfigBuilder->setPersistence($persisted);

        $this->assertFalse($this->gridConfigBuilder->isPersisted());
    }

    public function testSetPage()
    {
        $page = 1;
        $this->gridConfigBuilder->setPage($page);

        $this->assertAttributeEquals($page, 'page', $this->gridConfigBuilder);
    }

    public function testGetPage()
    {
        $page = 5;
        $this->gridConfigBuilder->setPage($page);

        $this->assertEquals($page, $this->gridConfigBuilder->getPage());
    }

    public function testGetOptions()
    {
        $this->assertEquals($this->options, $this->gridConfigBuilder->getOptions());
    }

    public function testHasOption()
    {
        $this->assertTrue($this->gridConfigBuilder->hasOption('foo'));
        $this->assertFalse($this->gridConfigBuilder->hasOption('foobar'));
    }

    public function testGetOption()
    {
        $this->assertEquals('foo', $this->gridConfigBuilder->getOption('foo'));
        $this->assertEquals('default', $this->gridConfigBuilder->getOption('foobar', 'default'));
        $this->assertNull($this->gridConfigBuilder->getOption('foobar'));
    }

    public function testSetMaxPerPage()
    {
        $limit = 50;
        $this->gridConfigBuilder->setMaxPerPage($limit);

        $this->assertAttributeEquals($limit, 'limit', $this->gridConfigBuilder);
    }

    public function testGetMaxPerPage()
    {
        $limit = 100;
        $this->gridConfigBuilder->setMaxPerPage($limit);

        $this->assertEquals($limit, $this->gridConfigBuilder->getMaxPerPage());
    }

    public function testSetMaxResults()
    {
        $maxResults = 50;
        $this->gridConfigBuilder->setMaxResults($maxResults);

        $this->assertAttributeEquals($maxResults, 'maxResults', $this->gridConfigBuilder);
    }

    public function testGetMaxResults()
    {
        $maxResults = 100;
        $this->gridConfigBuilder->setMaxResults($maxResults);

        $this->assertEquals($maxResults, $this->gridConfigBuilder->getMaxResults());
    }

    public function testSetSortable()
    {
        $sortable = true;
        $this->gridConfigBuilder->setSortable($sortable);

        $this->assertAttributeEquals(true, 'sortable', $this->gridConfigBuilder);
    }

    public function testIsSortable()
    {
        $sortable = false;
        $this->gridConfigBuilder->setSortable($sortable);

        $this->assertFalse($this->gridConfigBuilder->isSortable());
    }

    public function testSetFilterable()
    {
        $filterable = false;
        $this->gridConfigBuilder->setFilterable($filterable);

        $this->assertAttributeEquals($filterable, 'filterable', $this->gridConfigBuilder);
    }

    public function testIsFilterable()
    {
        $filterable = true;
        $this->gridConfigBuilder->setFilterable($filterable);

        $this->assertTrue($this->gridConfigBuilder->isFilterable());
    }

    public function testSetOrder()
    {
        $order = 'asc';
        $this->gridConfigBuilder->setOrder($order);

        $this->assertAttributeEquals($order, 'order', $this->gridConfigBuilder);
    }

    public function testGetOrder()
    {
        $order = 'desc';
        $this->gridConfigBuilder->setOrder($order);

        $this->assertEquals($order, $this->gridConfigBuilder->getOrder());
    }

    public function testSetSortBy()
    {
        $sortBy = 'foo';
        $this->gridConfigBuilder->setSortBy($sortBy);

        $this->assertAttributeEquals($sortBy, 'sortBy', $this->gridConfigBuilder);
    }

    public function testGetSortBy()
    {
        $sortBy = 'bar';
        $this->gridConfigBuilder->setSortBy($sortBy);

        $this->assertEquals($sortBy, $this->gridConfigBuilder->getSortBy());
    }

    public function testSetGroupBy()
    {
        $groupBy = 'foo';
        $this->gridConfigBuilder->setGroupBy($groupBy);

        $this->assertAttributeEquals($groupBy, 'groupBy', $this->gridConfigBuilder);
    }

    public function testGetGroupBy()
    {
        $groupBy = ['foo', 'bar'];
        $this->gridConfigBuilder->setGroupBy($groupBy);

        $this->assertEquals($groupBy, $this->gridConfigBuilder->getGroupBy());
    }

    public function testAddAction()
    {
        $action1 = $this->createMock(RowActionInterface::class);
        $action1->method('getColumn')->willReturn('foo');

        $action2 = $this->createMock(RowActionInterface::class);
        $action2->method('getColumn')->willReturn('bar');

        $action3 = $this->createMock(RowActionInterface::class);
        $action3->method('getColumn')->willReturn('bar');

        $this->gridConfigBuilder
            ->addAction($action1)
            ->addAction($action2)
            ->addAction($action3);

        $this->assertAttributeEquals(['foo' => [$action1], 'bar' => [$action2, $action3]], 'actions', $this->gridConfigBuilder);
    }

    public function testGetGridConfig()
    {
        $this->assertInstanceOf(GridConfigBuilder::class, $this->gridConfigBuilder->getGridConfig());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->gridConfigBuilder = new GridConfigBuilder($this->name, $this->options);
    }
}

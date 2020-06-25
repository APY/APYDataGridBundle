<?php

namespace APY\DataGridBundle\Grid\Tests;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\InvalidArgumentException;
use APY\DataGridBundle\Grid\Exception\UnexpectedTypeException;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\GridBuilder;
use APY\DataGridBundle\Grid\GridFactoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class GridBuilderTest.
 */
class GridBuilderTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $container;

    /**
     * @var MockObject
     */
    private $factory;

    /**
     * @var GridBuilder
     */
    private $builder;

    public function testAddUnexpectedType()
    {
        $this->expectException(UnexpectedTypeException::class);

        $this->builder->add('foo', 123);
        $this->builder->add('foo', ['test']);
    }

    public function testAddColumnTypeString()
    {
        $this->assertFalse($this->builder->has('foo'));

        $this->factory->expects($this->once())
                      ->method('createColumn')
                      ->with('foo', 'text', [])
                      ->willReturn($this->createMock(Column::class));

        $this->builder->add('foo', 'text');

        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAddColumnType()
    {
        $this->factory->expects($this->never())->method('createColumn');

        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', $this->createMock(Column::class));
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAddIsFluent()
    {
        $builder = $this->builder->add('name', 'text', ['key' => 'value']);
        $this->assertSame($builder, $this->builder);
    }

    public function testGetUnknown()
    {
        $this->expectException(
            InvalidArgumentException::class,
            'The column with the name "foo" does not exist.'
        );

        $this->builder->get('foo');
    }

    public function testGetExplicitColumnType()
    {
        $expectedColumn = $this->createMock(Column::class);

        $this->factory->expects($this->once())
                      ->method('createColumn')
                      ->with('foo', 'text', [])
                      ->willReturn($expectedColumn);

        $this->builder->add('foo', 'text');

        $column = $this->builder->get('foo');

        $this->assertSame($expectedColumn, $column);
    }

    public function testHasColumnType()
    {
        $this->factory->expects($this->once())
                      ->method('createColumn')
                      ->with('foo', 'text', [])
                      ->willReturn($this->createMock(Column::class));

        $this->builder->add('foo', 'text');

        $this->assertTrue($this->builder->has('foo'));
    }

    public function assertHasNotColumnType()
    {
        $this->assertFalse($this->builder->has('foo'));
    }

    public function testRemove()
    {
        $this->factory->expects($this->once())
                      ->method('createColumn')
                      ->with('foo', 'text', [])
                      ->willReturn($this->createMock(Column::class));

        $this->builder->add('foo', 'text');

        $this->assertTrue($this->builder->has('foo'));
        $this->builder->remove('foo');
        $this->assertFalse($this->builder->has('foo'));
    }

    public function testRemoveIsFluent()
    {
        $builder = $this->builder->remove('foo');
        $this->assertSame($builder, $this->builder);
    }

    public function testGetGrid()
    {
        $this->assertInstanceOf(Grid::class, $this->builder->getGrid());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $self = $this;

        $this->container = $this->createMock(Container::class);
        $this->container->expects($this->any())
            ->method('get')
            ->will($this->returnCallback(function ($param) use ($self) {
                switch ($param) {
                    case 'router':
                        return $self->createMock(RouterInterface::class);
                        break;
                    case 'request_stack':
                        $request = new Request([], [], ['key' => 'value']);
                        $requestStack = new RequestStack();
                        $requestStack->push($request);

                        return $requestStack;
                        break;
                    case 'security.authorization_checker':
                        return $self->createMock(AuthorizationCheckerInterface::class);
                        break;
                }
            }));

        $this->factory = $this->createMock(GridFactoryInterface::class);
        $this->builder = new GridBuilder($this->container, $this->factory, 'name');
    }

    protected function tearDown()
    {
        $this->factory = null;
        $this->builder = null;
    }
}

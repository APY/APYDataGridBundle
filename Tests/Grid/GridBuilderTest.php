<?php

namespace APY\DataGridBundle\Grid\Tests;

use APY\DataGridBundle\Grid\GridBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class GridBuilderTest.
 */
class GridBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $factory;

    /**
     * @var GridBuilder
     */
    private $builder;

    public function testAddUnexpectedType()
    {
        $this->setExpectedException('APY\DataGridBundle\Grid\Exception\UnexpectedTypeException');

        $this->builder->add('foo', 123);
        $this->builder->add('foo', ['test']);
    }

    public function testAddColumnTypeString()
    {
        $this->assertFalse($this->builder->has('foo'));

        $this->factory->expects($this->once())
                      ->method('createColumn')
                      ->with('foo', 'text', [])
                      ->willReturn($this->getMock('APY\DataGridBundle\Grid\Column\Column'));

        $this->builder->add('foo', 'text');

        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAddColumnType()
    {
        $this->factory->expects($this->never())->method('createColumn');

        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', $this->getMock('APY\DataGridBundle\Grid\Column\Column'));
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAddIsFluent()
    {
        $builder = $this->builder->add('name', 'text', ['key' => 'value']);
        $this->assertSame($builder, $this->builder);
    }

    public function testGetUnknown()
    {
        $this->setExpectedException(
            'APY\DataGridBundle\Grid\Exception\InvalidArgumentException',
            'The column with the name "foo" does not exist.'
        );

        $this->builder->get('foo');
    }

    public function testGetExplicitColumnType()
    {
        $expectedColumn = $this->getMock('APY\DataGridBundle\Grid\Column\Column');

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
                      ->willReturn($this->getMock('APY\DataGridBundle\Grid\Column\Column'));

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
                      ->willReturn($this->getMock('APY\DataGridBundle\Grid\Column\Column'));

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
        $this->assertInstanceOf('APY\DataGridBundle\Grid\Grid', $this->builder->getGrid());
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $self = $this;

        $this->container = $this->getMock('Symfony\Component\DependencyInjection\Container');
        $this->container->expects($this->any())
                        ->method('get')
                        ->will($this->returnCallback(function ($param) use ($self) {
                            switch ($param) {
                                case 'router':
                                    return $self->getMock('Symfony\Component\Routing\RouterInterface');
                                    break;
                                case 'request':
                                    $request = new Request([], [], ['key' => 'value']);

                                    return $request;
                                    break;
                                case 'security.authorization_checker':
                                    return $self->getMock('Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface');
                                    break;
                            }
                        }));

        $this->factory = $this->getMock('APY\DataGridBundle\Grid\GridFactoryInterface');
        $this->builder = new GridBuilder($this->container, $this->factory, 'name');
    }

    protected function tearDown()
    {
        $this->factory = null;
        $this->builder = null;
    }
}

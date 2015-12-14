<?php
namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\GridBuilder;
use APY\DataGridBundle\Grid\GridBuilderInterface;
use APY\DataGridBundle\Grid\GridFactory;
use APY\DataGridBundle\Grid\Type\GridType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class GridFactoryTest
 *
 * @package APY\DataGridBundle\Tests\Grid
 */
class GridFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $builder;

    /**
     * @var GridFactory
     */
    private $factory;

    public function testCreateWithUnexpectedType()
    {
        $this->setExpectedException('APY\DataGridBundle\Grid\Exception\UnexpectedTypeException');
        $this->factory->create(1234);
        $this->factory->create(array('foo'));
        $this->factory->create(new \stdClass());
    }

    public function testCreateWithTypeString()
    {
        $this->registry->expects($this->once())
                       ->method('getType')
                       ->with('foo')
                       ->willReturn($this->getMock('APY\DataGridBundle\Grid\GridTypeInterface'));

        $this->assertInstanceOf('APY\DataGridBundle\Grid\Grid', $this->factory->create('foo'));
    }

    public function testCreateWithTypeObject()
    {
        $this->registry->expects($this->never())->method('getType');

        $this->assertInstanceOf('APY\DataGridBundle\Grid\Grid', $this->factory->create(new GridType()));
    }

    public function testCreateBuilderWithDefaultType()
    {
        $defaultType = new GridType();

        $this->registry->expects($this->once())
                       ->method('getType')
                       ->with('grid')
                       ->willReturn($defaultType);

        $builder = $this->factory->createBuilder();

        $this->assertSame($defaultType, $builder->getType());
    }

    public function testCreateBuilder()
    {
        $givenOptions    = array('a' => 1, 'b' => 2);
        $resolvedOptions = array('a' => 1, 'b' => 2, 'c' => 3);

        $type = $this->getMock('APY\DataGridBundle\Grid\GridTypeInterface');

        $type->expects($this->once())
             ->method('getName')
             ->willReturn('TYPE');

        $type->expects($this->once())
             ->method('configureOptions')
             ->with($this->callback(function ($resolver) use ($resolvedOptions) {
                 if (!$resolver instanceof OptionsResolver) {
                     return false;
                 }

                 $resolver->setDefaults($resolvedOptions);

                 return true;
             }));

        $type->expects($this->once())
             ->method('buildGrid')
             ->with($this->callback(function ($builder) {
                 return $builder instanceof GridBuilder && $builder->getName() == 'TYPE';
             }), $resolvedOptions);

        $builder = $this->factory->createBuilder($type, null, $givenOptions);

        $this->assertInstanceOf('APY\DataGridBundle\Grid\GridBuilderInterface', $builder);
        $this->assertSame($type, $builder->getType());
        $this->assertSame('TYPE', $builder->getName());
        $this->assertEquals($resolvedOptions, $builder->getOptions());
        $this->assertNull($builder->getSource());
    }

    public function testCreateColumnWithUnexpectedType()
    {
        $this->setExpectedException('APY\DataGridBundle\Grid\Exception\UnexpectedTypeException');
        $this->factory->createColumn('foo', 1234);
    }

    public function testCreateColumnWithTypeString()
    {
        $expectedColumn = new TextColumn();

        $this->registry->expects($this->once())
                       ->method('getColumn')
                       ->with('text')
                       ->willReturn($expectedColumn);

        $column = $this->factory->createColumn('foo', 'text', array('title' => 'bar'));

        $this->assertInstanceOf('APY\DataGridBundle\Grid\Column\TextColumn', $column);
        $this->assertEquals('text', $column->getType());
        $this->assertEquals('foo', $column->getId());
        $this->assertEquals('bar', $column->getTitle());
        $this->assertEquals('foo', $column->getField());
        $this->assertTrue($column->isVisibleForSource());
    }

    public function testCreateColumnWithObject()
    {
        $column = $this->factory->createColumn('foo', new TextColumn(), array('title' => 'bar'));

        $this->assertInstanceOf('APY\DataGridBundle\Grid\Column\TextColumn', $column);
        $this->assertEquals('text', $column->getType());
        $this->assertEquals('foo', $column->getId());
        $this->assertEmpty($column->getTitle());
        $this->assertNull($column->getField());
        $this->assertFalse($column->isVisibleForSource());
    }

    protected function setUp()
    {
        $self = $this;
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\Container');
        $this->container->expects($this->any())
                        ->method('get')
                        ->will($this->returnCallback(function ($param) use($self) {
                            switch ($param) {
                                case 'router':
                                    return $self->getMock('Symfony\Component\Routing\RouterInterface');
                                    break;
                                case 'request':
                                    $request = new Request(array(), array(), array('key' => 'value'));

                                    return $request;
                                    break;
                                case 'security.context':
                                    return $self->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
                                    break;
                            }
                        }));

        $this->registry = $this->getMock('APY\DataGridBundle\Grid\GridRegistryInterface');
        $this->builder  = $this->getMock('APY\DataGridBundle\Grid\GridBuilderInterface');
        $this->factory  = new GridFactory($this->container, $this->registry);
    }
}

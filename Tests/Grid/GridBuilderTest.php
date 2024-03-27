<?php

namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Exception\InvalidArgumentException;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\GridBuilder;
use APY\DataGridBundle\Grid\GridFactoryInterface;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

class GridBuilderTest extends TestCase
{
    private GridFactoryInterface|MockObject|null $factory;
    private RouterInterface|MockObject|null $router;
    private RequestStack|MockObject $requestStack;
    private Request|MockObject $request;
    private ?GridBuilder $builder;

    public function testAddColumnTypeString(): void
    {
        $this->assertFalse($this->builder->has('foo'));

        $this->factory->expects($this->once())
                      ->method('createColumn')
                      ->with('foo', 'text', [])
                      ->willReturn($this->createMock(Column::class));

        $this->builder->add('foo', 'text');

        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAddColumnType(): void
    {
        $this->factory->expects($this->never())->method('createColumn');

        $this->assertFalse($this->builder->has('foo'));
        $this->builder->add('foo', $this->createMock(Column::class));
        $this->assertTrue($this->builder->has('foo'));
    }

    public function testAddIsFluent(): void
    {
        $builder = $this->builder->add('name', 'text', ['key' => 'value']);
        $this->assertSame($builder, $this->builder);
    }

    public function testGetUnknown(): void
    {
        $this->expectException(
            InvalidArgumentException::class,
            'The column with the name "foo" does not exist.'
        );

        $this->builder->get('foo');
    }

    public function testGetExplicitColumnType(): void
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

    public function testHasColumnType(): void
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

    public function testRemove(): void
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

    public function testRemoveIsFluent(): void
    {
        $builder = $this->builder->remove('foo');
        $this->assertSame($builder, $this->builder);
    }

    public function testGetGrid(): void
    {
        $this->assertInstanceOf(Grid::class, $this->builder->getGrid());
    }

    protected function setUp(): void
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $manager = $this->createMock(Manager::class);
        $kernel = $this->createMock(KernelInterface::class);
        $twig = $this->createMock(Environment::class);
        $this->request = $this->createMock(Request::class);
        $this->request->attributes = $this->createMock(ParameterBag::class);
        $this->request->method('getSession')->willReturn($this->createMock(SessionInterface::class));
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method('getCurrentRequest')->willReturn($this->request);
        $this->factory = $this->createMock(GridFactoryInterface::class);
        $this->router = $this->createMock(RouterInterface::class);
        $this->builder = new GridBuilder($this->router, $authChecker, $registry, $manager, $kernel, $twig, $this->requestStack, $this->factory, 'name');
    }

    protected function tearDown(): void
    {
        $this->factory = null;
        $this->builder = null;
    }
}

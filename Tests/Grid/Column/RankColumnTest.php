<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\RankColumn;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class RankColumnTest extends TestCase
{
    /** @var RankColumn */
    private $column;

    public function testGetType(): void
    {
        $this->assertEquals('rank', $this->column->getType());
    }

    public function testInitialize(): void
    {
        self::markTestSkipped();
        $params = [
            'foo' => 'foo',
            'bar' => 'bar',
            'title' => 'title',
            'filterable' => true,
            'source' => true,
        ];

        $column = new RankColumn($params);

        $this->assertAttributeEquals([
            'foo' => 'foo',
            'bar' => 'bar',
            'title' => 'title',
            'filterable' => false,
            'sortable' => false,
            'source' => false,
        ], 'params', $column);
    }

    public function testSetId(): void
    {
        $this->assertEquals('rank', $this->column->getId());

        $column = new RankColumn(['id' => 'foo']);
        $this->assertEquals('foo', $column->getId());
    }

    public function testSetTitle(): void
    {
        $this->assertEquals('rank', $this->column->getTitle());

        $column = new RankColumn(['title' => 'foo']);
        $this->assertEquals('foo', $column->getTitle());
    }

    public function testSetSize(): void
    {
        $this->assertEquals('30', $this->column->getSize());

        $column = new RankColumn(['size' => '20']);
        $this->assertEquals('20', $column->getSize());
    }

    public function testSetAlign(): void
    {
        $this->assertEquals(Column::ALIGN_CENTER, $this->column->getAlign());

        $column = new RankColumn(['align' => Column::ALIGN_RIGHT]);
        $this->assertEquals(Column::ALIGN_RIGHT, $column->getAlign());
    }

    public function testRenderCell(): void
    {
        self::markTestSkipped();
        $this->assertEquals(1, $this->column->renderCell(true, $this->createMock(Row::class), $this->createMock(Router::class)));
        $this->assertAttributeEquals(2, 'rank', $this->column);

        $this->assertEquals(2, $this->column->renderCell(true, $this->createMock(Row::class), $this->createMock(Router::class)));
        $this->assertAttributeEquals(3, 'rank', $this->column);
    }

    protected function setUp(): void
    {
        $this->column = new RankColumn();
    }
}

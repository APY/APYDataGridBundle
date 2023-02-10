<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\RankColumn;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class RankColumnTest extends TestCase
{
    private \APY\DataGridBundle\Grid\Column\RankColumn $column;

    public function testGetType()
    {
        $this->assertEquals('rank', $this->column->getType());
    }

    public function testInitialize()
    {
        $params = [
            'foo'        => 'foo',
            'bar'        => 'bar',
            'title'      => 'title',
            'filterable' => true,
            'source'     => true,
        ];

        $column = new RankColumn($params);

        $this->assertEquals([
            'foo'        => 'foo',
            'bar'        => 'bar',
            'title'      => 'title',
            'filterable' => false,
            'sortable'   => false,
            'source'     => false,
        ], $column->getParams());
    }

    public function testSetId()
    {
        $this->assertEquals('rank', $this->column->getId());

        $column = new RankColumn(['id' => 'foo']);
        $this->assertEquals('foo', $column->getId());
    }

    public function testSetTitle()
    {
        $this->assertEquals('rank', $this->column->getTitle());

        $column = new RankColumn(['title' => 'foo']);
        $this->assertEquals('foo', $column->getTitle());
    }

    public function testSetSize()
    {
        $this->assertEquals('30', $this->column->getSize());

        $column = new RankColumn(['size' => '20']);
        $this->assertEquals('20', $column->getSize());
    }

    public function testSetAlign()
    {
        $this->assertEquals(Column::ALIGN_CENTER, $this->column->getAlign());

        $column = new RankColumn(['align' => Column::ALIGN_RIGHT]);
        $this->assertEquals(Column::ALIGN_RIGHT, $column->getAlign());
    }

    public function testRenderCell()
    {
        $this->assertEquals(1, $this->column->renderCell(true, $this->createMock(Row::class), $this->createMock(Router::class)));
        $this->assertEquals(2, $this->column->getRank());

        $this->assertEquals(2, $this->column->renderCell(true, $this->createMock(Row::class), $this->createMock(Router::class)));
        $this->assertEquals(3, $this->column->getRank());
    }

    public function setUp(): void
    {
        $this->column = new RankColumn();
    }
}

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

        $this->assertAttributeEquals([
            'foo'        => 'foo',
            'bar'        => 'bar',
            'title'      => 'title',
            'filterable' => false,
            'sortable'   => false,
            'source'     => false,
        ], 'params', $column);
    }

    public function testSetId()
    {
        $this->assertAttributeEquals('rank', 'id', $this->column);

        $column = new RankColumn(['id' => 'foo']);
        $this->assertAttributeEquals('foo', 'id', $column);
    }

    public function testSetTitle()
    {
        $this->assertAttributeEquals('rank', 'title', $this->column);

        $column = new RankColumn(['title' => 'foo']);
        $this->assertAttributeEquals('foo', 'title', $column);
    }

    public function testSetSize()
    {
        $this->assertAttributeEquals('30', 'size', $this->column);

        $column = new RankColumn(['size' => '20']);
        $this->assertAttributeEquals('20', 'size', $column);
    }

    public function testSetAlign()
    {
        $this->assertAttributeEquals(Column::ALIGN_CENTER, 'align', $this->column);

        $column = new RankColumn(['align' => Column::ALIGN_RIGHT]);
        $this->assertAttributeEquals(Column::ALIGN_RIGHT, 'align', $column);
    }

    public function testRenderCell()
    {
        $this->assertEquals(1, $this->column->renderCell(true, $this->createMock(Row::class), $this->createMock(Router::class)));
        $this->assertAttributeEquals(2, 'rank', $this->column);

        $this->assertEquals(2, $this->column->renderCell(true, $this->createMock(Row::class), $this->createMock(Router::class)));
        $this->assertAttributeEquals(3, 'rank', $this->column);
    }

    public function setUp()
    {
        $this->column = new RankColumn();
    }
}

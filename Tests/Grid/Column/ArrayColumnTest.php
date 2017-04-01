<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\ArrayColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Router;

class ArrayColumnTest extends TestCase
{
    /** @var ArrayColumn */
    private $column;

    public function testGetType()
    {
        $this->assertEquals('array', $this->column->getType());
    }

    public function testInitializeDefaultParams()
    {
        $this->assertAttributeEquals([
            Column::OPERATOR_LIKE,
            Column::OPERATOR_NLIKE,
            Column::OPERATOR_EQ,
            Column::OPERATOR_NEQ,
            Column::OPERATOR_ISNULL,
            Column::OPERATOR_ISNOTNULL,
        ], 'operators', $this->column);
    }

    public function testDocumentFilters()
    {
        $value = ['foo', 'bar'];

        $this->column->setData(['operator' => Column::OPERATOR_EQ, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_EQ, $value, null)],
            $this->column->getFilters('document')
        );
    }

    public function testEqualFilter()
    {
        $value = ['foo', 'foobar'];

        $this->column->setData(['operator' => Column::OPERATOR_EQ, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_EQ, 'a:2:{i:1;s:3:"foo";i:2;s:6:"foobar";}')],
            $this->column->getFilters('asource')
        );
    }

    public function testNotEqualFilter()
    {
        $value = ['foo', 'foobar'];

        $this->column->setData(['operator' => Column::OPERATOR_NEQ, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_NEQ, 'a:2:{i:1;s:3:"foo";i:2;s:6:"foobar";}')],
            $this->column->getFilters('asource')
        );
    }

    public function testLikeFilter()
    {
        $value = ['foo'];

        $this->column->setData(['operator' => Column::OPERATOR_LIKE, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_LIKE, 's:3:"foo";')],
            $this->column->getFilters('asource')
        );
    }

    public function testNotLikeFilter()
    {
        $value = ['foo'];

        $this->column->setData(['operator' => Column::OPERATOR_NLIKE, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_NLIKE, 's:3:"foo";')],
            $this->column->getFilters('asource')
        );
    }

    public function testIsNullFilter()
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNULL]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNULL),
            new Filter(Column::OPERATOR_EQ, 'a:0:{}'),
        ], $this->column->getFilters('asource'));
        $this->assertAttributeEquals(Column::DATA_DISJUNCTION, 'dataJunction', $this->column);
    }

    public function testIsNotNullFilter()
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNOTNULL]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNOTNULL),
            new Filter(Column::OPERATOR_NEQ, 'a:0:{}'),
        ], $this->column->getFilters('asource'));
    }

    public function testRenderCellWithoutCallback()
    {
        $values = ['foo' => 'a', 'bar' => 'b', 'foobar' => ['c', 'd']];

        $result = $this->column->renderCell(
            $values,
            $this->createMock(Row::class),
            $this->createMock(Router::class)
        );

        // @todo: is this the expected result?
        $this->assertEquals($result, $values);
    }

    public function testRenderCellWithCallback()
    {
        $values = ['foo' => 'a', 'bar' => 'b', 'foobar' => ['c', 'd']];
        $this->column->manipulateRenderCell(function ($value, $row, $router) {
            return ['bar' => 'a', 'foo' => 'b'];
        });

        $result = $this->column->renderCell(
            $values,
            $this->createMock(Row::class),
            $this->createMock(Router::class)
        );

        $this->assertEquals($result, ['bar' => 'a', 'foo' => 'b']);
    }

    public function setUp()
    {
        $this->column = new ArrayColumn();
    }
}

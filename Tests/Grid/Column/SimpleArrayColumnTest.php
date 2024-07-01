<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\SimpleArrayColumn;
use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Router;

class SimpleArrayColumnTest extends TestCase
{
    /** @var SimpleArrayColumn */
    private $column;

    public function testGetType(): void
    {
        $this->assertEquals('simple_array', $this->column->getType());
    }

    protected function setUp(): void
    {
        $this->column = new SimpleArrayColumn();
    }

    public function testInitializeDefaultParams(): void
    {
        $this->assertEquals([
            Column::OPERATOR_LIKE,
            Column::OPERATOR_NLIKE,
            Column::OPERATOR_EQ,
            Column::OPERATOR_NEQ,
            Column::OPERATOR_ISNULL,
            Column::OPERATOR_ISNOTNULL,
        ], $this->column->getOperators());

        $this->assertEquals(Column::OPERATOR_LIKE, $this->column->getDefaultOperator());
    }

    public function testEqualFilter(): void
    {
        $value = ['foo, bar'];

        $this->column->setData(['operator' => Column::OPERATOR_EQ, 'from' => $value]);

        $this->assertEquals([new Filter(Column::OPERATOR_EQ, 'foo, bar')], $this->column->getFilters('asource'));
    }

    public function testNotEqualFilter(): void
    {
        $value = ['foo, bar'];

        $this->column->setData(['operator' => Column::OPERATOR_NEQ, 'from' => $value]);

        $this->assertEquals([new Filter(Column::OPERATOR_NEQ, 'foo, bar')], $this->column->getFilters('asource'));
    }

    public function testLikeFilter(): void
    {
        $value = ['foo, bar'];

        $this->column->setData(['operator' => Column::OPERATOR_LIKE, 'from' => $value]);

        $this->assertEquals([new Filter(Column::OPERATOR_LIKE, 'foo, bar')], $this->column->getFilters('asource'));
    }

    public function testNotLikeFilter(): void
    {
        $value = ['foo, bar'];

        $this->column->setData(['operator' => Column::OPERATOR_NLIKE, 'from' => $value]);

        $this->assertEquals([new Filter(Column::OPERATOR_NLIKE, 'foo, bar')], $this->column->getFilters('asource'));
    }

    public function testIsNullFilter(): void
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNULL]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNULL),
            new Filter(Column::OPERATOR_EQ, ''),
        ], $this->column->getFilters('asource'));
        $this->assertEquals(Column::DATA_DISJUNCTION, $this->column->getDataJunction());
    }

    public function testIsNotNullFilter(): void
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNOTNULL]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNOTNULL),
            new Filter(Column::OPERATOR_NEQ, ''),
        ], $this->column->getFilters('asource'));
    }

    public function testRenderCellWithoutCallback(): void
    {
        $values = ['foo, bar'];

        $result = $this->column->renderCell(
            $values,
            $this->createMock(Row::class),
            $this->createMock(Router::class)
        );

        $this->assertEquals($result, $values);
    }

    public function testRenderCellWithCallback(): void
    {
        $values = ['foo, bar'];
        $this->column->manipulateRenderCell(static function($value, $row, $router) {
            return ['foobar'];
        });

        $result = $this->column->renderCell(
            $values,
            $this->createMock(Row::class),
            $this->createMock(Router::class)
        );

        $this->assertEquals($result, ['foobar']);
    }
}

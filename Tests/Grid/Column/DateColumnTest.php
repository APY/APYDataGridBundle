<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\DateColumn;
use APY\DataGridBundle\Grid\Filter;
use PHPUnit\Framework\TestCase;

class DateColumnTest extends TestCase
{
    /** @var DateColumn */
    private $column;

    public function testGetType()
    {
        $this->assertEquals('date', $this->column->getType());
    }

    public function testGetFiltersWithoutValue()
    {
        $operators = array_flip(Column::getAvailableOperators());
        unset($operators[Column::OPERATOR_ISNOTNULL]);
        unset($operators[Column::OPERATOR_ISNULL]);

        foreach (array_keys($operators) as $operator) {
            $this->column->setData(['operator' => $operator]);
            $this->assertEmpty($this->column->getFilters('asource'));
        }
    }

    public function testGetFiltersWithNotNullOperator()
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNOTNULL]);

        $this->assertEquals([new Filter(Column::OPERATOR_ISNOTNULL)], $this->column->getFilters('asource'));
    }

    public function testGetFiltersWithIsNullOperator()
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNULL]);
        $filters = $this->column->getFilters('asource');

        $this->assertEquals([new Filter(Column::OPERATOR_ISNULL)], $filters);
    }

    public function testGetFiltersOperatorEq()
    {
        $from = '2017-03-18';
        $to = '2017-03-20';

        $this->column->setData(['operator' => Column::OPERATOR_EQ, 'from' => $from, 'to' => $to]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_GTE, new \DateTime($from . ' 00:00:00')),
            new Filter(Column::OPERATOR_LTE, new \DateTime($from . '23:59:59')),
        ], $this->column->getFilters('asource'));
    }

    public function testGetFiltersOperatorNeq()
    {
        $from = '2017-03-18';
        $to = '2017-03-20';

        $this->column->setData(['operator' => Column::OPERATOR_NEQ, 'from' => $from, 'to' => $to]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_LT, new \DateTime($from . ' 00:00:00')),
            new Filter(Column::OPERATOR_GT, new \DateTime($from . '23:59:59')),
        ], $this->column->getFilters('asource'));
        $this->assertAttributeEquals(Column::DATA_DISJUNCTION, 'dataJunction', $this->column);
    }

    public function testGetFiltersOperatorLt()
    {
        $value = '2017-03-18';

        $this->column->setData(['operator' => Column::OPERATOR_LT, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_LT, new \DateTime($value . '00:00:00'))],
            $this->column->getFilters('asource')
        );
    }

    public function testGetFiltersOperatorGte()
    {
        $value = '2017-03-18';

        $this->column->setData(['operator' => Column::OPERATOR_GTE, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_GTE, new \DateTime($value . '00:00:00'))],
            $this->column->getFilters('asource')
        );
    }

    public function testGetFiltersOperatorGt()
    {
        $value = '2017-03-18';

        $this->column->setData(['operator' => Column::OPERATOR_GT, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_GT, new \DateTime($value . '23:59:59'))],
            $this->column->getFilters('asource')
        );
    }

    public function testGetFiltersOperatorLte()
    {
        $value = '2017-03-18';

        $this->column->setData(['operator' => Column::OPERATOR_LTE, 'from' => $value]);

        $this->assertEquals(
            [new Filter(Column::OPERATOR_LTE, new \DateTime($value . '23:59:59'))],
            $this->column->getFilters('asource')
        );
    }

    public function setUp()
    {
        $this->column = new DateColumn();
    }
}

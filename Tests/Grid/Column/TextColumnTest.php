<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\Filter;
use PHPUnit\Framework\TestCase;

class TextColumnTest extends TestCase
{
    /** @var TextColumn */
    private $column;

    public function testGetType()
    {
        $this->assertEquals('text', $this->column->getType());
    }

    public function testIsQueryValid()
    {
        $this->assertTrue($this->column->isQueryValid('foo'));
        $this->assertTrue($this->column->isQueryValid(['foo', 1, 'bar', null]));
        $this->assertFalse($this->column->isQueryValid(1));
    }

    public function testNullOperatorFilters()
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNULL]);
        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNULL),
            new Filter(Column::OPERATOR_EQ, ''),
        ], $this->column->getFilters('asource'));
        $this->assertAttributeEquals(Column::DATA_DISJUNCTION, 'dataJunction', $this->column);
    }

    public function testNotNullOperatorFilters()
    {
        $this->column->setData(['operator' => Column::OPERATOR_ISNOTNULL]);
        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNOTNULL),
            new Filter(Column::OPERATOR_NEQ, ''),
        ], $this->column->getFilters('asource'));
    }

    public function testOtherOperatorFilters()
    {
        $operators = array_flip(Column::getAvailableOperators());
        unset($operators[Column::OPERATOR_ISNOTNULL]);
        unset($operators[Column::OPERATOR_ISNULL]);

        foreach (array_keys($operators) as $operator) {
            $this->column->setData(['operator' => $operator]);
            $this->assertEmpty($this->column->getFilters('asource'));
        }
    }

    public function setUp()
    {
        $this->column = new TextColumn();
    }
}

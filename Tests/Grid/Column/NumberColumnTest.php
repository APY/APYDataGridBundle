<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\NumberColumn;
use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class NumberColumnTest extends TestCase
{
    private NumberColumn $column;

    public function testGetType(): void
    {
        $this->assertEquals('number', $this->column->getType());
    }

    public function testInitializeDefaultParams(): void
    {
        $this->assertEquals(Column::ALIGN_RIGHT, $this->column->getAlign());
        $this->assertEquals(\NumberFormatter::DECIMAL, $this->column->getStyle());
        $this->assertEquals(\Locale::getDefault(), $this->column->getLocale());
        $this->assertNull($this->column->getPrecision());
        $this->assertFalse($this->column->getGrouping());
        $this->assertEquals(\NumberFormatter::ROUND_HALFUP, $this->column->getRoundingMode());
        $this->assertNull($this->column->getRuleSet());
        $this->assertNull($this->column->getCurrencyCode());
        $this->assertFalse($this->column->getFractional());
        $this->assertNull($this->column->getMaxFractionDigits());
        $this->assertEquals([
            Column::OPERATOR_EQ,
            Column::OPERATOR_NEQ,
            Column::OPERATOR_LT,
            Column::OPERATOR_LTE,
            Column::OPERATOR_GT,
            Column::OPERATOR_GTE,
            Column::OPERATOR_BTW,
            Column::OPERATOR_BTWE,
            Column::OPERATOR_ISNULL,
            Column::OPERATOR_ISNOTNULL,
        ], $this->column->getOperators());
        $this->assertEquals(Column::OPERATOR_EQ, $this->column->getDefaultOperator());
    }

    public function testInitializeStyle(): void
    {
        $column = new NumberColumn(['style' => 'decimal']);
        $this->assertEquals(\NumberFormatter::DECIMAL, $column->getStyle());

        $column = new NumberColumn(['style' => 'percent']);
        $this->assertEquals(\NumberFormatter::PERCENT, $column->getStyle());

        $column = new NumberColumn(['style' => 'money']);
        $this->assertEquals(\NumberFormatter::CURRENCY, $column->getStyle());

        $column = new NumberColumn(['style' => 'currency']);
        $this->assertEquals(\NumberFormatter::CURRENCY, $column->getStyle());

        $column = new NumberColumn(['style' => 'duration']);
        $this->assertEquals(\NumberFormatter::DURATION, $column->getStyle());
        $this->assertEquals('en', $column->getLocale());
        $this->assertEquals('%in-numerals', $column->getRuleSet());

        $column = new NumberColumn(['style' => 'scientific']);
        $this->assertEquals(\NumberFormatter::SCIENTIFIC, $column->getStyle());

        $column = new NumberColumn(['style' => 'spellout']);
        $this->assertEquals(\NumberFormatter::SPELLOUT, $column->getStyle());
    }

    public function testInitializeStyleWithInvalidValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $column = new NumberColumn(['style' => 'foostyle']);
    }

    public function testInitializeLocale(): void
    {
        $column = new NumberColumn(['locale' => 'it']);
        $this->assertEquals('it', $column->getLocale());
    }

    public function testInitializePrecision(): void
    {
        $column = new NumberColumn(['precision' => 2]);
        $this->assertEquals(2, $column->getPrecision());
    }

    public function testInitializeGrouping(): void
    {
        $column = new NumberColumn(['grouping' => 3]);
        $this->assertEquals(3, $column->getGrouping());
    }

    public function testInitializeRoundingMode(): void
    {
        $column = new NumberColumn(['roundingMode' => \NumberFormatter::ROUND_HALFDOWN]);
        $this->assertEquals(\NumberFormatter::ROUND_HALFDOWN, $column->getRoundingMode());
    }

    public function testInitializeRuleSet(): void
    {
        $column = new NumberColumn(['ruleSet' => \NumberFormatter::PUBLIC_RULESETS]);
        $this->assertEquals(\NumberFormatter::PUBLIC_RULESETS, $column->getRuleSet());
    }

    public function testInitializeCurrencyCode(): void
    {
        $column = new NumberColumn(['currencyCode' => 'EUR']);
        $this->assertEquals('EUR', $column->getCurrencyCode());
    }

    public function testInizializeFractional(): void
    {
        $column = new NumberColumn(['fractional' => true]);
        $this->assertTrue($column->getFractional());
    }

    public function testInizializeMaxFractionalDigits(): void
    {
        $column = new NumberColumn(['maxFractionDigits' => 2]);
        $this->assertEquals(2, $column->getMaxFractionDigits());
    }

    public function testIsQueryValid(): void
    {
        $this->assertTrue($this->column->isQueryValid('1'));
        $this->assertTrue($this->column->isQueryValid(1));
        $this->assertTrue($this->column->isQueryValid('1.2'));
        $this->assertTrue($this->column->isQueryValid(1.2));
        $this->assertTrue($this->column->isQueryValid([1, '1', 1.2, '1.2', 'foo']));
        $this->assertFalse($this->column->isQueryValid('foo'));
        $this->assertFalse($this->column->isQueryValid(['foo', 'bar']));
    }

    public function testRenderCellWithCallback(): void
    {
        $value = 1.0;
        $this->column->manipulateRenderCell(static function($value, $row, $router) {
            return (int) $value;
        });

        $result = $this->column->renderCell(
            $value,
            $this->createMock(Row::class),
            $this->createMock(Router::class)
        );

        $this->assertEquals($result, $value);
    }

    public function testDisplayedValueWithEmptyValue(): void
    {
        $this->assertEquals('', $this->column->getDisplayedValue(''));
        $this->assertEquals('', $this->column->getDisplayedValue(null));
    }

    public function testDisplayedPercentValue(): void
    {
        $column = new NumberColumn([
            'precision' => 2,
            'roundingMode' => \NumberFormatter::ROUND_DOWN,
            'ruleSet' => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping' => 3,
            'style' => 'percent',
            'locale' => 'en_US',
        ]);

        $this->assertEquals('1,000.00%', $column->getDisplayedValue(1000));
    }

    public function testDisplayedCurrencyValue(): void
    {
        $column = new NumberColumn([
            'precision' => 2,
            'roundingMode' => \NumberFormatter::ROUND_DOWN,
            'ruleSet' => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping' => 3,
            'style' => 'currency',
            'currencyCode' => 'EUR',
            'locale' => 'en_US',
        ]);

        $this->assertEquals('€1,000.00', $column->getDisplayedValue(1000));
    }

    public function testDisplayedCurrencyWithoutCurrencyCode(): void
    {
        $column = new NumberColumn([
            'precision' => 2,
            'roundingMode' => \NumberFormatter::ROUND_DOWN,
            'ruleSet' => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping' => 3,
            'style' => 'currency',
            'locale' => 'en_US',
        ]);

        $this->assertEquals('$1,000.00', $column->getDisplayedValue(1000));
    }

    public function testDisplayedCurrencyWithoutAValidISO4217CCurrencyCode(): void
    {
        $column = new NumberColumn([
            'precision' => 2,
            'roundingMode' => \NumberFormatter::ROUND_DOWN,
            'ruleSet' => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping' => 3,
            'style' => 'currency',
            'currencyCode' => 'notAnISO4217C',
        ]);

        $this->expectException(\Exception::class);
        $column->getDisplayedValue(1000);
    }

    public function testDisplayedValueFromArrayValues(): void
    {
        $column = new NumberColumn([
            'style' => 'decimal',
            'values' => [100 => 200],
        ]);

        $this->assertEquals(200, $column->getDisplayedValue(100));
    }

    public function testGetFilters(): void
    {
        $this->column->setData(['operator' => Column::OPERATOR_BTW, 'from' => '10', 'to' => '20']);
        $this->assertEquals([
            new Filter(Column::OPERATOR_GT, 10),
            new Filter(Column::OPERATOR_LT, 20),
        ], $this->column->getFilters('asource'));

        $this->column->setData(['operator' => Column::OPERATOR_BTW, 'from' => 10, 'to' => 20]);
        $this->assertEquals([
            new Filter(Column::OPERATOR_GT, 10),
            new Filter(Column::OPERATOR_LT, 20),
        ], $this->column->getFilters('asource'));

        $this->column->setData(['operator' => Column::OPERATOR_ISNULL]);
        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNULL),
        ], $this->column->getFilters('asource'));
    }

    public function getStyle()
    {
        $column = new NumberColumn(['style' => 'decimal']);
        $this->assertEquals(\NumberFormatter::DECIMAL, $column->getStyle());
    }

    public function getLocale()
    {
        $column = new NumberColumn(['locale' => 'it_IT']);
        $this->assertEquals('it_IT', $column->getLocale());
    }

    public function getPrecision()
    {
        $column = new NumberColumn(['precision' => 2]);
        $this->assertEquals(2, $column->getPrecision());
    }

    public function getGrouping()
    {
        $column = new NumberColumn(['grouping' => 3]);
        $this->assertEquals(3, $column->getGrouping());
    }

    public function getRoundingMode()
    {
        $column = new NumberColumn(['roundingMode' => \NumberFormatter::ROUND_HALFDOWN]);
        $this->assertEquals(\NumberFormatter::ROUND_HALFDOWN, $column->getRoundingMode());
    }

    public function getRuleSet()
    {
        $column = new NumberColumn(['ruleSet' => \NumberFormatter::PUBLIC_RULESETS]);
        $this->assertEquals(\NumberFormatter::PUBLIC_RULESETS, $column->getRuleSet());
    }

    public function getCurrencyCode()
    {
        $column = new NumberColumn(['currencyCode' => 'USD']);
        $this->assertEquals('USD', $column->getCurrencyCode());
    }

    public function getFractional()
    {
        $column = new NumberColumn(['fractional' => true]);
        $this->assertTrue($column->getFractional());
    }

    public function getMaxFractionDigits()
    {
        $column = new NumberColumn(['maxFractionDigits' => 3]);
        $this->assertEquals(3, $column->getMaxFractionDigits());
    }

    protected function setUp(): void
    {
        $this->column = new NumberColumn();
    }
}

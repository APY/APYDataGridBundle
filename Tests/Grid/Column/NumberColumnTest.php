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
    /**
     * @var NumberColumn
     */
    private $column;

    public function testGetType()
    {
        $this->assertEquals('number', $this->column->getType());
    }

    public function testInitializeDefaultParams()
    {
        $this->assertEquals(Column::ALIGN_RIGHT, $this->column->getAlign());
        $this->assertEquals(\NumberFormatter::DECIMAL, $this->column->getStyle());
        $this->assertEquals(\Locale::getDefault(), $this->column->getLocale());
        $this->assertEquals(null, $this->column->getPrecision());
        $this->assertEquals(false, $this->column->getGrouping());
        $this->assertEquals(\NumberFormatter::ROUND_HALFUP, $this->column->getRoundingMode());
        $this->assertEquals(null, $this->column->getRuleSet());
        $this->assertEquals(null, $this->column->getCurrencyCode());
        $this->assertEquals(false, $this->column->getFractional());
        $this->assertEquals(null, $this->column->getMaxFractionDigits());
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

    public function testInitializeStyle()
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

    public function testInitializeStyleWithInvalidValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $column = new NumberColumn(['style' => 'foostyle']);
    }

    public function testInitializeLocale()
    {
        $column = new NumberColumn(['locale' => 'it']);
        $this->assertEquals('it', $column->getLocale());
    }

    public function testInitializePrecision()
    {
        $column = new NumberColumn(['precision' => 2]);
        $this->assertEquals(2, $column->getPrecision());
    }

    public function testInitializeGrouping()
    {
        $column = new NumberColumn(['grouping' => 3]);
        $this->assertEquals(3, $column->getGrouping());
    }

    public function testInitializeRoundingMode()
    {
        $column = new NumberColumn(['roundingMode' => \NumberFormatter::ROUND_HALFDOWN]);
        $this->assertEquals(\NumberFormatter::ROUND_HALFDOWN, $column->getRoundingMode());
    }

    public function testInitializeRuleSet()
    {
        $column = new NumberColumn(['ruleSet' => \NumberFormatter::PUBLIC_RULESETS]);
        $this->assertEquals(\NumberFormatter::PUBLIC_RULESETS, $column->getRuleSet());
    }

    public function testInitializeCurrencyCode()
    {
        $column = new NumberColumn(['currencyCode' => 'EUR']);
        $this->assertEquals('EUR', $column->getCurrencyCode());
    }

    public function testInizializeFractional()
    {
        $column = new NumberColumn(['fractional' => true]);
        $this->assertEquals(true, $column->getFractional());
    }

    public function testInizializeMaxFractionalDigits()
    {
        $column = new NumberColumn(['maxFractionDigits' => 2]);
        $this->assertEquals(2, $column->getMaxFractionDigits());
    }

    public function testIsQueryValid()
    {
        $this->assertTrue($this->column->isQueryValid('1'));
        $this->assertTrue($this->column->isQueryValid(1));
        $this->assertTrue($this->column->isQueryValid('1.2'));
        $this->assertTrue($this->column->isQueryValid(1.2));
        $this->assertTrue($this->column->isQueryValid([1, '1', 1.2, '1.2', 'foo']));
        $this->assertFalse($this->column->isQueryValid('foo'));
        $this->assertFalse($this->column->isQueryValid(['foo', 'bar']));
    }

    public function testRenderCellWithCallback()
    {
        $value = 1.0;
        $this->column->manipulateRenderCell(function ($value, $row, $router) {
            return (int) $value;
        });

        $result = $this->column->renderCell(
            $value,
            $this->createMock(Row::class),
            $this->createMock(Router::class)
        );

        $this->assertEquals($result, $value);
    }

    public function testDisplayedValueWithEmptyValue()
    {
        $this->assertEquals('', $this->column->getDisplayedValue(''));
        $this->assertEquals('', $this->column->getDisplayedValue(null));
    }

    public function testDisplayedPercentValue()
    {
        $column = new NumberColumn([
            'precision'         => 2,
            'roundingMode'      => \NumberFormatter::ROUND_DOWN,
            'ruleSet'           => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping'          => 3,
            'style'             => 'percent',
            'locale'            => 'en_US',
        ]);

        $this->assertEquals('1,000.00%', $column->getDisplayedValue(1000));
    }

    public function testDisplayedCurrencyValue()
    {
        $column = new NumberColumn([
            'precision'         => 2,
            'roundingMode'      => \NumberFormatter::ROUND_DOWN,
            'ruleSet'           => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping'          => 3,
            'style'             => 'currency',
            'currencyCode'      => 'EUR',
            'locale'            => 'en_US',
        ]);

        $this->assertEquals('€1,000.00', $column->getDisplayedValue(1000));
    }

    public function testDisplayedCurrencyWithoutCurrencyCode()
    {
        $column = new NumberColumn([
            'precision'         => 2,
            'roundingMode'      => \NumberFormatter::ROUND_DOWN,
            'ruleSet'           => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping'          => 3,
            'style'             => 'currency',
            'locale'            => 'en_US',
        ]);

        $this->assertEquals('$1,000.00', $column->getDisplayedValue(1000));
    }

    public function testDisplayedCurrencyWithoutAValidISO4217CCurrencyCode()
    {
        $column = new NumberColumn([
            'precision'         => 2,
            'roundingMode'      => \NumberFormatter::ROUND_DOWN,
            'ruleSet'           => \NumberFormatter::POSITIVE_PREFIX,
            'maxFractionDigits' => 2,
            'grouping'          => 3,
            'style'             => 'currency',
            'currencyCode'      => 'notAnISO4217C',
        ]);

        $this->expectException(\Exception::class);
        $column->getDisplayedValue(1000);
    }

    public function testDisplayedValueFromArrayValues()
    {
        $column = new NumberColumn([
            'style'  => 'decimal',
            'values' => [100 => 200],
        ]);

        $this->assertEquals(200, $column->getDisplayedValue(100));
    }

    public function testGetFilters()
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

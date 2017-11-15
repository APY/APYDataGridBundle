<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\DateTimeColumn;
use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use Symfony\Bundle\FrameworkBundle\Routing\Router;

class DateTimeColumnTest extends \PHPUnit_Framework_TestCase
{
    public function testGetType()
    {
        $column = new DateTimeColumn();
        $this->assertEquals('datetime', $column->getType());
    }

    public function testSetFormat()
    {
        $format = 'Y-m-d';

        $column = new DateTimeColumn();
        $column->setFormat($format);

        $this->assertAttributeEquals($format, 'format', $column);
    }

    public function testGetFormat()
    {
        $format = 'Y-m-d';

        $column = new DateTimeColumn();
        $column->setFormat($format);

        $this->assertEquals($format, $column->getFormat());
    }

    public function testSetTimezone()
    {
        $timezone = 'UTC';

        $column = new DateTimeColumn();
        $column->setTimezone($timezone);

        $this->assertAttributeEquals($timezone, 'timezone', $column);
    }

    public function testGetTimezone()
    {
        $timezone = 'UTC';

        $column = new DateTimeColumn();
        $column->setTimezone($timezone);

        $this->assertEquals($timezone, $column->getTimezone());
    }

    public function testRenderCellWithoutCallback()
    {
        $column = new DateTimeColumn();
        $column->setFormat('Y-m-d H:i:s');

        $dateTime = '2000-01-01 01:00:00';
        $now = new \DateTime($dateTime);

        $this->assertEquals(
            $dateTime,
            $column->renderCell(
                $now,
                $this->createMock(Row::class),
                $this->createMock(Router::class)
            )
        );
    }

    public function testRenderCellWithCallback()
    {
        $column = new DateTimeColumn();
        $column->setFormat('Y-m-d H:i:s');
        $column->manipulateRenderCell(function ($value, $row, $router) {
            return '01:00:00';
        });

        $dateTime = '2000-01-01 01:00:00';
        $now = new \DateTime($dateTime);

        $this->assertEquals(
            '01:00:00',
            $column->renderCell(
                $now,
                $this->createMock(Row::class),
                $this->createMock(Router::class)
            )
        );
    }

    public function testFilterWithValue()
    {
        $column = new DateTimeColumn();
        $column->setData(['operator' => Column::OPERATOR_BTW, 'from' => '2017-03-22', 'to' => '2017-03-23']);

        $this->assertEquals([
            new Filter(Column::OPERATOR_GT, new \DateTime('2017-03-22')),
            new Filter(Column::OPERATOR_LT, new \DateTime('2017-03-23')),
        ], $column->getFilters('asource'));
    }

    public function testFilterWithoutValue()
    {
        $column = new DateTimeColumn();
        $column->setData(['operator' => Column::OPERATOR_ISNULL]);

        $this->assertEquals([new Filter(Column::OPERATOR_ISNULL)], $column->getFilters('asource'));
    }

    public function testQueryIsValid()
    {
        $column = new DateTimeColumn();

        $this->assertTrue($column->isQueryValid('2017-03-22 23:00:00'));
    }

    public function testQueryIsInvalid()
    {
        $column = new DateTimeColumn();

        $this->assertFalse($column->isQueryValid('foo'));
    }

    public function testInitializeDefaultParams()
    {
        $column = new DateTimeColumn();

        $this->assertAttributeEquals(null, 'format', $column);
        $this->assertAttributeEquals([
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
        ], 'operators', $column);
        $this->assertAttributeEquals(Column::OPERATOR_EQ, 'defaultOperator', $column);
        $this->assertAttributeEquals(date_default_timezone_get(), 'timezone', $column);
    }

    public function testInitialize()
    {
        $format = 'Y-m-d H:i:s';
        $timezone = 'UTC';

        $params = [
            'format'          => $format,
            'operators'       => [Column::OPERATOR_LT, Column::OPERATOR_LTE],
            'defaultOperator' => Column::OPERATOR_LT,
            'timezone'        => $timezone,
        ];

        $column = new DateTimeColumn($params);

        $this->assertAttributeEquals($format, 'format', $column);
        $this->assertAttributeEquals([
            Column::OPERATOR_LT, Column::OPERATOR_LTE,
        ], 'operators', $column);
        $this->assertAttributeEquals(Column::OPERATOR_LT, 'defaultOperator', $column);
        $this->assertAttributeEquals($timezone, 'timezone', $column);
    }

    /**
     * @dataProvider provideDisplayInput
     */
    public function testCorrectDisplayOut($value, $expectedOutput, $timeZone = null)
    {
        $column = new DateTimeColumn();
        $column->setFormat('Y-m-d H:i:s');

        if ($timeZone !== null) {
            $column->setTimezone($timeZone);
        }

        $this->assertEquals($expectedOutput, $column->getDisplayedValue($value));
    }

    public function testDisplayValueForDateTimeImmutable()
    {
        if (PHP_VERSION_ID < 50500) {
            $this->markTestSkipped('\\DateTimeImmutable was introduced in PHP 5.5');
        }

        $now = new \DateTimeImmutable();

        $column = new DateTimeColumn();
        $column->setFormat('Y-m-d H:i:s');
        $this->assertEquals($now->format('Y-m-d H:i:s'), $column->getDisplayedValue($now));
    }

    public function testDateTimeZoneForDisplayValueIsTheSameAsTheColumn()
    {
        $column = new DateTimeColumn();
        $column->setFormat('Y-m-d H:i:s');
        $column->setTimezone('UTC');

        $now = new \DateTime('2000-01-01 01:00:00', new \DateTimeZone('Europe/Amsterdam'));

        $this->assertEquals('2000-01-01 00:00:00', $column->getDisplayedValue($now));
    }

//    public function testDisplayValueWithDefaultFormats()
//    {
//        $column = new DateTimeColumn();
//        $now = new \DateTime('2017-03-22 22:52:00');
//
//        $this->assertEquals('Mar 22, 2017, 10:52:00 PM', $column->getDisplayedValue($now));
//    }
//
//    public function testDisplayValueWithoutFormatButTimeZone()
//    {
//        $column = new DateTimeColumn();
//        $column->setTimezone('UTC');
//
//        $now = new \DateTime('2017-03-22 22:52:00', new \DateTimeZone('Europe/Amsterdam'));
//
//        $this->assertEquals('Mar 22, 2017, 9:52:00 PM', $column->getDisplayedValue($now));
//    }
//
//    public function testDisplayValueWithFallbackFormat()
//    {
//        $column = new DateTimeColumn();
//        $column->setTimezone(\IntlDateFormatter::NONE);
//
//        $now = new \DateTime('2017/03/22 22:52:00');
//
//        $this->assertEquals('2017-03-22 20:52:00', $column->getDisplayedValue($now));
//    }

    public function provideDisplayInput()
    {
        $now = new \DateTime();

        return [
            [$now, $now->format('Y-m-d H:i:s')],
            ['2016/01/01 12:13:14', '2016-01-01 12:13:14'],
            [1, '1970-01-01 00:00:01', 'UTC'],
            ['', ''],
        ];
    }
}

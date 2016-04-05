<?php
namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\DateTimeColumn;

class DateTimeColumnTest extends \PHPUnit_Framework_TestCase
{
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

        $this->assertEquals(
            $expectedOutput,
            $column->getDisplayedValue($value)
        );
    }

    public function testDisplayValueForDateTimeImmutable()
    {
        if (PHP_VERSION_ID < 50500) {
            $this->markTestSkipped('\\DateTimeImmutable was introduced in PHP 5.5');
        }

        $now = new \DateTimeImmutable();

        $column = new DateTimeColumn();
        $column->setFormat('Y-m-d H:i:s');
        $this->assertEquals(
            $now->format('Y-m-d H:i:s'),
            $column->getDisplayedValue($now)
        );
    }

    public function testDateTimeZoneForDisplayValueIsTheSameAsTheColumn()
    {
        $column = new DateTimeColumn();
        $column->setFormat('Y-m-d H:i:s');
        $column->setTimezone('UTC');

        $now = new \DateTime('2000-01-01 01:00:00', new \DateTimeZone('Europe/Amsterdam'));

        $this->assertEquals(
            '2000-01-01 00:00:00',
            $column->getDisplayedValue($now)
        );
    }

    public function provideDisplayInput()
    {
        $now = new \DateTime();
        
        return array(
            array($now, $now->format('Y-m-d H:i:s')),
            array('2016/01/01 12:13:14', '2016-01-01 12:13:14'),
            array(1, '1970-01-01 00:00:01', 'UTC')
        );
    }
}

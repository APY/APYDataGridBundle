<?php

namespace APY\DataGridBundle\Tests\Grid\Mapping\Metadata;

use APY\DataGridBundle\Grid\Mapping\Metadata\DriverHeap;
use PHPUnit\Framework\TestCase;

class DriverHeapTest extends TestCase
{
    public function testCompareOk(): void
    {
        $priority1 = $priority2 = 1;

        $driverHeap = new DriverHeap();

        $this->assertEquals(0, $driverHeap->compare($priority1, $priority2));
    }

    public function testPriority1MoreThanPriority2(): void
    {
        $priority1 = 100;
        $priority2 = 1;

        $driverHeap = new DriverHeap();

        $this->assertEquals(-1, $driverHeap->compare($priority1, $priority2));
    }

    public function testPriority1LessThanPriority2(): void
    {
        $priority1 = 1;
        $priority2 = 100;

        $driverHeap = new DriverHeap();

        $this->assertEquals(1, $driverHeap->compare($priority1, $priority2));
    }
}

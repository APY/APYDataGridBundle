<?php

namespace APY\DataGridBundle\Grid\Tests;

use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Rows;
use PHPUnit\Framework\TestCase;

class RowsTest extends TestCase
{
    /** @var Rows */
    private $rowsSUT;

    /** @var array */
    private $rows;

    public function testAddRowsOnConstruct()
    {
        $this->assertEquals(3, $this->rowsSUT->count());
    }

    public function testGetIterator()
    {
        $this->assertInstanceOf(\SplObjectStorage::class, $this->rowsSUT->getIterator());
    }

    public function testAddRow()
    {
        $this->rowsSUT->addRow($this->createMock(Row::class));
        $this->assertEquals(4, $this->rowsSUT->count());
    }

    public function testToArray()
    {
        $this->assertEquals($this->rows, $this->rowsSUT->toArray());
    }

    public function setUp()
    {
        $this->rows = [$this->createMock(Row::class), $this->createMock(Row::class), $this->createMock(Row::class)];
        $this->rowsSUT = new Rows($this->rows);
    }
}

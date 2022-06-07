<?php

namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Filter;
use PHPUnit\Framework\TestCase;

class FilterTest extends TestCase
{
    public function testCreateFilters()
    {
        $filter1 = new Filter('like', 'foo', 'column1');

        $this->assertEquals('like', $filter1->getOperator());
        $this->assertEquals('foo', $filter1->getValue());
        $this->assertEquals('column1', $filter1->getColumnName());
    }

    public function testSetOperator()
    {
        $filter = new Filter('like');
        $filter->setOperator('nlike');

        $this->assertEquals('nlike', $filter->getOperator());
    }

    public function testGetOperator()
    {
        $filter = new Filter('like');

        $this->assertEquals('like', $filter->getOperator());
    }

    public function testSetValue()
    {
        $filter = new Filter('like');
        $filter->setValue('foo');

        $this->assertEquals('foo', $filter->getValue());
    }

    public function testGetValue()
    {
        $filter = new Filter('like', 'foo');

        $this->assertEquals('foo', $filter->getValue());
    }

    public function testSetColumnName()
    {
        $filter = new Filter('like');
        $filter->setColumnName('col1');

        $this->assertEquals('col1', $filter->getColumnName());
    }

    public function testGetColumnName()
    {
        $filter = new Filter('like', null, 'col1');

        $this->assertEquals('col1', $filter->getColumnName());
    }

    public function testHasColumnName()
    {
        $filter1 = new Filter('like', 'foo', 'col1');
        $filter2 = new Filter('like');

        $this->assertTrue($filter1->hasColumnName());
        $this->assertFalse($filter2->hasColumnName());
    }
}

<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\BlankColumn;
use PHPUnit\Framework\TestCase;

class BlankColumnTest extends TestCase
{
    public function testGetType(): void
    {
        $column = new BlankColumn();

        $this->assertEquals('blank', $column->getType());
    }

    public function testInitialize(): void
    {
        self::markTestSkipped();
        $params = [
            'filterable' => true,
            'sortable' => true,
            'foo' => false,
            'bar' => true,
        ];

        $column = new BlankColumn($params);

        $this->assertAttributeEquals([
            'filterable' => false,
            'sortable' => false,
            'source' => false,
            'foo' => false,
            'bar' => true,
        ], 'params', $column);
    }
}

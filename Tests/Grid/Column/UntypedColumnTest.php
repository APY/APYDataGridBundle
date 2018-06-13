<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\UntypedColumn;
use PHPUnit\Framework\TestCase;

class UntypedColumnTest extends TestCase
{
    public function testGetParams()
    {
        $params = ['foo', 'bar'];
        $column = new UntypedColumn($params);

        $this->assertEquals($params, $column->getParams());
    }

    public function testSetType()
    {
        $type = 'text';

        $column = new UntypedColumn();
        $column->setType($type);

        $this->assertAttributeEquals($type, 'type', $column);
    }

    public function getType()
    {
        $type = 'text';

        $column = new UntypedColumn();
        $column->setType($type);

        $this->assertEquals($type, $column->getType());
    }
}

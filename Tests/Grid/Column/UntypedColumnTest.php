<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\UntypedColumn;
use PHPUnit\Framework\TestCase;

class UntypedColumnTest extends TestCase
{
    public function testGetParams(): void
    {
        $params = ['foo', 'bar'];
        $column = new UntypedColumn($params);

        $this->assertEquals($params, $column->getParams());
    }

    public function testSetType(): void
    {
        $type = 'text';

        $column = new UntypedColumn();
        $column->setType($type);

        $this->assertEquals($type, $column->getType());
    }

    public function getType()
    {
        $type = 'text';

        $column = new UntypedColumn();
        $column->setType($type);

        $this->assertEquals($type, $column->getType());
    }
}

<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\MassActionColumn;
use PHPUnit\Framework\TestCase;

class MassActionColumnTest extends TestCase
{
    /** @var MassActionColumn */
    private $column;

    public function testGetType()
    {
        $this->assertEquals('massaction', $this->column->getType());
    }

    public function testGetFilterType()
    {
        $this->assertEquals('massaction', $this->column->getFilterType());
    }

    public function testIsVisible()
    {
        $this->assertFalse($this->column->isVisible(true));
        $this->assertTrue($this->column->isVisible(false));
    }

    public function testInitialize()
    {
        $this->assertAttributeEquals([
            'id'         => MassActionColumn::ID,
            'title'      => '',
            'size'       => 15,
            'filterable' => true,
            'sortable'   => false,
            'source'     => false,
            'align'      => Column::ALIGN_CENTER,
        ], 'params', $this->column);
    }

    public function setUp()
    {
        $this->column = new MassActionColumn();
    }
}

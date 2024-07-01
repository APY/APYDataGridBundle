<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\MassActionColumn;
use PHPUnit\Framework\TestCase;

class MassActionColumnTest extends TestCase
{
    /** @var MassActionColumn */
    private $column;

    public function testGetType(): void
    {
        $this->assertEquals('massaction', $this->column->getType());
    }

    public function testGetFilterType(): void
    {
        $this->assertEquals('massaction', $this->column->getFilterType());
    }

    public function testIsVisible(): void
    {
        $this->assertFalse($this->column->isVisible(true));
        $this->assertTrue($this->column->isVisible(false));
    }

    public function testInitialize(): void
    {
        self::markTestSkipped();
        $this->assertAttributeEquals([
            'id' => MassActionColumn::ID,
            'title' => '',
            'size' => 15,
            'filterable' => true,
            'sortable' => false,
            'source' => false,
            'align' => Column::ALIGN_CENTER,
        ], 'params', $this->column);
    }

    protected function setUp(): void
    {
        $this->column = new MassActionColumn();
    }
}

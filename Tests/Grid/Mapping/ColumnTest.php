<?php

namespace APY\DataGridBundle\Tests\Grid\Mapping;

use APY\DataGridBundle\Grid\Mapping\Column;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    public function setUp(): void
    {
        $this->stringMetadata = 'foo';
        $this->arrayMetadata = ['foo' => 'bar', 'groups' => 'baz'];
    }

    public function testColumnMetadataCanBeEmpty()
    {
        $column = new Column([]);
        $this->assertEmpty($column->getMetadata());
        $this->assertEquals(['default'], $column->getGroups());
    }

    public function testColumnStringMetadataInjectedInConstructor()
    {
        $column = new Column($this->stringMetadata);
        $this->assertEquals($this->stringMetadata, $column->getMetadata());
    }

    public function testColumnArrayMetadataInjectedInConstructor()
    {
        $column = new Column($this->arrayMetadata);
        $this->assertEquals($this->arrayMetadata, $column->getMetadata());
    }
}

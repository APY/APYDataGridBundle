<?php

namespace APY\DataGridBundle\Grid\Tests\Mapping;

use APY\DataGridBundle\Grid\Mapping\Column;
use PHPUnit\Framework\TestCase;

class ColumnTest extends TestCase
{
    public function setUp()
    {
        $this->stringMetadata = 'foo';
        $this->arrayMetadata = ['foo' => 'bar', 'groups' => 'baz'];
    }

    public function testColumnMetadataCanBeEmpty()
    {
        $column = new Column([]);
        $this->assertAttributeEmpty('metadata', $column);
        $this->assertAttributeEquals(['default'], 'groups', $column);
    }

    public function testColumnStringMetadataInjectedInConstructor()
    {
        $column = new Column($this->stringMetadata);
        $this->assertAttributeEquals($this->stringMetadata, 'metadata', $column);
    }

    public function testColumnArrayMetadataInjectedInConstructor()
    {
        $column = new Column($this->arrayMetadata);
        $this->assertAttributeEquals($this->arrayMetadata, 'metadata', $column);
    }
}

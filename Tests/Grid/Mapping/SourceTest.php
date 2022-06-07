<?php

namespace APY\DataGridBundle\Tests\Grid\Mapping;

use APY\DataGridBundle\Grid\Mapping\Source;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
    protected function setUp(): void
    {
        $this->source = new Source([]);
    }

    public function testColumnsHasDefaultValue()
    {
        $this->assertEquals([], $this->source->getColumns());
    }

    public function testFilterableHasDefaultValue()
    {
        $this->assertEquals(true, $this->source->isFilterable());
    }

    public function testSortableHasDefaultValue()
    {
        $this->assertEquals(true, $this->source->isSortable());
    }

    public function testGroupsHasDefaultValue()
    {
        $expectedGroups = ['0' => 'default'];

        $this->assertEquals($expectedGroups, $this->source->getGroups());
    }

    public function testGroupByHasDefaultValue()
    {
        $this->assertEquals([], $this->source->getGroupBy());
    }

    public function testGetterColumns()
    {
        $columns = 'columns';
        $expectedColumns = [$columns];

        $this->source = new Source(['columns' => $columns]);

        $this->assertEquals($expectedColumns, $this->source->getColumns());
    }

    public function testGetterHasColumns()
    {
        $columns = 'columns';

        $this->source = new Source(['columns' => $columns]);

        $this->assertTrue($this->source->hasColumns());
    }

    public function testGetterFilterable()
    {
        $filterable = false;

        $this->source = new Source(['filterable' => $filterable]);

        $this->assertEquals($filterable, $this->source->isFilterable());
    }

    public function testGetterSortable()
    {
        $sortable = false;

        $this->source = new Source(['sortable' => $sortable]);

        $this->assertEquals($sortable, $this->source->isSortable());
    }

    public function testGetterGroups()
    {
        $groups = 'groups';
        $expectedGroups = [$groups];

        $this->source = new Source(['groups' => $groups]);

        $this->assertEquals($expectedGroups, $this->source->getGroups());
    }

    public function testGetterGroupBy()
    {
        $groupsBy = 'groupBy';
        $expectedGroupsBy = [$groupsBy];

        $this->source = new Source(['groupBy' => $groupsBy]);

        $this->assertEquals($expectedGroupsBy, $this->source->getGroupBy());
    }
}

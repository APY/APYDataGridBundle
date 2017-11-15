<?php

namespace APY\DataGridBundle\Grid\Tests\Mapping;

use APY\DataGridBundle\Grid\Mapping\Source;
use PHPUnit\Framework\TestCase;

class SourceTest extends TestCase
{
    public function setUp()
    {
        $this->source = new Source([]);
    }

    public function testColumnsHasDefaultValue()
    {
        $this->assertAttributeEquals([], 'columns', $this->source);
    }

    public function testFilterableHasDefaultValue()
    {
        $this->assertAttributeEquals(true, 'filterable', $this->source);
    }

    public function testSortableHasDefaultValue()
    {
        $this->assertAttributeEquals(true, 'sortable', $this->source);
    }

    public function testGroupsHasDefaultValue()
    {
        $expectedGroups = ['0' => 'default'];

        $this->assertAttributeEquals($expectedGroups, 'groups', $this->source);
    }

    public function testGroupByHasDefaultValue()
    {
        $this->assertAttributeEquals([], 'groupBy', $this->source);
    }

    public function testSetterColumns()
    {
        $columns = 'columns';
        $expectedColumns = [$columns];

        $this->source = new Source(['columns' => $columns]);

        $this->assertAttributeEquals($expectedColumns, 'columns', $this->source);
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

    public function testSetterFilterable()
    {
        $filterable = false;

        $this->source = new Source(['filterable' => $filterable]);

        $this->assertAttributeEquals($filterable, 'filterable', $this->source);
    }

    public function testGetterFilterable()
    {
        $filterable = false;

        $this->source = new Source(['filterable' => $filterable]);

        $this->assertEquals($filterable, $this->source->isFilterable());
    }

    public function testSetterSortable()
    {
        $sortable = false;

        $this->source = new Source(['sortable' => $sortable]);

        $this->assertAttributeEquals($sortable, 'sortable', $this->source);
    }

    public function testGetterSortable()
    {
        $sortable = false;

        $this->source = new Source(['sortable' => $sortable]);

        $this->assertEquals($sortable, $this->source->isSortable());
    }

    public function testSetterGroups()
    {
        $groups = 'groups';
        $expectedGroups = [$groups];

        $this->source = new Source(['groups' => $groups]);

        $this->assertAttributeEquals($expectedGroups, 'groups', $this->source);
    }

    public function testGetterGroups()
    {
        $groups = 'groups';
        $expectedGroups = [$groups];

        $this->source = new Source(['groups' => $groups]);

        $this->assertEquals($expectedGroups, $this->source->getGroups());
    }

    public function testSetterGroupBy()
    {
        $groupsBy = 'groupBy';
        $expectedGroupsBy = [$groupsBy];

        $this->source = new Source(['groupBy' => $groupsBy]);

        $this->assertAttributeEquals($expectedGroupsBy, 'groupBy', $this->source);
    }

    public function testGetterGroupBy()
    {
        $groupsBy = 'groupBy';
        $expectedGroupsBy = [$groupsBy];

        $this->source = new Source(['groupBy' => $groupsBy]);

        $this->assertEquals($expectedGroupsBy, $this->source->getGroupBy());
    }
}

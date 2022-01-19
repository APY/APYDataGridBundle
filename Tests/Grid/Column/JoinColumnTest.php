<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\JoinColumn;
use APY\DataGridBundle\Grid\Filter;
use PHPUnit\Framework\TestCase;

class JoinColumnTest extends TestCase
{
    private \APY\DataGridBundle\Grid\Column\JoinColumn $column;

    public function testGetType()
    {
        $this->assertEquals('join', $this->column->getType());
    }

    public function testInitializeDefaultParams()
    {
        $params = [];
        $column = new JoinColumn($params);

        $this->assertEquals([], $column->getParams());
        $this->assertEquals([], $column->getJoinColumns());
        $this->assertAttributeEquals('&nbsp;', 'separator', $column);
        $this->assertEquals(true, $column->getVisibleForSource());
        $this->assertEquals(true, $column->getIsManualField());
    }

    public function testInitialize()
    {
        $col1 = 'col1';
        $col2 = 'col2';
        $separator = '/';

        $params = [
            'columns'   => [$col1, $col2],
            'separator' => $separator,
        ];
        $column = new JoinColumn($params);

        $this->assertEquals($params, $column->getParams());
        $this->assertEquals([$col1, $col2], $column->getJoinColumns());
        $this->assertEquals($separator, $column->getSeparator());
    }

    public function testSetJoinColumns()
    {
        $col1 = 'col1';
        $col2 = 'col2';

        $this->column->setJoinColumns([$col1, $col2]);

        $this->assertEquals([$col1, $col2], $this->column->getJoinColumns());
    }

    public function testGetjoinColumns()
    {
        $col1 = 'col1';
        $col2 = 'col2';

        $this->column->setJoinColumns([$col1, $col2]);

        $this->assertEquals([$col1, $col2], $this->column->getJoinColumns());
    }

    public function testSetColumnNameOnFilters()
    {
        $col1 = 'col1';
        $col2 = 'col2';
        $separator = '/';

        $params = [
            'columns'   => [$col1, $col2],
            'separator' => $separator,
        ];

        $column = new JoinColumn($params);
        $column->setData(['operator' => Column::OPERATOR_ISNOTNULL]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_ISNOTNULL, null, $col1),
            new Filter(Column::OPERATOR_NEQ, null, $col1),
            new Filter(Column::OPERATOR_ISNOTNULL, null, $col2),
            new Filter(Column::OPERATOR_NEQ, null, $col2),
        ], $column->getFilters('asource'));
    }

    public function setUp(): void
    {
        $this->column = new JoinColumn();
    }
}

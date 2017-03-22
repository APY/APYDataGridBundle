<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\JoinColumn;
use APY\DataGridBundle\Grid\Filter;
use PHPUnit\Framework\TestCase;

class JoinColumnTest extends TestCase
{
    /** @var JoinColumn */
    private $column;

    public function testGetType()
    {
        $this->assertEquals('join', $this->column->getType());
    }

    public function testInitializeDefaultParams()
    {
        $params = [];
        $column = new JoinColumn($params);

        $this->assertAttributeEquals([], 'params', $column);
        $this->assertAttributeEquals([], 'joinColumns', $column);
        $this->assertAttributeEquals('&nbsp;', 'separator', $column);
        $this->assertAttributeEquals(true, 'visibleForSource', $column);
        $this->assertAttributeEquals(true, 'isManualField', $column);
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

        $this->assertAttributeEquals($params, 'params', $column);
        $this->assertAttributeEquals([$col1, $col2], 'joinColumns', $column);
        $this->assertAttributeEquals($separator, 'separator', $column);
    }

    public function testSetJoinColumns()
    {
        $col1 = 'col1';
        $col2 = 'col2';

        $this->column->setJoinColumns([$col1, $col2]);

        $this->assertAttributeEquals([$col1, $col2], 'joinColumns', $this->column);
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

    public function setUp()
    {
        $this->column = new JoinColumn();
    }
}

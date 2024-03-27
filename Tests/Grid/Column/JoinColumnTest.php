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

    public function testGetType(): void
    {
        $this->assertEquals('join', $this->column->getType());
    }

    public function testInitializeDefaultParams(): void
    {
        $params = [];
        $column = new JoinColumn($params);

        //        $this->assertAttributeEquals([], 'params', $column);
        $this->assertEquals([], $column->getJoinColumns());
        $this->assertEquals('&nbsp;', $column->getSeparator());
        $this->assertTrue($column->isVisibleForSource());
        $this->assertTrue($column->getIsManualField());
    }

    public function testInitialize(): void
    {
        $col1 = 'col1';
        $col2 = 'col2';
        $separator = '/';

        $params = [
            'columns' => [$col1, $col2],
            'separator' => $separator,
        ];
        $column = new JoinColumn($params);

        //        $this->assertAttributeEquals($params, 'params', $column);
        $this->assertEquals([$col1, $col2], $column->getJoinColumns());
        $this->assertEquals($separator, $column->getSeparator());
    }

    public function testSetJoinColumns(): void
    {
        $col1 = 'col1';
        $col2 = 'col2';

        $this->column->setJoinColumns([$col1, $col2]);

        $this->assertEquals([$col1, $col2], $this->column->getJoinColumns());
    }

    public function testGetjoinColumns(): void
    {
        $col1 = 'col1';
        $col2 = 'col2';

        $this->column->setJoinColumns([$col1, $col2]);

        $this->assertEquals([$col1, $col2], $this->column->getJoinColumns());
    }

    public function testSetColumnNameOnFilters(): void
    {
        $col1 = 'col1';
        $col2 = 'col2';
        $separator = '/';

        $params = [
            'columns' => [$col1, $col2],
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

    protected function setUp(): void
    {
        $this->column = new JoinColumn();
    }
}

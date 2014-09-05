<?php

namespace APY\DataGridBundle\Tests;

use APY\DataGridBundle\Grid\Columns;

class AddColumnTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->col1 = $this->getMock('APY\DataGridBundle\Grid\Column\Column');
        $this->col2 = $this->getMock('APY\DataGridBundle\Grid\Column\Column');
        $this->col3 = $this->getMock('APY\DataGridBundle\Grid\Column\Column');
        $this->newCol = $this->getMock('APY\DataGridBundle\Grid\Column\Column');
    }

    public function testAddColumnPositiveOffset()
    {
        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 1);
        $this->assertAttributeEquals(array($this->newCol, $this->col1, $this->col2, $this->col3), 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 2);
        $this->assertAttributeEquals(array($this->col1, $this->newCol, $this->col2, $this->col3), 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 3);
        $this->assertAttributeEquals(array($this->col1, $this->col2, $this->newCol, $this->col3), 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 4);
        $this->assertAttributeEquals(array($this->col1, $this->col2, $this->col3, $this->newCol), 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 5);
        $this->assertAttributeEquals(array($this->col1, $this->col2, $this->col3, $this->newCol), 'columns', $columns);
    }

    public function testAddColumnNullOffset()
    {
        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol);
        $this->assertAttributeEquals(array($this->col1, $this->col2, $this->col3, $this->newCol), 'columns', $columns);
    }

    public function testAddColumnNegativeOffset()
    {
        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -1);
        $this->assertAttributeEquals(array($this->col1, $this->col2, $this->newCol, $this->col3), 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -2);
        $this->assertAttributeEquals(array($this->col1, $this->newCol, $this->col2, $this->col3), 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -3);
        $this->assertAttributeEquals(array($this->newCol, $this->col1, $this->col2, $this->col3), 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -4);
        $this->assertAttributeEquals(array($this->newCol, $this->col1, $this->col2, $this->col3), 'columns', $columns);
    }

    protected function getBaseColumns()
    {
        $context = $this->getMock('Symfony\Component\Security\Core\SecurityContextInterface');
        $columns = new Columns($context);
        $columns->addColumn($this->col1);
        $columns->addColumn($this->col2);
        $columns->addColumn($this->col3);
        $this->assertAttributeEquals(array($this->col1, $this->col2, $this->col3), 'columns', $columns);
        return $columns;
    }
}


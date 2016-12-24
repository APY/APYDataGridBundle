<?php

namespace APY\DataGridBundle\Tests;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Columns;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AddColumnTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->col1 = $this->createMock(Column::class);
        $this->col2 = $this->createMock(Column::class);
        $this->col3 = $this->createMock(Column::class);
        $this->newCol = $this->createMock(Column::class);
    }

    public function testAddColumnPositiveOffset()
    {
        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 1);
        $this->assertAttributeEquals([$this->newCol, $this->col1, $this->col2, $this->col3], 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 2);
        $this->assertAttributeEquals([$this->col1, $this->newCol, $this->col2, $this->col3], 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 3);
        $this->assertAttributeEquals([$this->col1, $this->col2, $this->newCol, $this->col3], 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 4);
        $this->assertAttributeEquals([$this->col1, $this->col2, $this->col3, $this->newCol], 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, 5);
        $this->assertAttributeEquals([$this->col1, $this->col2, $this->col3, $this->newCol], 'columns', $columns);
    }

    public function testAddColumnNullOffset()
    {
        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol);
        $this->assertAttributeEquals([$this->col1, $this->col2, $this->col3, $this->newCol], 'columns', $columns);
    }

    public function testAddColumnNegativeOffset()
    {
        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -1);
        $this->assertAttributeEquals([$this->col1, $this->col2, $this->newCol, $this->col3], 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -2);
        $this->assertAttributeEquals([$this->col1, $this->newCol, $this->col2, $this->col3], 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -3);
        $this->assertAttributeEquals([$this->newCol, $this->col1, $this->col2, $this->col3], 'columns', $columns);

        $columns = $this->getBaseColumns();
        $columns->addColumn($this->newCol, -4);
        $this->assertAttributeEquals([$this->newCol, $this->col1, $this->col2, $this->col3], 'columns', $columns);
    }

    protected function getBaseColumns()
    {
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $columns = new Columns($authChecker);
        $columns->addColumn($this->col1);
        $columns->addColumn($this->col2);
        $columns->addColumn($this->col3);
        $this->assertAttributeEquals([$this->col1, $this->col2, $this->col3], 'columns', $columns);

        return $columns;
    }
}

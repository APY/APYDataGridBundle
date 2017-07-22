<?php

namespace APY\DataGridBundle\Grid\Tests\Source;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\UntypedColumn;
use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Source\Vector;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class VectorTest extends TestCase
{
    /**
     * @var Vector
     */
    private $vector;

    public function testCreateVectorWithEmptyData()
    {
        $this->assertAttributeEmpty('data', $this->vector);
    }

    public function testRaiseExceptionDuringVectorCreationWhenDataIsNotAVector()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Vector(['notAnArray'], []);
    }

    public function testRaiseExceptionDuringVectorCreationWhenEmptyVector()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Vector([[]], []);
    }

    public function testCreateVectorWithColumns()
    {
        $column = $this->createMock(Column::class);
        $column2 = $this->createMock(Column::class);
        $columns = [$column, $column2];

        $vector = new Vector([], $columns);

        $this->assertAttributeEquals($columns, 'columns', $vector);
    }

    public function testInitialiseWithoutData()
    {
        $this->vector->initialise($this->createMock(Container::class));

        $this->assertAttributeEmpty('columns', $this->vector);
    }

    public function testInizialiseWithGuessedColumnsMergedToAlreadySettedColumns()
    {
        $columnId = 'cId';
        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($columnId);

        $column2Id = 'c2Id';
        $column2 = $this->createMock(Column::class);
        $column2
            ->method('getId')
            ->willReturn($column2Id);

        $vector = new Vector([['c3Id' => 'c3', 'c4Id' => 'c4']], [$column, $column2]);

        $uc1 = new UntypedColumn([
            'id'         => 'c3Id',
            'title'      => 'c3Id',
            'source'     => true,
            'filterable' => true,
            'sortable'   => true,
            'visible'    => true,
            'field'      => 'c3Id',
        ]);
        $uc1->setType('text');

        $uc2 = new UntypedColumn([
            'id'         => 'c4Id',
            'title'      => 'c4Id',
            'source'     => true,
            'filterable' => true,
            'sortable'   => true,
            'visible'    => true,
            'field'      => 'c4Id',
        ]);
        $uc2->setType('text');

        $vector->initialise($this->createMock(Container::class));

        $this->assertAttributeEquals([$column, $column2, $uc1, $uc2], 'columns', $vector);
    }

    public function testInizialiseWithoutGuessedColumns()
    {
        $columnId = 'cId';
        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($columnId);

        $column2Id = 'c2Id';
        $column2 = $this->createMock(Column::class);
        $column2
            ->method('getId')
            ->willReturn($column2Id);

        $vector = new Vector([[$columnId => 'c1', $column2Id => 'c2']], [$column, $column2]);

        $vector->initialise($this->createMock(Container::class));

        $this->assertAttributeEquals([$column, $column2], 'columns', $vector);
    }

    /**
     * @dataProvider guessedColumnProvider
     */
    public function testInizializeWithGuessedColumn($vectorValue, UntypedColumn $untypedColumn, $columnType)
    {
        $untypedColumn->setType($columnType);

        $vector = new Vector($vectorValue);
        $vector->initialise($this->createMock(Container::class));

        $this->assertAttributeEquals([$untypedColumn], 'columns', $vector);
    }

    public function testExecute()
    {
        $rows = [new Row(), new Row()];
        $columns = $this->createMock(Columns::class);

        $vector = $this->createPartialMock(Vector::class, ['executeFromData']);
        $vector
            ->method('executeFromData')
            ->with($columns, 0, null, null)
            ->willReturn($rows);

        $this->assertEquals($rows, $vector->execute($columns, 0, null, null));
    }

    public function testPopulateSelectFilters()
    {
        $columns = $this->createMock(Columns::class);

        $vector = $this->createPartialMock(Vector::class, ['populateSelectFiltersFromData']);
        $vector
            ->expects($this->once())
            ->method('populateSelectFiltersFromData')
            ->with($columns, false);

        $vector->populateSelectFilters($columns);
    }

    public function testGetTotalCount()
    {
        $maxResults = 10;

        $vector = $this->createPartialMock(Vector::class, ['getTotalCountFromData']);
        $vector
            ->method('getTotalCountFromData')
            ->with($maxResults)
            ->willReturn(8);

        $this->assertEquals(8, $vector->getTotalCount($maxResults));
    }

    public function testGetHash()
    {
        $idCol1 = 'idCol1';
        $column1 = $this->createMock(Column::class);
        $column1
            ->method('getId')
            ->willReturn($idCol1);

        $idCol2 = 'idCol2';
        $column2 = $this->createMock(Column::class);
        $column2
            ->method('getId')
            ->willReturn($idCol2);

        $vector = new Vector([], [$column1, $column2]);

        $this->assertEquals('APY\DataGridBundle\Grid\Source\Vector' . md5($idCol1.$idCol2), $vector->getHash());
    }

    public function testSetId()
    {
        $id = 'id';
        $this->vector->setId($id);

        $this->assertAttributeEquals($id, 'id', $this->vector);
    }

    public function testGetId()
    {
        $id = 'id';
        $this->vector->setId($id);

        $this->assertEquals($id, $this->vector->getId());
    }

    public function guessedColumnProvider()
    {
        $uc = new UntypedColumn([
            'id'         => 'c1Id',
            'title'      => 'c1Id',
            'source'     => true,
            'filterable' => true,
            'sortable'   => true,
            'visible'    => true,
            'field'      => 'c1Id',
        ]);

        $date = new \DateTime();
        $date->setTime(0, 0, 0);

        return [
            'Empty' => [[['c1Id' => '']], $uc, 'text'],
            'Null' => [[['c1Id' => null]], $uc, 'text'],
            'Array' => [[['c1Id' => []]], $uc, 'array'],
            'Datetime' => [[['c1Id' => new \DateTime()]], $uc, 'datetime'],
            'Date' => [[['c1Id' => $date]], $uc, 'date'],
            'String but not date' => [[['c1Id' => 'thisIsAString']], $uc, 'text'],
            'Date string' => [[['c1Id' => '2017-07-22']], $uc, 'date'],
            'Datetime string' => [[['c1Id' => '2017-07-22 12:00:00']], $uc, 'datetime'],
            'True value' => [[['c1Id' => true]], $uc, 'boolean'],
            'False value' => [[['c1Id' => true]], $uc, 'boolean'],
            'True int value' => [[['c1Id' => 1]], $uc, 'boolean'],
            'False int value' => [[['c1Id' => 0]], $uc, 'boolean'],
            'True string value' => [[['c1Id' => '1']], $uc, 'boolean'],
            'False string value' => [[['c1Id' => '0']], $uc, 'boolean'],
            'Number' => [[['c1Id' => 12]], $uc, 'number'],
            'Boolean and not number' => [[['c1Id' => true], ['c1Id' => '2017-07-22']], $uc, 'text'],
            'Boolean and number' => [[['c1Id' => true], ['c1Id' => 20]], $uc, 'number'],
            'Date and not date time' => [[['c1Id' => '2017-07-22'], ['c1Id' => 20]], $uc, 'text'],
            'Date and time' => [[['c1Id' => '2017-07-22'], ['c1Id' => '2017-07-22 11:00:00']], $uc, 'datetime']
        ];
    }

    public function setUp()
    {
        $this->vector = new Vector([], []);
    }
}

class VectorObj
{
}
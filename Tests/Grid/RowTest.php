<?php

namespace APY\DataGridBundle\Grid\Tests;

use APY\DataGridBundle\Grid\Row;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class RowTest extends TestCase
{
    /** @var Row */
    private $row;

    public function testSetRepository()
    {
        $repo = $this->createMock(EntityRepository::class);
        $this->row->setRepository($repo);

        $this->assertAttributeSame($repo, 'repository', $this->row);
    }

    public function testSetPrimaryField()
    {
        $pf = 'id';
        $this->row->setPrimaryField($pf);

        $this->assertAttributeEquals($pf, 'primaryField', $this->row);
    }

    public function testGetPrimaryField()
    {
        $pf = 'id';
        $this->row->setPrimaryField($pf);

        $this->assertEquals($pf, $this->row->getPrimaryField());
    }

    public function testSetField()
    {
        $field1Id = 'col1';
        $field1Val = 'col1_val';

        $field2Id = 'col2';
        $field2Val = 'col2_val';

        $this->row->setField($field1Id, $field1Val);
        $this->row->setField($field2Id, $field2Val);

        $this->assertAttributeEquals([$field1Id => $field1Val, $field2Id => $field2Val], 'fields', $this->row);
    }

    public function testGetField()
    {
        $field = 'col1';
        $val = 'col1_val';

        $this->row->setField($field, $val);

        $this->assertEquals($val, $this->row->getField($field));
        $this->assertEmpty($this->row->getField('col2'));
    }

    public function testGetPrimaryFieldValueWithoutDefiningIt()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->row->getPrimaryFieldValue();
    }

    public function testGetPrimaryFieldValueWithoutAddingItToFields()
    {
        $this->expectException(\InvalidArgumentException::class);

        $field = 'foo';
        $fieldValue = 1;
        $primaryField = 'id';

        $this->row->setField($field, $fieldValue);
        $this->row->setPrimaryField($primaryField);

        $this->row->getPrimaryFieldValue();
    }

    public function testGetSinglePrimaryFieldValue()
    {
        $field = 'id';
        $value = 1;
        $primaryField = 'id';

        $this->row->setField($field, $value);
        $this->row->setPrimaryField($primaryField);

        $this->assertEquals($value, $this->row->getPrimaryFieldValue());
    }

    public function testGetArrayPrimaryFieldsValue()
    {
        $field1 = 'id';
        $value1 = 1;

        $field2 = 'foo';
        $value2 = 'foo_value';

        $this->row->setField($field1, $value1);
        $this->row->setField($field2, $value2);
        $this->row->setPrimaryField([$field1, $field2]);

        $this->assertEquals([$field1 => $value1, $field2 => $value2], $this->row->getPrimaryFieldValue());
    }

    public function testGetSinglePrimaryKeyValue()
    {
        $field = 'foo';
        $value = 1;

        $this->row->setField($field, $value);
        $this->row->setPrimaryField($field);

        // @todo: as you can see, primary field named foo is now translated to id: is that correct?
        $this->assertEquals(['id' => $value], $this->row->getPrimaryKeyValue());
    }

    public function testGetCompositePrimaryKeyValue()
    {
        $field1 = 'foo';
        $value1 = 1;

        $field2 = 'bar';
        $value2 = 2;

        $this->row->setField($field1, $value1);
        $this->row->setField($field2, $value2);
        $this->row->setPrimaryField([$field1, $field2]);

        $this->assertEquals([$field1 => $value1, $field2 => $value2], $this->row->getPrimaryKeyValue());
    }

    public function testGetEntity()
    {
        $field = 'foo';
        $value = 1;

        $this->row->setField($field, $value);
        $this->row->setPrimaryField($field);

        $entityDummy = $this->createMock(self::class);
        $repo = $this->createMock(EntityRepository::class);
        $repo
            ->expects($this->once())
            ->method('find')
            ->with($value)
            ->willReturn($entityDummy);
        $this->row->setRepository($repo);

        $this->assertSame($entityDummy, $this->row->getEntity());
    }

    public function testSetClass()
    {
        $class = 'Vendor/Bundle/Foo';
        $this->row->setClass($class);

        $this->assertAttributeEquals($class, 'class', $this->row);
    }

    public function testGetClass()
    {
        $class = 'Vendor/Bundle/Foo';
        $this->row->setClass($class);

        $this->assertEquals($class, $this->row->getClass());
    }

    public function testSetColor()
    {
        $color = 'red';
        $this->row->setColor($color);

        $this->assertAttributeEquals($color, 'color', $this->row);
    }

    public function testGetColor()
    {
        $color = 'blue';
        $this->row->setColor($color);

        $this->assertEquals($color, $this->row->getColor());
    }

    public function testSetLegend()
    {
        $legend = 'foo';
        $this->row->setLegend($legend);

        $this->assertAttributeEquals($legend, 'legend', $this->row);
    }

    public function testGetLegend()
    {
        $legend = 'bar';
        $this->row->setLegend($legend);

        $this->assertEquals($legend, $this->row->getLegend());
    }

    public function setUp()
    {
        $this->row = new Row();
    }
}

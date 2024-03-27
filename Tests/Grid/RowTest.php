<?php

namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Row;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class RowTest extends TestCase
{
    private Row $row;

    public function testSetRepository(): void
    {
        self::markTestSkipped();
        $repo = $this->createMock(EntityRepository::class);
        $this->row->setRepository($repo);

        $this->assertAttributeSame($repo, 'repository', $this->row);
    }

    public function testGetPrimaryField(): void
    {
        $pf = 'id';
        $this->row->setPrimaryField($pf);

        $this->assertEquals($pf, $this->row->getPrimaryField());
    }

    public function testGetField(): void
    {
        $field = 'col1';
        $val = 'col1_val';

        $this->row->setField($field, $val);

        $this->assertEquals($val, $this->row->getField($field));
        $this->assertEmpty($this->row->getField('col2'));
    }

    public function testGetPrimaryFieldValueWithoutDefiningIt(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->row->getPrimaryFieldValue();
    }

    public function testGetPrimaryFieldValueWithoutAddingItToFields(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $field = 'foo';
        $fieldValue = 1;
        $primaryField = 'id';

        $this->row->setField($field, $fieldValue);
        $this->row->setPrimaryField($primaryField);

        $this->row->getPrimaryFieldValue();
    }

    public function testGetSinglePrimaryFieldValue(): void
    {
        $field = 'id';
        $value = 1;
        $primaryField = 'id';

        $this->row->setField($field, $value);
        $this->row->setPrimaryField($primaryField);

        $this->assertEquals($value, $this->row->getPrimaryFieldValue());
    }

    public function testGetArrayPrimaryFieldsValue(): void
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

    public function testGetSinglePrimaryKeyValue(): void
    {
        $field = 'foo';
        $value = 1;

        $this->row->setField($field, $value);
        $this->row->setPrimaryField($field);

        // @todo: as you can see, primary field named foo is now translated to id: is that correct?
        $this->assertEquals(['id' => $value], $this->row->getPrimaryKeyValue());
    }

    public function testGetCompositePrimaryKeyValue(): void
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

    public function testGetEntity(): void
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

    public function testGetClass(): void
    {
        $class = 'Vendor/Bundle/Foo';
        $this->row->setClass($class);

        $this->assertEquals($class, $this->row->getClass());
    }

    public function testGetColor(): void
    {
        $color = 'blue';
        $this->row->setColor($color);

        $this->assertEquals($color, $this->row->getColor());
    }

    public function testGetLegend(): void
    {
        $legend = 'bar';
        $this->row->setLegend($legend);

        $this->assertEquals($legend, $this->row->getLegend());
    }

    protected function setUp(): void
    {
        $this->row = new Row();
    }
}

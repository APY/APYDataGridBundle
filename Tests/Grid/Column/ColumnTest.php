<?php

namespace APY\DataGridBundle\Tests\Grid\Column;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Role\Role;

class ColumnTest extends TestCase
{
    public function testInitializeDefaultValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $field = 'field';

        $mock->__initialize(['field' => $field]);

        $this->assertEquals($field, $mock->getTitle());
        $this->assertEquals(true, $mock->getSortable());
        $this->assertEquals(true, $mock->getVisible());
        $this->assertAttributeEquals(-1, 'size', $mock);
        $this->assertEquals(true, $mock->getFilterable());
        $this->assertEquals(false, $mock->getVisibleForSource());
        $this->assertEquals(false, $mock->getPrimary());
        $this->assertAttributeEquals(Column::ALIGN_LEFT, 'align', $mock);
        $this->assertAttributeEquals('text', 'inputType', $mock);
        $this->assertAttributeEquals('input', 'filterType', $mock);
        $this->assertAttributeEquals('query', 'selectFrom', $mock);
        $this->assertEquals([], $mock->getValues());
        $this->assertEquals(true, $mock->getOperatorsVisible());
        $this->assertEquals(false, $mock->getIsManualField());
        $this->assertEquals(false, $mock->getIsAggregate());
        $this->assertEquals(true, $mock->getUsePrefixTitle());
        $this->assertAttributeEquals(Column::getAvailableOperators(), 'operators', $mock);
        $this->assertAttributeEquals(Column::OPERATOR_LIKE, 'defaultOperator', $mock);
        $this->assertEquals(false, $mock->getSelectMulti());
        $this->assertEquals(false, $mock->getSelectExpanded());
        $this->assertEquals(false, $mock->getSearchOnClick());
        $this->assertAttributeEquals('html', 'safe', $mock);
        $this->assertAttributeEquals('<br />', 'separator', $mock);
    }

    public function testInitialize()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $id = 'id';
        $title = 'title';
        $sortable = false;
        $visible = false;
        $size = 2;
        $filterable = false;
        $source = true;
        $primary = true;
        $align = Column::ALIGN_RIGHT;
        $inputType = 'number';
        $field = 'field';
        $role = 'role';
        $order = 1;
        $joinType = 'left';
        $filter = 'filter';
        $selectFrom = 'source';
        $values = [1, 2, 3];
        $operatorsVisible = false;
        $isManualField = true;
        $isAggregate = true;
        $usePrefixText = false;
        $operators = [Column::OPERATOR_ISNULL, Column::OPERATOR_ISNOTNULL];
        $defaultOperator = Column::OPERATOR_ISNOTNULL;
        $selectMulti = true;
        $selectExpanded = true;
        $searchOnClick = true;
        $safe = 'safe';
        $separator = '<hr>';
        $export = true;
        $class = 'class';
        $translationDomain = 'en_EN';

        $params = [
            'id'                 => $id,
            'title'              => $title,
            'sortable'           => $sortable,
            'visible'            => $visible,
            'size'               => $size,
            'filterable'         => $filterable,
            'source'             => $source,
            'primary'            => $primary,
            'align'              => $align,
            'inputType'          => $inputType,
            'field'              => $field,
            'role'               => $role,
            'order'              => $order,
            'joinType'           => $joinType,
            'filter'             => $filter,
            'selectFrom'         => $selectFrom,
            'values'             => $values,
            'operatorsVisible'   => $operatorsVisible,
            'isManualField'      => $isManualField,
            'isAggregate'        => $isAggregate,
            'usePrefixTitle'     => $usePrefixText,
            'operators'          => $operators,
            'defaultOperator'    => $defaultOperator,
            'selectMulti'        => $selectMulti,
            'selectExpanded'     => $selectExpanded,
            'searchOnClick'      => $searchOnClick,
            'safe'               => $safe,
            'separator'          => $separator,
            'export'             => $export,
            'class'              => $class,
            'translation_domain' => $translationDomain,
        ];

        $mock->__initialize($params);

        $this->assertEquals($params, $mock->getParams());
        $this->assertEquals($id, $mock->getId());
        $this->assertEquals($title, $mock->getTitle());
        $this->assertEquals($sortable, $mock->getSortable());
        $this->assertEquals($visible, $mock->getVisible());
        $this->assertEquals($size, $mock->getSize());
        $this->assertEquals($filterable, $mock->getFilterable());
        $this->assertEquals($source, $mock->getVisibleForSource());
        $this->assertEquals($primary, $mock->getPrimary());
        $this->assertEquals($align, $mock->getAlign());
        $this->assertEquals($inputType, $mock->getInputType());
        $this->assertEquals($field, $mock->getField());
        $this->assertEquals($role, $mock->getRole());
        $this->assertEquals($order, $mock->getOrder());
        $this->assertEquals($joinType, $mock->getJoinType());
        $this->assertEquals($filter, $mock->getFilterType());
        $this->assertEquals($selectFrom, $mock->getSelectFrom());
        $this->assertEquals($values, $mock->getValues());
        $this->assertEquals($operatorsVisible, $mock->getOperatorsVisible());
        $this->assertEquals($isManualField, $mock->getIsManualField());
        $this->assertEquals($isAggregate, $mock->getIsAggregate());
        $this->assertEquals($usePrefixText, $mock->getUsePrefixTitle());
        $this->assertEquals($operators, $mock->getOperators());
        $this->assertEquals($defaultOperator, $mock->getDefaultOperator());
        $this->assertEquals($selectMulti, $mock->getSelectMulti());
        $this->assertEquals($selectExpanded, $mock->getSelectExpanded());
        $this->assertEquals($searchOnClick, $mock->getSearchOnClick());
        $this->assertEquals($safe, $mock->getSafe());
        $this->assertEquals($separator, $mock->getSeparator());
        $this->assertEquals($export, $mock->getExport());
        $this->assertEquals($class, $mock->getClass());
        $this->assertEquals($translationDomain, $mock->getTranslationDomain());
    }

    public function testRenderCellWithCallback()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $value = 0;
        $row = $this->createMock(Row::class);
        $router = $this->createMock(Router::class);

        $mock->manipulateRenderCell(fn($value, $row, $router) => 1);

        $this->assertEquals(1, $mock->renderCell($value, $row, $router));
    }

    public function testRenderCellWithBoolValue()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $row = $this->createMock(Row::class);
        $router = $this->createMock(Router::class);

        $mock->setValues([1 => 'foo']);
        $this->assertEquals('foo', $mock->renderCell(1, $row, $router));

        $mock->setValues(['1' => 'bar']);
        $this->assertEquals('bar', $mock->renderCell('1', $row, $router));
    }

    public function testRenderCell()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $row = $this->createMock(Row::class);
        $router = $this->createMock(Router::class);

        $mock->setValues(['foo' => 'bar']);
        $this->assertEquals('bar', $mock->renderCell('foo', $row, $router));
    }

    public function testManipulateRenderCell()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $value = 0;
        $row = $this->createMock(Row::class);
        $router = $this->createMock(Router::class);

        $callback = fn($value, $row, $router) => 1;
        $mock->manipulateRenderCell($callback);

        $this->assertEquals($callback, $mock->getCallback());
    }

    public function testSetId()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setId(1);

        $this->assertEquals(1, $mock->getId());
    }

    public function testGetId()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setId(1);

        $this->assertEquals(1, $mock->getId());
    }

    public function testGetRenderBlockId()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setId('foo.bar:foobar');

        $this->assertEquals('foo_bar_foobar', $mock->getRenderBlockId());
    }

    public function testSetTitle()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $title = 'title';
        $mock->setTitle($title);

        $this->assertEquals($title, $mock->getTitle());
    }

    public function testGetTitle()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $title = 'title';
        $mock->setTitle($title);

        $this->assertEquals($title, $mock->getTitle());
    }

    public function testSetVisible()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $isVisible = true;
        $mock->setVisible($isVisible);

        $this->assertEquals($isVisible, $mock->getVisible());
    }

    public function testItIsNotVisibleWhenNotExported()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $isVisible = false;
        $mock->setVisible($isVisible);

        $exported = false;
        $this->assertFalse($mock->isVisible($exported));
    }

    public function testItIsVisibleIfNotExported()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $isVisible = true;
        $mock->setVisible($isVisible);

        $exported = false;
        $this->assertTrue($mock->isVisible($exported));
    }

    public function testItIsVisibleIfNotExportedAndRoleNotSetted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $isVisible = true;
        $mock->setVisible($isVisible);
        $mock->setAuthorizationChecker($this->createMock(AuthorizationCheckerInterface::class));

        $exported = false;
        $this->assertTrue($mock->isVisible($exported));
    }

    public function testItIsVisibleIfNotExportedAndGranted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $role = 'ROLE_USER';
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->with($role)->willReturn(true);

        $isVisible = true;
        $mock->setVisible($isVisible);
        $mock->setAuthorizationChecker($authChecker);
        $mock->setRole($role);

        $exported = false;
        $this->assertTrue($mock->isVisible($exported));
    }

    public function testItIsNotVisibleIfNotExportedButNotGranted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $role = 'ROLE_USER';
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->with($role)->willReturn(false);

        $isVisible = true;
        $mock->setVisible($isVisible);
        $mock->setAuthorizationChecker($authChecker);
        $mock->setRole($role);

        $exported = false;
        $this->assertFalse($mock->isVisible($exported));
    }

    public function testItIsNotVisibleWhenExported()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $export = false;
        $mock->setExport($export);

        $exported = true;
        $this->assertFalse($mock->isVisible($exported));
    }

    public function testItIsVisibleIfExported()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $export = true;
        $mock->setExport($export);

        $exported = true;
        $this->assertTrue($mock->isVisible($exported));
    }

    public function testItIsVisibleIfExportedAndRoleNotSetted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $export = true;
        $mock->setExport($export);
        $mock->setAuthorizationChecker($this->createMock(AuthorizationCheckerInterface::class));

        $exported = true;
        $this->assertTrue($mock->isVisible($exported));
    }

    public function testItIsVisibleIfExportedAndGranted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $role = 'ROLE_USER';
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->with($role)->willReturn(true);

        $export = true;
        $mock->setExport($export);
        $mock->setAuthorizationChecker($authChecker);
        $mock->setRole($role);

        $exported = true;
        $this->assertTrue($mock->isVisible($exported));
    }

    public function testItIsNotVisibleIfExportedButNotGranted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $role = 'ROLE_USER';
        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $authChecker->method('isGranted')->with($role)->willReturn(false);

        $export = true;
        $mock->setExport($export);
        $mock->setAuthorizationChecker($authChecker);
        $mock->setRole($role);

        $exported = true;
        $this->assertFalse($mock->isVisible($exported));
    }

    public function testIsNotSortedWhenNotOrdered()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertEquals(false, $mock->getIsSorted());
    }

    public function testIsSortedWhenOrdered()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOrder(1);

        $this->assertEquals(true, $mock->getIsSorted());
    }

    public function testSetSortable()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSortable(true);

        $this->assertEquals(true, $mock->getSortable());
    }

    public function testIsSortable()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSortable(true);

        $this->assertTrue(true, $mock->isSortable());
    }

    public function testIsNotFilteredIfNeitherOperatorNorOperandsSetted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertFalse($mock->isFiltered());
    }

    public function testIsNotFilteredIfFromOperandHasDefaultValue()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['from' => Column::DEFAULT_VALUE]);

        $this->assertFalse($mock->isFiltered());
    }

    public function testIsNotFilteredIfToOperandHasDefaultValue()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['to' => Column::DEFAULT_VALUE]);

        $this->assertFalse($mock->isFiltered());
    }

    public function testIsNotFilteredIfOperatorNeitherIsIsNullNorIsNotNull()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_LIKE]);

        $this->assertFalse($mock->isFiltered());
    }

    public function testIsFilteredIfFromOperandHasValueDifferentThanDefault()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['from' => 1]);

        $this->assertTrue($mock->isFiltered());
    }

    public function testIsFilteredIfToOperandHasValueDifferentThanDefault()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['to' => 1]);

        $this->assertTrue($mock->isFiltered());
    }

    public function testIsFilteredIfOperatorIsNull()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_ISNULL]);

        $this->assertTrue($mock->isFiltered());
    }

    public function testIsFilteredIfOperatorIsNotNull()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_ISNOTNULL]);

        $this->assertTrue($mock->isFiltered());
    }

    public function testSetFilterable()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setFilterable(true);

        $this->assertEquals(true, $mock->getFilterable());
    }

    public function testIsFilterable()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setFilterable(true);

        $this->assertTrue($mock->isFilterable());
    }

    public function testItDoesNotSetOrderIfOrderIsNull()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOrder(null);

        $this->assertEquals(null, $mock->getOrder());
        $this->assertEquals(false, $mock->getIsSorted());
    }

    public function testItDoesSetOrderIfZero()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOrder(0);

        $this->assertAttributeEquals(0, 'order', $mock);
        $this->assertEquals(true, $mock->getIsSorted());
    }

    public function testItDoesSetOrder()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOrder(1);

        $this->assertEquals(1, $mock->getOrder());
        $this->assertEquals(true, $mock->getIsSorted());
    }

    public function testGetOrder()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOrder(1);

        $this->assertEquals(1, $mock->getOrder());
    }

    public function testRaiseExceptionIfSizeNotAllowed()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->expectException(\InvalidArgumentException::class);

        $mock->setSize(-2);
    }

    public function testAutoResize()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSize(-1);

        $this->assertAttributeEquals(-1, 'size', $mock);
    }

    public function testSetSize()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSize(2);

        $this->assertEquals(2, $mock->getSize());
    }

    public function testGetSize()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSize(3);

        $this->assertEquals(3, $mock->getSize());
    }

    public function testDataDefaultIfNoDataSetted()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData([]);

        $this->assertAttributeEquals([
            'operator' => Column::OPERATOR_LIKE,
            'from'     => Column::DEFAULT_VALUE,
            'to'       => Column::DEFAULT_VALUE,
        ], 'data', $mock);
    }

    public function testSetNullOperatorWithoutFromToValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_ISNULL]);

        $this->assertAttributeEquals([
            'operator' => Column::OPERATOR_ISNULL,
            'from'     => Column::DEFAULT_VALUE,
            'to'       => Column::DEFAULT_VALUE,
        ], 'data', $mock);
    }

    public function testSetNotNullOperatorWithoutFromToValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_ISNOTNULL]);

        $this->assertAttributeEquals([
            'operator' => Column::OPERATOR_ISNOTNULL,
            'from'     => Column::DEFAULT_VALUE,
            'to'       => Column::DEFAULT_VALUE,
        ], 'data', $mock);
    }

    public function testDoesNotSetDataIfOperatorNotNotNullOrNullNoFromToValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $operators = array_flip(Column::getAvailableOperators());
        unset($operators[Column::OPERATOR_ISNOTNULL]);
        unset($operators[Column::OPERATOR_ISNULL]);

        foreach (array_keys($operators) as $operator) {
            $mock->setData(['operator' => $operator]);

            $this->assertAttributeEquals([
                'operator' => Column::OPERATOR_LIKE,
                'from'     => Column::DEFAULT_VALUE,
                'to'       => Column::DEFAULT_VALUE,
            ], 'data', $mock);
        }
    }

    public function testItSetsData()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $operators = array_flip(Column::getAvailableOperators());
        unset($operators[Column::OPERATOR_ISNOTNULL]);
        unset($operators[Column::OPERATOR_ISNULL]);

        foreach (array_keys($operators) as $operator) {
            $mock->setData(['operator' => $operator, 'from' => 'from', 'to' => 'to']);

            $this->assertAttributeEquals([
                'operator' => $operator,
                'from'     => 'from',
                'to'       => 'to',
            ], 'data', $mock);
        }
    }

    public function testGetDataNullOpearatorWithoutValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_ISNULL]);

        $this->assertEquals([
            'operator' => Column::OPERATOR_ISNULL,
        ], $mock->getData());
    }

    public function testGetDataNotNullOpearatorWithoutValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $mock->setData(['operator' => Column::OPERATOR_ISNOTNULL]);

        $this->assertEquals([
            'operator' => Column::OPERATOR_ISNOTNULL,
        ], $mock->getData());
    }

    public function testGetEmptyDataIfOperatorNotNotNullOrNullNoFromToValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $operators = array_flip(Column::getAvailableOperators());
        unset($operators[Column::OPERATOR_ISNOTNULL]);
        unset($operators[Column::OPERATOR_ISNULL]);

        foreach (array_keys($operators) as $operator) {
            $mock->setData(['operator' => $operator]);

            $this->assertEmpty($mock->getData());
        }
    }

    public function testGetNullData()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        try {
            $mock->getData();
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testGetFiltersWithoutData()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        try {
            $mock->getFilters('aSource');
        } catch (\Exception $exception) {
            $this->fail($exception->getMessage());
        }
    }

    public function testGetData()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $operators = array_flip(Column::getAvailableOperators());
        unset($operators[Column::OPERATOR_ISNOTNULL]);
        unset($operators[Column::OPERATOR_ISNULL]);

        foreach (array_keys($operators) as $operator) {
            $mock->setData(['operator' => $operator, 'from' => 'from', 'to' => 'to']);

            $this->assertEquals([
                'operator' => $operator,
                'from'     => 'from',
                'to'       => 'to',
            ], $mock->getData());
        }
    }

    public function testQueryIsAlwaysValid()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertTrue($mock->isQueryValid('foo'));
    }

    public function testSetVisibleForSource()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setVisibleForSource(true);

        $this->assertEquals(true, $mock->getVisibleForSource());
    }

    public function testIsVisibleForSource()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setVisibleForSource(true);

        $this->assertTrue($mock->isVisibleForSource());
    }

    public function testSetPrimary()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setPrimary(true);

        $this->assertEquals(true, $mock->getPrimary());
    }

    public function testIsPrimary()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setPrimary(true);

        $this->assertTrue($mock->isPrimary());
    }

    public function testItThrowsExceptionIfSetAnAlignNotAllowed()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->expectException(\InvalidArgumentException::class);

        $mock->setAlign('foo');
    }

    public function testSetAlign()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setAlign(Column::ALIGN_RIGHT);

        $this->assertAttributeEquals(Column::ALIGN_RIGHT, 'align', $mock);
    }

    public function testGetAlign()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setAlign(Column::ALIGN_RIGHT);

        $this->assertEquals(Column::ALIGN_RIGHT, $mock->getAlign());
    }

    public function testSetInputType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setInputType('string');

        $this->assertAttributeEquals('string', 'inputType', $mock);
    }

    public function testGetInputType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setInputType('string');

        $this->assertEquals('string', $mock->getInputType());
    }

    public function testSetField()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setField('foo');

        $this->assertAttributeEquals('foo', 'field', $mock);
    }

    public function testGetField()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setField('foo');

        $this->assertEquals('foo', $mock->getField());
    }

    public function testSetRole()
    {
        $role = 'ROLE_USER';
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setRole($role);

        $this->assertEquals($role, $mock->getRole());
    }

    public function testGetRole()
    {
        $role = 'ROLE_USER';
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setRole($role);

        $this->assertEquals($role, $mock->getRole());
    }

    public function testSetFilterType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setFilterType('TEXTBOX');

        $this->assertAttributeEquals('textbox', 'filterType', $mock);
    }

    public function testGetFilterType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setFilterType('TEXTBOX');

        $this->assertEquals('textbox', $mock->getFilterType());
    }

    public function testSetDataJunction()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setDataJunction(Column::DATA_DISJUNCTION);

        $this->assertAttributeEquals(Column::DATA_DISJUNCTION, 'dataJunction', $mock);
    }

    public function testGetDataJunction()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setDataJunction(Column::DATA_DISJUNCTION);

        $this->assertEquals(Column::DATA_DISJUNCTION, $mock->getDataJunction());
    }

    public function testItThrowsExceptionIfSetDefaultOperatorWithOperatorNotAllowed()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->expectException(\Exception::class);

        $mock->setDefaultOperator('foo');
    }

    public function testSetDefaultOperator()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setDefaultOperator(Column::OPERATOR_LTE);

        $this->assertAttributeEquals(Column::OPERATOR_LTE, 'defaultOperator', $mock);
    }

    public function testGetDefaultOperator()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setDefaultOperator(Column::OPERATOR_LTE);

        $this->assertEquals(Column::OPERATOR_LTE, $mock->getDefaultOperator());
    }

    public function testHasOperator()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertTrue($mock->hasOperator(Column::OPERATOR_LIKE));
        $this->assertFalse($mock->hasOperator('foo'));
    }

    public function testSetOperatorsVisible()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOperatorsVisible(false);

        $this->assertEquals(false, $mock->getOperatorsVisible());
    }

    public function testGetOperatorsVisible()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOperatorsVisible(false);

        $this->assertFalse($mock->getOperatorsVisible());
    }

    public function testSetValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $values = [0 => 'foo', 1 => 'bar'];
        $mock->setValues($values);

        $this->assertEquals($values, $mock->getValues());
    }

    public function testGetValues()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $values = [0 => 'foo', 1 => 'bar'];
        $mock->setValues($values);

        $this->assertEquals($values, $mock->getValues());
    }

    public function testSetSelectFrom()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectFrom('source');

        $this->assertAttributeEquals('source', 'selectFrom', $mock);
    }

    public function testGetSelectFrom()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectFrom('source');

        $this->assertEquals('source', $mock->getSelectFrom());
    }

    public function testSetSelectMulti()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectMulti(true);

        $this->assertEquals(true, $mock->getSelectMulti());
    }

    public function testGetSelectMulti()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectMulti(true);

        $this->assertTrue($mock->getSelectMulti());
    }

    public function testSetSelectExpanded()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectExpanded(true);

        $this->assertEquals(true, $mock->getSelectExpanded());
    }

    public function testGetSelectExpanded()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectExpanded(true);

        $this->assertTrue($mock->getSelectExpanded());
    }

    public function testSetAuthChecker()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $mock->setAuthorizationChecker($authChecker);

        $this->assertEquals($authChecker, $mock->getAuthorizationChecker());
    }

    public function testNoParentType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertEmpty($mock->getParentType());
    }

    public function testNoType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertEmpty($mock->getType());
    }

    public function testIsFilterSubmitOnChange()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectMulti(true);

        $this->assertFalse($mock->isFilterSubmitOnChange());

        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectMulti(false);

        $this->assertTrue($mock->isFilterSubmitOnChange());
    }

    public function testSetSearchOnClick()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSearchOnClick(false);

        $this->assertEquals(false, $mock->getSearchOnClick());
    }

    public function testGetSearchOnClick()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSearchOnClick(false);

        $this->assertFalse($mock->getSearchOnClick());
    }

    public function testSetSafe()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSafe('html');

        $this->assertAttributeEquals('html', 'safe', $mock);
    }

    public function testGetSafe()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSafe('html');

        $this->assertEquals('html', $mock->getSafe());
    }

    public function testSetSeparator()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSeparator(';');

        $this->assertAttributeEquals(';', 'separator', $mock);
    }

    public function testGetSeparator()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSeparator(';');

        $this->assertEquals(';', $mock->getSeparator());
    }

    public function testSetJoinType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setJoinType('left');

        $this->assertAttributeEquals('left', 'joinType', $mock);
    }

    public function testGetJoinType()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setJoinType('left');

        $this->assertEquals('left', $mock->getJoinType());
    }

    public function testSetExport()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setExport(true);

        $this->assertEquals(true, $mock->getExport());
    }

    public function testGetExport()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setExport(true);

        $this->assertTrue($mock->getExport());
    }

    public function testSetClass()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setClass('aClass');

        $this->assertAttributeEquals('aClass', 'class', $mock);
    }

    public function testGetClass()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setClass('aClass');

        $this->assertEquals('aClass', $mock->getClass());
    }

    public function testSetIsManualField()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setIsManualField(true);

        $this->assertEquals(true, $mock->getIsManualField());
    }

    public function testGetIsManualField()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setIsManualField(true);

        $this->assertTrue($mock->getIsManualField());
    }

    public function testSetIsAggregate()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setIsAggregate(true);

        $this->assertEquals(true, $mock->getIsAggregate());
    }

    public function testGetIsAggregate()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setIsAggregate(true);

        $this->assertTrue($mock->getIsAggregate());
    }

    public function testSetUsePrefixTitle()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setUsePrefixTitle(false);

        $this->assertEquals(false, $mock->getUsePrefixTitle());
    }

    public function testGetUsePrefixTitle()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setUsePrefixTitle(false);

        $this->assertFalse($mock->getUsePrefixTitle());
    }

    public function testSetTranslationDomain()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setTranslationDomain('it');

        $this->assertAttributeEquals('it', 'translationDomain', $mock);
    }

    public function testGetTranslationDomain()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setTranslationDomain('it');

        $this->assertEquals('it', $mock->getTranslationDomain());
    }

    public function testGetFiltersWithoutOperator()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertEmpty($mock->getFilters('aSource'));
    }

    public function testGetFiltersBtwWithoutFrom()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_BTW, 'to' => 10]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_LT, 10),
        ], $mock->getFilters('aSource'));
    }

    public function testGetFiltersBtwWithoutTo()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_BTW, 'from' => 1]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_GT, 1),
        ], $mock->getFilters('aSource'));
    }

    public function testGetFiltersBtw()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_BTW, 'from' => 1, 'to' => 10]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_GT, 1),
            new Filter(Column::OPERATOR_LT, 10),
        ], $mock->getFilters('aSource'));
    }

    public function testGetFiltersBtweWithoutFrom()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_BTWE, 'to' => 10]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_LTE, 10),
        ], $mock->getFilters('aSource'));
    }

    public function testGetFiltersBtweWithoutTo()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_BTWE, 'from' => 1]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_GTE, 1),
        ], $mock->getFilters('aSource'));
    }

    public function testGetFiltersBtwe()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => Column::OPERATOR_BTWE, 'from' => 1, 'to' => 10]);

        $this->assertEquals([
            new Filter(Column::OPERATOR_GTE, 1),
            new Filter(Column::OPERATOR_LTE, 10),
        ], $mock->getFilters('aSource'));
    }

    public function testGetFiltersNullNoNull()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $mock->setData(['operator' => Column::OPERATOR_ISNULL]);
        $this->assertEquals([new Filter(Column::OPERATOR_ISNULL)], $mock->getFilters('aSource'));

        $mock->setData(['operator' => Column::OPERATOR_ISNOTNULL]);
        $this->assertEquals([new Filter(Column::OPERATOR_ISNOTNULL)], $mock->getFilters('aSource'));
    }

    public function testGetFiltersLikeCombinationsNoMulti()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $operators = [
            Column::OPERATOR_LIKE,
            Column::OPERATOR_RLIKE,
            Column::OPERATOR_LLIKE,
            Column::OPERATOR_SLIKE,
            Column::OPERATOR_RSLIKE,
            Column::OPERATOR_LSLIKE,
            Column::OPERATOR_EQ,
        ];

        foreach ($operators as $operator) {
            $mock->setData(['operator' => $operator]);
            $this->assertEmpty($mock->getFilters('aSource'));
            $this->assertAttributeEquals(Column::DATA_CONJUNCTION, 'dataJunction', $mock);
        }
    }

    public function testGetFiltersLikeCombinationsMulti()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setSelectMulti(true);

        $operators = [
            Column::OPERATOR_LIKE,
            Column::OPERATOR_RLIKE,
            Column::OPERATOR_LLIKE,
            Column::OPERATOR_SLIKE,
            Column::OPERATOR_RSLIKE,
            Column::OPERATOR_LSLIKE,
            Column::OPERATOR_EQ,
        ];

        foreach ($operators as $operator) {
            $mock->setData(['operator' => $operator]);
            $this->assertEmpty($mock->getFilters('aSource'));
            $this->assertAttributeEquals(Column::DATA_DISJUNCTION, 'dataJunction', $mock);
        }
    }

    public function testGetFiltersNotLikeCombination()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $operators = [
            Column::OPERATOR_NEQ,
            Column::OPERATOR_NLIKE,
            Column::OPERATOR_NSLIKE,
        ];

        foreach ($operators as $operator) {
            $mock->setData(['operator' => $operator, 'from' => [1, 2, 3]]);
            $this->assertEquals([
                new Filter($operator, 1),
                new Filter($operator, 2),
                new Filter($operator, 3),
            ], $mock->getFilters('aSource'));
        }
    }

    public function testGetFiltersWithNotHandledOperator()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setData(['operator' => 'foo', 'from' => 'bar']);

        $this->assertEquals([
            new Filter(Column::OPERATOR_LIKE, 'bar'),
        ], $mock->getFilters('aSource'));
    }

    public function testSetOperators()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setOperators([
            Column::OPERATOR_ISNULL,
            Column::OPERATOR_ISNOTNULL,
        ]);

        $this->assertEquals([
            Column::OPERATOR_ISNULL,
            Column::OPERATOR_ISNOTNULL,
        ], $mock->getOperators());
    }

    public function testGetOperators()
    {
        $mock = $this->getMockForAbstractClass(Column::class);

        $this->assertEquals(Column::getAvailableOperators(), $mock->getOperators());
    }

    public function testItHasDqlFunctionWithoutMatchesResultArray()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setField('foo:bar:foobar');

        $this->assertEquals(1, $mock->hasDQLFunction());
    }

    public function testItHasDqlFunctionWithMatchesResultArray()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setField('foo:bar:foobar');

        $result = [];
        $this->assertEquals(1, $mock->hasDQLFunction($result));
        $this->assertEquals([
            0            => 'foo:bar:foobar',
            'all'        => 'foo:bar:foobar',
            1            => 'foo:bar:foobar',
            'field'      => 'foo',
            2            => 'foo',
            'function'   => 'bar',
            3            => 'bar',
            4            => ':',
            'parameters' => 'foobar',
            5            => 'foobar',
        ], $result);
    }

    public function testItHasNotDqlFunctionWithoutMatchesResultArray()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setField('foo');

        $this->assertEquals(0, $mock->hasDQLFunction());
    }

    public function testItHasNotDqlFunctionWithMatchesResultArray()
    {
        $mock = $this->getMockForAbstractClass(Column::class);
        $mock->setField('foo');

        $result = [];
        $this->assertEquals(0, $mock->hasDQLFunction($result));
        $this->assertEmpty($result);
    }
}

<?php

namespace APY\DataGridBundle\Grid\Tests\Source;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Helper\ColumnsIterator;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Mapping\Metadata\Metadata;
use APY\DataGridBundle\Grid\Rows;
use APY\DataGridBundle\Grid\Source\Document;
use Doctrine\ODM\MongoDB\Cursor;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\Query\Builder;
use Doctrine\ODM\MongoDB\Query\Expr;
use Doctrine\ODM\MongoDB\Query\Query;
use MongoDB\BSON\Regex;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

class DocumentTest extends TestCase
{
    /**
     * @var Document
     */
    private $document;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $manager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $odmMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $metadata;

    public function testConstructedWithDefaultGroup()
    {
        $name = 'name';
        $document = new Document($name);

        $this->assertAttributeEquals($name, 'documentName', $document);
        $this->assertAttributeEquals('default', 'group', $document);
    }

    public function testConstructedWithAGroup()
    {
        $name = 'name';
        $group = 'aGroup';
        $document = new Document($name, $group);

        $this->assertAttributeEquals($name, 'documentName', $document);
        $this->assertAttributeEquals($group, 'group', $document);
    }

    public function testInitQueryBuilder()
    {
        $qb = $this->createMock(Builder::class);

        $this->document->initQueryBuilder($qb);

        $this->assertAttributeEquals($qb, 'query', $this->document);
        $this->assertAttributeNotSame($qb, 'query', $this->document);
    }

    /**
     * @dataProvider fieldsMetadataProvider
     */
    public function testGetFieldsMetadataProv($name, array $fieldMapping, array $metadata, array $referenceMappings = [])
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $property
            ->method('getName')
            ->willReturn($name);

        $this
            ->odmMetadata
            ->method('getReflectionProperties')
            ->willReturn([$property]);
        $this
            ->odmMetadata
            ->method('getFieldMapping')
            ->with($name)
            ->willReturn($fieldMapping);

        $this->assertEquals($metadata, $this->document->getFieldsMetadata('name', 'default'));

        $this->assertAttributeEquals($referenceMappings, 'referencedMappings', $this->document);
    }

    public function testGetFieldsMetadata()
    {
        $name1 = 'propName1';

        $property1 = $this->createMock(\ReflectionProperty::class);
        $property1
            ->method('getName')
            ->willReturn($name1);

        $name2 = 'propName2';

        $property2 = $this->createMock(\ReflectionProperty::class);
        $property2
            ->method('getName')
            ->willReturn($name2);

        $getFieldMappingMap = [
            [$name1, ['type' => 'text']],
            [$name2, ['type' => 'text']]
        ];

        $this
            ->odmMetadata
            ->method('getReflectionProperties')
            ->willReturn([$property1, $property2]);
        $this
            ->odmMetadata
            ->method('getFieldMapping')
            ->will($this->returnValueMap($getFieldMappingMap));

        $this->assertEquals(
            [$name1 => [
                'title' => $name1,
                'type' => 'text',
                'source' => true,
            ],
            $name2 => [
                'title' => $name2,
                'type' => 'text',
                'source' => true,
            ]],
            $this->document->getFieldsMetadata('name', 'default')
        );
    }

    public function testGetRepository()
    {
        $repo = $this->createMock(DocumentRepository::class);

        $this
            ->manager
            ->method('getRepository')
            ->with('name')
            ->willReturn($repo);

        $this->assertEquals($repo, $this->document->getRepository());
    }

    public function testRaiseExceptionIfDeleteNonExistentObjectFromId()
    {
        $this->assertEquals('name', $this->document->getHash());
    }

    public function testDeleteRaiseExceptionIfIdNotMatchAnyObject()
    {
        $this->expectException(\Exception::class);

        $repo = $this->createMock(DocumentRepository::class);

        $this
            ->manager
            ->method('getRepository')
            ->willReturn($repo);

        $this->document->delete(['id']);
    }

    public function testDelete()
    {
        $id1 = 'id1';
        $id2 = 'id2';
        $ids = [$id1, $id2];

        $doc1 = $this->createMock(DocumentEntity::class);
        $doc2 = $this->createMock(DocumentEntity::class);

        $repo = $this->createMock(DocumentRepository::class);
        $repo
            ->method('find')
            ->withConsecutive([$id1], [$id2])
            ->willReturnOnConsecutiveCalls($doc1, $doc2);

        $this
            ->manager
            ->method('getRepository')
            ->willReturn($repo);

        $this
            ->manager
            ->expects($this->exactly(2))
            ->method('remove')
            ->withConsecutive([$doc1], [$doc2]);
        $this
            ->manager
            ->expects($this->atLeastOnce())
            ->method('flush');

        $this->document->delete($ids);
    }

    public function testExceuteWithExistentNewQueryBuilder()
    {
        $builder = $this->stubBuilder();

        $this->document->initQueryBuilder($builder);

        $columnsIterator = $this->createMock(ColumnsIterator::class);
        $this->assertEquals(new Rows(), $this->document->execute($columnsIterator));
    }

    public function testExecuteWithPageAndLimit()
    {
        $page = 2;
        $limit = 3;
        $total = 6;

        $builder = $this->stubBuilder();

        $builder
            ->expects($this->once())
            ->method('skip')
            ->with($total);

        $columnsIterator = $this->createMock(ColumnsIterator::class);
        $this->assertEquals(new Rows(), $this->document->execute($columnsIterator, $page, $limit));
    }

    public function testExecuteWithLimit()
    {
        $limit = 3;

        $builder = $this->stubBuilder();

        $builder
            ->expects($this->once())
            ->method('limit')
            ->with($limit);

        $columnsIterator = $this->createMock(ColumnsIterator::class);
        $this->assertEquals(new Rows(), $this->document->execute($columnsIterator, 0, $limit));
    }

    public function testExecuteWithLimitPageAndMaxResultDecreasingLimit()
    {
        $page = 1;
        $limit = 3;
        $maxResult = 5;
        $newLimit = $maxResult - $page * $limit;

        $builder = $this->stubBuilder();

        $builder
            ->expects($this->once())
            ->method('limit')
            ->with($newLimit);

        $columnsIterator = $this->createMock(ColumnsIterator::class);
        $this->assertEquals(new Rows(), $this->document->execute($columnsIterator, $page, $limit, $maxResult));
    }

    public function testExecuteWithLimitPageAndMaxResultNotDecreasingLimit()
    {
        $page = 2;
        $limit = 7;
        $maxResult = 50;

        $builder = $this->stubBuilder();

        $builder
            ->expects($this->once())
            ->method('limit')
            ->with($limit);

        $columnsIterator = $this->createMock(ColumnsIterator::class);
        $this->assertEquals(new Rows(), $this->document->execute($columnsIterator, $page, $limit, $maxResult));
    }

    public function testExecuteWithMaxResult()
    {
        $maxResult = 50;

        $builder = $this->stubBuilder();

        $builder
            ->expects($this->once())
            ->method('limit')
            ->with($maxResult);

        $columnsIterator = $this->createMock(ColumnsIterator::class);
        $this->assertEquals(new Rows(), $this->document->execute($columnsIterator, 0, 0, $maxResult));
    }

    public function testExecuteWithNoFilteredSubColumns()
    {
        $document = new DocumentEntity();
        $this->stubBuilder([$document]);

        $id = 'colId';
        $subCol = 'subCol';
        $colId = $id . '.' .$subCol;

        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($colId);

        $this->arrangeGetFieldsMetadata($id, ['type' => 'one', 'reference' => true, 'targetDocument' => 'foo']);

        $this->document->getFieldsMetadata('name', 'default');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $result = $this->document->execute($columnsIterator);
        $iterator = $result->getIterator();

        $this->assertEquals(1, $result->count());
        foreach ($iterator as $row) {
            $this->assertAttributeEquals([$colId => 'subColValue'], 'fields', $row);
        }
    }

    /**
     * @dataProvider filterProvider
     */
    public function testExecuteWithFiltersOnSubColumns($operator, $method, $filterValue, $params)
    {
        $document = new DocumentEntity();

        $cursor = $this->mockCursor([$document]);

        $query = $this->createMock(Query::class);
        $query
            ->method('execute')
            ->willReturn($cursor);

        $builder = $this->createMock(Builder::class);
        $builder
            ->method('getQuery')
            ->willReturn($query);

        $filter = $this->stubFilter($operator, $filterValue);

        $id = 'colId';
        $subCol = 'subCol';
        $colId = $id . '.' .$subCol;

        $column = $this->stubColumnWithFilters($colId, [$filter]);

        $helperCursor = $this->mockCursor([]);

        $helperQuery = $this->createMock(Query::class);
        $helperQuery
            ->method('execute')
            ->willReturn($helperCursor);

        $helperBuilder = $this->stubBuilderWithField($subCol, $helperQuery);

        $createQbMap = [
            ['name', $builder],
            ['foo', $helperBuilder]
        ];

        $this
            ->manager
            ->method('createQueryBuilder')
            ->will($this->returnValueMap($createQbMap));

        $this->arrangeGetFieldsMetadata($id, ['type' => 'one', 'reference' => true, 'targetDocument' => 'foo']);

        $this->document->getFieldsMetadata('name', 'default');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $helperBuilder
            ->expects($this->once())
            ->method($method)
            ->with($params);

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithFiltersOnSubColumnsAndEmptyCursorResult()
    {
        $document = new DocumentEntity();
        $cursor = $this->mockCursor([$document]);

        $subDoc = new DocumentEntity();
        $helperCursor = $this->mockHelperCursor([$subDoc]);

        $filter = $this->stubFilter(Column::OPERATOR_EQ, 'aValue');

        $id = 'colId';
        $subCol = 'subCol';
        $colId = $id . '.' .$subCol;
        $column = $this->stubColumnWithFilters($colId, [$filter]);

        $query = $this->createMock(Query::class);
        $query
            ->method('execute')
            ->willReturn($cursor);

        $builder = $this->createMock(Builder::class);
        $builder
            ->method('expr')
            ->willReturn($builder);
        $builder
            ->method('field')
            ->with($id)
            ->willReturn($builder);
        $builder
            ->method('references')
            ->with($subDoc)
            ->willReturn($builder);
        $builder
            ->method('getQuery')
            ->willReturn($query);

        $helperQuery = $this->createMock(Query::class);
        $helperQuery
            ->method('execute')
            ->willReturn($helperCursor);

        $helperBuilder = $this->stubBuilderWithField($subCol, $helperQuery);

        $createQbMap = [
            ['name', $builder],
            ['foo', $helperBuilder]
        ];

        $this
            ->manager
            ->method('createQueryBuilder')
            ->will($this->returnValueMap($createQbMap));

        $this->arrangeGetFieldsMetadata($id, ['type' => 'one', 'reference' => true, 'targetDocument' => 'foo']);

        $this->document->getFieldsMetadata('name', 'default');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->once())
            ->method('addOr')
            ->with($builder);
        $builder
            ->expects($this->never())
            ->method('select');

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithFiltersOnSubColumnsAndCursorWithMoreThanOneResult()
    {
        $document = new DocumentEntity();
        $cursor = $this->mockCursor([$document]);

        $subDoc1 = new DocumentEntity();
        $subDoc2 = new DocumentEntity();
        $helperCursor = $this->mockHelperCursor([$subDoc1, $subDoc2]);
        $helperCursor
            ->method('count')
            ->willReturn(2);

        $filter = $this->stubFilter(Column::OPERATOR_EQ, 'aValue');

        $id = 'colId';
        $subCol = 'subCol';
        $colId = $id . '.' .$subCol;
        $column = $this->stubColumnWithFilters($colId, [$filter]);

        $query = $this->createMock(Query::class);
        $query
            ->method('execute')
            ->willReturn($cursor);

        $builder = $this->createMock(Builder::class);
        $builder
            ->method('expr')
            ->willReturn($builder);
        $builder
            ->method('field')
            ->with($id)
            ->willReturn($builder);
        $builder
            ->method('references')
            ->withConsecutive([$subDoc1], [$subDoc2])
            ->willReturn($builder);
        $builder
            ->method('getQuery')
            ->willReturn($query);

        $helperQuery = $this->createMock(Query::class);
        $helperQuery
            ->method('execute')
            ->willReturn($helperCursor);

        $helperBuilder = $this->stubBuilderWithField($subCol, $helperQuery);

        $createQbMap = [
            ['name', $builder],
            ['foo', $helperBuilder]
        ];

        $this
            ->manager
            ->method('createQueryBuilder')
            ->will($this->returnValueMap($createQbMap));

        $this->arrangeGetFieldsMetadata($id, ['type' => 'one', 'reference' => true, 'targetDocument' => 'foo']);

        $this->document->getFieldsMetadata('name', 'default');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->once())
            ->method('addOr')
            ->with($builder);
        $builder
            ->expects($this->once())
            ->method('select')
            ->with($id);

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithFiltersOnSubColumnsAndCursorWithOneResult()
    {
        $document = new DocumentEntity();
        $cursor = $this->mockCursor([$document]);

        $subDoc = new DocumentEntity();
        $helperCursor = $this->mockHelperCursor([$subDoc]);
        $helperCursor
            ->method('count')
            ->willReturn(1);

        $filter = $this->stubFilter(Column::OPERATOR_EQ, 'aValue');

        $id = 'colId';
        $subCol = 'subCol';
        $colId = $id . '.' .$subCol;
        $column = $this->stubColumnWithFilters($colId, [$filter]);

        $query = $this->createMock(Query::class);
        $query
            ->method('execute')
            ->willReturn($cursor);

        $builder = $this->stubBuilderWithField($id, $query);

        $helperQuery = $this->createMock(Query::class);
        $helperQuery
            ->method('execute')
            ->willReturn($helperCursor);

        $helperBuilder = $this->stubBuilderWithField($subCol, $helperQuery);

        $createQbMap = [
            ['name', $builder],
            ['foo', $helperBuilder]
        ];

        $this
            ->manager
            ->method('createQueryBuilder')
            ->will($this->returnValueMap($createQbMap));

        $this->arrangeGetFieldsMetadata($id, ['type' => 'one', 'reference' => true, 'targetDocument' => 'foo']);

        $this->document->getFieldsMetadata('name', 'default');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->once())
            ->method('references')
            ->with($subDoc);
        $builder
            ->expects($this->once())
            ->method('select')
            ->with($id);
        $builder
            ->expects($this->never())
            ->method('addOr')
            ->with($builder);

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithSubColumnsButNotGetter()
    {
        $this->expectException(\Exception::class);

        $document = new DocumentEntity();
        $this->stubBuilder([$document]);

        $id = 'colId';
        $subCol = 'subCol1';
        $colId = $id . '.' .$subCol;
        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($colId);

        $this->arrangeGetFieldsMetadata($id, ['type' => 'one', 'reference' => true, 'targetDocument' => 'foo']);

        $this->document->getFieldsMetadata('name', 'default');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithSortedColumn()
    {
        $document = new DocumentEntity();
        $builder = $this->stubBuilder([$document]);

        $colId = 'colId';
        $colField = 'colField';
        $colOrder = 'asc';
        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isSorted')
            ->willReturn(true);
        $column
            ->method('getField')
            ->willReturn($colField);
        $column
            ->method('getOrder')
            ->willReturn($colOrder);

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->once())
            ->method('sort')
            ->with($colField, $colOrder);

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithPrimaryColumnAndDataDisjunction()
    {
        $document = new DocumentEntity();

        $expr = $this->createMock(Expr::class);
        $expr
            ->method('field')
            ->willReturn($expr);

        $builder = $this->stubBuilder([$document]);
        $builder
            ->method('expr')
            ->willReturn($expr);

        $this
            ->manager
            ->method('createQueryBuilder')
            ->with('name')
            ->willReturn($builder);

        $filter = $this->stubFilter(Column::OPERATOR_EQ, 'aValue');

        $colId = 'colId';

        $column = $this->stubColumnWithFilters($colId, [$filter], true);
        $column
            ->method('getDataJunction')
            ->willReturn(Column::DATA_DISJUNCTION);

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $column
            ->expects($this->once())
            ->method('setFilterable')
            ->with(false);

        $builder
            ->expects($this->never())
            ->method('addOr');

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithPrimaryColumnAndDataConjunction()
    {
        $document = new DocumentEntity();

        $builder = $this->stubBuilder([$document]);
        $builder
            ->method('field')
            ->willReturn($builder);

        $filter = $this->stubFilter(Column::OPERATOR_EQ, 'aValue');

        $colId = 'colId';

        $column = $this->stubColumnWithFilters($colId, [$filter], true);
        $columnsIterator = $this->mockColumnsIterator([$column]);

        $column
            ->expects($this->once())
            ->method('setFilterable')
            ->with(false);

        $builder
            ->expects($this->never())
            ->method('field');

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithoutPrimaryColumnDataDisjunctionAndNotFiltered()
    {
        $document = new DocumentEntity();
        $builder = $this->stubBuilder([$document]);

        $filterEqValue = 'filterValue';
        $filterEq = $this->stubFilter(Column::OPERATOR_EQ, $filterEqValue);

        $colId = 'colId';

        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilters')
            ->with('document')
            ->willReturn([$filterEq]);
        $column
            ->method('getDataJunction')
            ->willReturn(Column::DATA_DISJUNCTION);

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->never())
            ->method('addOr');

        $this->document->execute($columnsIterator);
    }

    public function testExecuteWithoutPrimaryColumnDataConjunctionAndNotFiltered()
    {
        $document = new DocumentEntity();
        $builder = $this->stubBuilder([$document]);

        $filter = $this->stubFilter(Column::OPERATOR_EQ, 'aValue');

        $colId = 'colId';

        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilters')
            ->with('document')
            ->willReturn([$filter]);

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->never())
            ->method('field');

        $this->document->execute($columnsIterator);
    }

    /**
     * @dataProvider filterProvider
     */
    public function testExecuteWithoutPrimaryColumnDataDisjunctionAndFilters($operator, $method, $filterValue, $params)
    {
        $document = new DocumentEntity();

        $expr = $this->createMock(Expr::class);
        $expr
            ->method('field')
            ->willReturn($expr);
        $expr
            ->method('addOr')
            ->willReturn($expr);

        $builder = $this->stubBuilder([$document]);
        $builder
            ->method('expr')
            ->willReturn($expr);

        $filter = $this->stubFilter($operator, $filterValue);

        $colId = 'colId';

        $column = $this->stubColumnWithFilters($colId, [$filter]);
        $column
            ->method('getDataJunction')
            ->willReturn(Column::DATA_DISJUNCTION);

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->once())
            ->method('addOr');
        $expr
            ->expects($this->once())
            ->method($method)
            ->with($params);

        $this->document->execute($columnsIterator);
    }

    /**
     * @dataProvider filterProvider
     */
    public function testExecuteWithoutPrimaryColumnDataConjunctionAndFilters($operator, $method, $filterValue, $params)
    {
        $document = new DocumentEntity();

        $builder = $this->stubBuilder([$document]);
        $builder
            ->method('field')
            ->willReturn($builder);

        $filter = $this->stubFilter($operator, $filterValue);

        $colId = 'colId';

        $column = $this->stubColumnWithFilters($colId, [$filter]);
        $column
            ->method('getDataJunction')
            ->willReturn(Column::DATA_CONJUNCTION);

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $builder
            ->expects($this->once())
            ->method($method)
            ->with($params);

        $this->document->execute($columnsIterator);
    }

    public function testExecuteAddingCorrectFieldsToRow()
    {
        $document = new DocumentEntity();
        $this->stubBuilder([$document]);

        $colId = 'colId';

        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($colId);

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $result = $this->document->execute($columnsIterator);

        $this->assertEquals(1, $result->count());
        foreach ($columnsIterator as $row) {
            $this->assertAttributeEquals([$colId => 'subColValue'], 'fields', $row);
        }
    }

    public function testGetTotalCountWithoutMaxResults()
    {
        $document = new DocumentEntity();
        $this->stubBuilder([$document]);

        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn('colId');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $this->document->execute($columnsIterator);

        $this->assertEquals(1, $this->document->getTotalCount());
    }

    public function testGetTotalCountWithMaxResults()
    {
        $document = new DocumentEntity();
        $document2 = new DocumentEntity();
        $this->stubBuilder([$document, $document2]);

        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn('colId');

        $columnsIterator = $this->mockColumnsIterator([$column]);

        $this->document->execute($columnsIterator);

        $this->assertEquals(1, $this->document->getTotalCount(1));
    }

    public function testReturnsColumns()
    {
        $columns = $this->createMock(Columns::class);

        $column = $this->createMock(Column::class);
        $column2 = $this->createMock(Column::class);
        $cols = [$column, $column2];

        $splObjStorage = $this->createMock(\SplObjectStorage::class);

        $splObjStorage
            ->expects($this->at(0))
            ->method('rewind');

        $counter = 1;
        foreach ($cols as $k => $v) {
            $splObjStorage
                ->expects($this->at($counter++))
                ->method('valid')
                ->willReturn(true);

            $splObjStorage
                ->expects($this->at($counter++))
                ->method('current')
                ->willReturn($v);

            $splObjStorage
                ->expects($this->at($counter++))
                ->method('key')
                ->willReturn($k);

            $splObjStorage
                ->expects($this->at($counter))
                ->method('next');
        }

        $this
            ->metadata
            ->method('getColumnsFromMapping')
            ->with($columns)
            ->willReturn($splObjStorage);

        $columns
            ->expects($this->exactly(2))
            ->method('addColumn')
            ->withConsecutive($column, $column2);

        $this->document->getColumns($columns);
    }

    public function testPopulateSelectFilters()
    {
        // @todo Don't know how to move on with __clone method on stubs / mocks
    }

    public function setUp()
    {
        $name = 'name';
        $this->document = new Document($name);

        $reflectionClassName = 'aName';
        $reflectionClass = $this->createMock(\ReflectionClass::class);
        $reflectionClass
            ->method('getName')
            ->willReturn($reflectionClassName);

        $odmMetadata = $this->createMock(ClassMetadata::class);
        $odmMetadata
            ->method('getReflectionClass')
            ->willReturn($reflectionClass);

        $this->odmMetadata = $odmMetadata;

        $documentManager = $this->createMock(DocumentManager::class);
        $documentManager
            ->method('getClassMetadata')
            ->with($name)
            ->willReturn($odmMetadata);

        $this->manager = $documentManager;

        $metadata = $this->createMock(Metadata::class);
        $this->metadata = $metadata;

        $mapping = $this->createMock(Manager::class);
        $mapping
            ->method('getMetadata')
            ->with($reflectionClassName, 'default')
            ->willReturn($metadata);

        $containerGetMap = [
            ['doctrine.odm.mongodb.document_manager', Container::EXCEPTION_ON_INVALID_REFERENCE, $documentManager],
            ['grid.mapping.manager', Container::EXCEPTION_ON_INVALID_REFERENCE, $mapping]
        ];

        $container = $this->createMock(Container::class);
        $container
            ->method('get')
            ->will($this->returnValueMap($containerGetMap));

        $mapping
            ->expects($this->once())
            ->method('addDriver')
            ->with($this->document, -1);

        $this->document->initialise($container);
    }

    private function stubBuilder(array $documents = [])
    {
        $cursor = $this->mockCursor($documents);

        $query = $this->createMock(Query::class);
        $query
            ->method('execute')
            ->willReturn($cursor);

        $builder = $this->createMock(Builder::class);
        $builder
            ->method('getQuery')
            ->willReturn($query);
        
        $this
            ->manager
            ->method('createQueryBuilder')
            ->with('name')
            ->willReturn($builder);

        return $builder;
    }

    private function stubBuilderWithField($col, $query)
    {
        $builder = $this->createMock(Builder::class);
        $builder
            ->method('field')
            ->with($col)
            ->willReturn($builder);
        $builder
            ->method('getQuery')
            ->willReturn($query);

        return $builder;
    }

    private function stubColumnWithFilters($colId, $filters, $isPrimary = false)
    {
        $column = $this->createMock(Column::class);
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isPrimary')
            ->willReturn($isPrimary);
        $column
            ->method('isFiltered')
            ->willReturn(true);
        $column
            ->method('getFilters')
            ->with('document')
            ->willReturn($filters);

        return $column;
    }

    private function stubFilter($operator, $filterValue)
    {
        $filter = $this->createMock(Filter::class);
        $filter
            ->method('getOperator')
            ->willReturn($operator);
        $filter
            ->method('getValue')
            ->willReturn($filterValue);

        return $filter;
    }

    /**
     * @param string $name
     * @param array $fieldMapping
     */
    private function arrangeGetFieldsMetadata($name, array $fieldMapping)
    {
        $property = $this->createMock(\ReflectionProperty::class);
        $property
            ->method('getName')
            ->willReturn($name);

        $this
            ->odmMetadata
            ->method('getReflectionProperties')
            ->willReturn([$property]);
        $this
            ->odmMetadata
            ->method('getFieldMapping')
            ->with($name)
            ->willReturn($fieldMapping);
    }

    /**
     * @param array $elements
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockColumnsIterator(array $elements)
    {
        $colIter = $this->createMock(ColumnsIterator::class);

        $colIter
            ->expects($this->at(0))
            ->method('rewind');

        $counter = 1;
        foreach ($elements as $k => $v) {
            $colIter
                ->expects($this->at($counter++))
                ->method('valid')
                ->willReturn(true);

            $colIter
                ->expects($this->at($counter++))
                ->method('current')
                ->willReturn($v);

            $colIter
                ->expects($this->at($counter++))
                ->method('key')
                ->willReturn($k);

            $colIter
                ->expects($this->at($counter++))
                ->method('next');
        }

        return $colIter;
    }

    /**
     * @param array $resources
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockCursor(array $resources)
    {
        $cursor = $this->createMock(Cursor::class);

        if (empty($resources)) {
            return $cursor;
        }

        $cursor
            ->expects($this->at(0))
            ->method('count')
            ->willReturn(count($resources));

        $cursor
            ->expects($this->at(1))
            ->method('rewind');

        $counter = 2;
        foreach ($resources as $k => $v) {
            $cursor
                ->expects($this->at($counter++))
                ->method('valid')
                ->willReturn(true);

            $cursor
                ->expects($this->at($counter++))
                ->method('current')
                ->willReturn($v);
        }

        return $cursor;
    }

    /**
     * @param array $resources
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function mockHelperCursor(array $resources)
    {
        $cursor = $this->createMock(Cursor::class);

        if (empty($resources)) {
            return $cursor;
        }

        $cursor
            ->expects($this->at(0))
            ->method('count')
            ->willReturn(count($resources));

        $counter = 1;
        foreach ($resources as $k => $v) {
            $cursor
                ->expects($this->at($counter++))
                ->method('valid')
                ->willReturn(true);

            $cursor
                ->expects($this->at($counter++))
                ->method('current')
                ->willReturn($v);
        }

        return $cursor;
    }

    public function filterProvider()
    {
        $value = 'filterValue';

        return [
            'Filter EQ' => [Column::OPERATOR_EQ, 'equals', $value, $value],
            'Filter LIKE' => [Column::OPERATOR_LIKE, 'equals', $value, new Regex($value, 'i')],
            'Filter NLIKE' => [Column::OPERATOR_NLIKE, 'equals', $value, new Regex('^((?!' . $value . ').)*$', 'i')],
            'Filter RLIKE' => [Column::OPERATOR_RLIKE, 'equals', $value, new Regex('^' . $value, 'i')],
            'Filter LLIKE' => [Column::OPERATOR_LLIKE, 'equals', $value, new Regex($value . '$', 'i')],
            'Filter SLIKE' => [Column::OPERATOR_SLIKE, 'equals', $value, new Regex($value, '')],
            'Filter NSLIKE' => [Column::OPERATOR_NSLIKE, 'equals', $value, $value],
            'Filter RSLIKE' => [Column::OPERATOR_RSLIKE, 'equals', $value, new Regex('^' . $value, '')],
            'Filter LSLIKE' => [Column::OPERATOR_LSLIKE, 'equals', $value, new Regex($value . '$', '')],
            'Filter NEQ' => [Column::OPERATOR_NEQ, 'equals', $value, new Regex('^(?!' . $value . '$).*$', 'i')],
            'Filter ISNULL' => [Column::OPERATOR_ISNULL, 'exists', $value, false],
            'Filter ISNOTNULL' => [Column::OPERATOR_ISNOTNULL, 'exists', $value, true]
        ];
    }

    public function fieldsMetadataProvider()
    {
        $name = 'propName';
        $fieldName = 'fieldName';

        return [
            'Title only' => [
                $name,
                ['type' => 'text'],
                [$name => ['title' => $name, 'source' => true, 'type' => 'text']]
            ],
            'Field name' => [
                $name,
                ['type' => 'text', 'fieldName' => $fieldName],
                [$name => ['title' => $name, 'source' => true, 'type' => 'text', 'field' => $fieldName, 'id' => $fieldName]]
            ],
            'Not primary' => [
                $name,
                ['type' => 'text', 'id' => 'notId'],
                [$name => ['title' => $name, 'source' => true, 'type' => 'text']]
            ],
            'Primary' => [
                $name,
                ['type' => 'text', 'id' => 'id'],
                [$name => ['title' => $name, 'source' => true, 'type' => 'text', 'primary' => true]]
            ],
            'Id type' => [
                $name,
                ['type' => 'id', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'String type' => [
                $name,
                ['type' => 'string', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Bin custom type' => [
                $name,
                ['type' => 'bin_custom', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Bin func type' => [
                $name,
                ['type' => 'bin_func', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Bin md5 type' => [
                $name,
                ['type' => 'bin_md5', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Bin type' => [
                $name,
                ['type' => 'bin', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Bin uuid type' => [
                $name,
                ['type' => 'bin_uuid', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'File type' => [
                $name,
                ['type' => 'file', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Key type' => [
                $name,
                ['type' => 'key', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Increment type' => [
                $name,
                ['type' => 'increment', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Int type' => [
                $name,
                ['type' => 'int', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'number',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Float type' => [
                $name,
                ['type' => 'float', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'number',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Boolean type' => [
                $name,
                ['type' => 'boolean', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'boolean',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Date type' => [
                $name,
                ['type' => 'date', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'date',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Timestamp type' => [
                $name,
                ['type' => 'timestamp', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'date',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'Collection type' => [
                $name,
                ['type' => 'collection', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'array',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'One type' => [
                $name,
                ['type' => 'one', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'array',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true]]
            ],
            'One cardinality false ref type' => [
                $name,
                ['type' => 'one', 'id' => 'id', 'fieldName' => $fieldName, 'reference' => 'aa'],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'array',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true,
                ]]
            ],
            'One cardinality with reference type' => [
                $name,
                ['type' => 'one', 'id' => 'id', 'fieldName' => $fieldName, 'reference' => true, 'targetDocument' => 'foo'],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'array',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true,
                ]],
                [$name => 'foo']
            ],
            'Many type' => [
                $name,
                ['type' => 'many', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'array',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true,
                ]]
            ],
            'Many type with non configured types map type' => [
                $name,
                ['type' => 'foo', 'id' => 'id', 'fieldName' => $fieldName],
                [$name => [
                    'title' => $name,
                    'source' => true,
                    'type' => 'text',
                    'field' => $fieldName,
                    'id' => $fieldName,
                    'primary' => true,
                ]]
            ]
        ];
    }
}

class DocumentEntity
{
    private $colId;

    private $subCol;

    public function __construct()
    {
        $this->colId = $this;
        $this->subCol = 'subColValue';
    }

    public function getColId()
    {
        return $this->colId;
    }

    public function getSubCol()
    {
        return $this->subCol;
    }
}
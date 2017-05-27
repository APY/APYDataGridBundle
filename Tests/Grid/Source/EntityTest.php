<?php

namespace APY\DataGridBundle\Tests\Grid\Source;

use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Mapping\Metadata\Metadata;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\JoinColumn;
use APY\DataGridBundle\Grid\Column\NumberColumn;
use APY\DataGridBundle\Grid\Column\DateTimeColumn;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\NoResultException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\DependencyInjection\Container;
use PHPUnit\Framework\TestCase;
use Doctrine\ORM\EntityManagerInterface;

class EntityTest extends TestCase
{
    private $entity;

    private $entityName = 'Cms:Page';

    private $managerName = 'mydatabase';

    private $joins = [];

    private $group = 'default';

    private $hints = [];

    private $tableAlias = '_a';

    private $groupBy = ['bar'];

    private $page = 0;

    private $limit = 0;

    private $queryMock;

    private $managerMock;

    private $objectMock;

    private $metadataMock;

    private $queryBuilderMock;

    private $columnMock;

    private $ormMetadata;

    private $filterMock;

    private $columnSingleMock;

    private $compositeExpression;

    private $expressionMock;

    private $entityRepositoryMock;

    private $doctrineMock;

    private $container;

    private $mappingMock;

    public function testSourceConstruct()
    {
        $this->assertAttributeEquals($this->entityName, 'entityName', $this->entity);
        $this->assertAttributeEquals($this->managerName, 'managerName', $this->entity);
        $this->assertAttributeEquals($this->joins, 'joins', $this->entity);
        $this->assertAttributeEquals($this->group, 'group', $this->entity);
        $this->assertAttributeEquals($this->hints, 'hints', $this->entity);
        $this->assertAttributeEquals($this->tableAlias, 'tableAlias', $this->entity);
    }

    public function testInitalise()
    {
        $this->initialise();

        $this->assertAttributeEquals($this->managerMock, 'manager', $this->entity);
        $this->assertAttributeEquals($this->objectMock->name, 'class', $this->entity);
        $this->assertAttributeEquals($this->metadataMock, 'metadata', $this->entity);
        $this->assertAttributeEquals($this->groupBy, 'groupBy', $this->entity);
    }

    public function testGetColumns()
    {
        $this->initialise();

        $columnMockToAdd = $this->createMock(Column::class);

        $splObjectStorage = new \SplObjectStorage;
        $splObjectStorage->attach($columnMockToAdd);

        $this->metadataMock
            ->method('getColumnsFromMapping')
            ->willReturn($splObjectStorage);

        $columnMock = $this->createMock(Columns::class);
        $columnMock
            ->expects($this->once())
            ->method('addColumn')
            ->with($columnMockToAdd);

        $this->entity->getColumns($columnMock);
    }

    public function testInitQueryBuilder()
    {
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->stubQueryBuilderWithGetRootAliases();

        $this->entity->initQueryBuilder($this->queryBuilderMock);

        $this->assertAttributeEquals('foo', 'tableAlias', $this->entity);
    }

    public function testExecuteWithoutAnyConfiguration()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->queryBuilderExpectAtLeastOnceMethods();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertAttributeEquals($this->queryBuilderMock, 'querySelectfromSource', $this->entity);
        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithNoInstanceOfQueryBuilder()
    {
        $this->objectMock = new \stdClass();
        $this->objectMock->name = 'foo';

        $this->initialiseOrmMetadata();

        $this->arrangeExecute();

        $this->initialiseManager();
        $this->stubManagerWithGetRepository('foo');
        $this->managerMock
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilderMock);

        $this->initialiseWithoutManager();

        $this->queryBuilderExpectAtLeastOnceMethods();

        $result = $this->entity->execute($this->columnMock);

        $this->assertAttributeEquals($this->queryBuilderMock, 'querySelectfromSource', $this->entity);
        $this->assertEquals(0, $result->count());
    }

    private function queryBuilderExpectAtLeastOnceMethods()
    {
        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('resetDQLPart')
            ->with('groupBy');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('addGroupBy');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('getQuery');
    }

    public function testExecuteWithNotManualColumnSortedAndFiltered()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator('nlike');

        $this->arrangeColumnSingleMock(false, true, false, true, $this->filterMock);

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('addSelect');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('orderBy');

        $this->initialise();

        $this->compositeExpression
            ->expects($this->atLeastOnce())
            ->method('add');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithGroupByFieldNameWithDot()
    {
        $this->groupBy = ['foo.bar'];

        $this->arrangeExecute();
        $this->initialise();

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('addGroupBy');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithGroupByFieldNameWithColumn()
    {
        $this->groupBy = ['foo:bar'];

        $this->arrangeExecute();
        $this->initialise();

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('addGroupBy');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithJoinColumnSorted()
    {
        $this->arrangeExecute();

        $this->columnSingleMock = $this->createMock(JoinColumn::class);

        $this->arrangeColumn();

        $this->arrangeColumnSingleMock(false, false, false, true);

        $this->columnSingleMock
            ->expects($this->any())
            ->method('getJoinColumns')
            ->willReturn(['']);

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('addOrderBy');

        $this->initialise();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_LIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, '%foo%', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorLLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_LLIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, '%foo', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorRLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_RLIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, 'foo%', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorNLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_NLIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, '%foo%', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorSLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_SLIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, '%foo%', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorLSLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_LSLIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, '%foo', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorRSLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_RSLIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, 'foo%', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorNSLikeReturnLikeOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator(Column::OPERATOR_NSLIKE);
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('like');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, '%foo%', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredAndOperatorDefaultReturnOperator()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator('notLike');
        $this->stubFilterGetValue('foo');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->initialise();

        $this->expressionMock
            ->expects($this->atLeastOnce())
            ->method('notLike');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setParameter')
            ->with(123, 'foo', null);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredWithHavingClause()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator('nlike');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('andHaving');

        $this->initialise();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnFilteredWithDisjunction()
    {
        $this->arrangeExecute();

        $this->stubFilterWithGetOperator('nlike');

        $this->arrangeColumnSingleMock(false, true, true, false, $this->filterMock);

        $this->columnSingleMock
            ->method('getDataJunction')
            ->willReturn(Column::DATA_DISJUNCTION);

        $this->compositeExpression
            ->expects($this->atLeastOnce())
            ->method('add');

        $this->initialise();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnAndLeftJoin()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->arrangeColumnSingleMock(false, false, false, false, $this->filterMock);
        $this->stubColumnSingleWithGetField('foo.bar');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('leftJoin');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteSortedWithManualColumnReturnField()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->arrangeColumnSingleMock(true, false, false, true);

        $this->columnSingleMock
            ->expects($this->exactly(2))
            ->method('getField')
            ->willReturn('foo.bar');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithFieldNameWithDQLAndDistinct()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->arrangeColumnSingleMock(false, false, false, false);
        $this->stubColumnSingleWithGetField('foo:bar');
        $this->stubColumnSingleWithHasDQLFunction('distinct', 'bar', 'baz');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteSortedColumnWithFieldNameWithDQLAndAliasTrue()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->arrangeColumnSingleMock(false, false, false, true);
        $this->stubColumnSingleWithGetField('foo:bar');
        $this->stubColumnSingleWithHasDQLFunction('foo', 'bar', 'baz');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('addGroupBy');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteSortedJoinColumnWithFieldNameWithAliasFalse()
    {
        $this->columnSingleMock = $this->createMock(JoinColumn::class);

        $this->arrangeColumn();

        $this->arrangeExecuteWithoutColumnMock();
        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->arrangeColumnSingleMock(false, false, false, true);

        $this->columnSingleMock
            ->method('getOrder')
            ->willReturn(false);
        $this->stubColumnSingleWithGetField('foo:bar');
        $this->columnSingleMock
            ->expects($this->any())
            ->method('getJoinColumns')
            ->willReturn(['']);

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('addOrderBy');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithColumnAndInnerJoin()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->arrangeColumnSingleMock(false, false, false, false);
        $this->stubColumnSingleWithGetField('foo.bar');
        $this->columnSingleMock
            ->method('getJoinType')
            ->willReturn('inner');

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('join');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithWhereCondition()
    {
        $this->arrangeExecute(1);

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('andWhere')
            ->with($this->compositeExpression);

        $this->initialise();

        $this->stubManagerWithGetRepository('foo');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithPageMoreThanZero()
    {
        $this->page = 10;

        $this->arrangeExecute();

        $this->initialise();

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setFirstResult')
            ->with($this->page * $this->limit);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock, $this->page);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithLimitMoreThanZero()
    {
        $this->page = 1;
        $this->limit = 10;
        $maxResults = 1;

        $this->arrangeExecute();

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setMaxResults')
            ->with($maxResults - $this->page * $this->limit);

        $this->initialise();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock, $this->page, $this->limit, $maxResults);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteSetMaxResultsWithMaxResults()
    {
        $this->page = 1;
        $this->limit = 0;
        $maxResults = 100;

        $this->arrangeExecute();

        $this->queryBuilderMock
            ->expects($this->atLeastOnce())
            ->method('setMaxResults')
            ->with($maxResults);

        $this->initialise();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock, $this->page, $this->limit, $maxResults);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithHints()
    {  
        $this->entity->addHint('foo', 'bar');

        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult([]);
        $this->queryMock
            ->expects($this->atLeastOnce())
            ->method('setHint')
            ->with('foo', 'bar');

        $this->arrangeExecuteWithoutQueryMock();
        $this->initialise();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithPrimayColumn()
    {  
        $this->arrangeExecute();
        $this->initialise();

        $this->columnSingleMock
            ->method('isPrimary')
            ->willReturn(true);
        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('getId');

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        $this->assertEquals(0, $result->count());
    }

    public function testExecuteWithResult()
    {  
        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult([
                ['foo' => serialize('bar')]
        ]);

        $this->arrangeExecuteWithoutQueryMock();
        $this->initialise();

        $this->stubColumnSingleWithGetType('array');
        $this->stubColumnSingleWithGetId('foo');

        $repositoryMock = $this->createMock(EntityRepository::class);

        $this->stubManagerWithGetRepository($repositoryMock);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $result = $this->entity->execute($this->columnMock);

        foreach ($result as $res) {
            $this->assertInstanceOf('\APY\DataGridBundle\Grid\Row', $res);
            $this->assertAttributeEquals(['foo' => 'bar'], 'fields', $res);
        }

        $this->assertEquals(1, $result->count());
    }

    public function testTotalCountWithoutHint()
    {
        $this->prepareQueryMock();

        $this->stubQueryWithGetScalarResult(10);

        $this->arrangeExecuteWithoutQueryMock();
        $this->initialise();

        $this->stubQueryBuilderWithGetRootAliases();
        $this->stubQueryBuilderWithGetQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);

        $countResult = $this->entity->getTotalCount();
        $this->assertEquals(10, $countResult);
    }

    public function testTotalCountWithHintsWalkerTrue()
    {
        $this->entity->addHint('foo', 'bar');

        $this->prepareQueryMock('callbackGetHintWalkerTrue');
        $this->arrangeExecuteWithoutQueryMock();
        $this->initialise();

        $this->stubQueryWithGetScalarResult(10);
        $this->stubQueryBuilderWithGetRootAliases();
        $this->stubQueryBuilderWithGetQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);

        $countResult = $this->entity->getTotalCount();

        $this->assertEquals(10, $countResult);
    }

    public function testTotalCountWithHintsWalkerFalse()
    {
        $this->entity->addHint('foo', 'bar');

        $this->prepareQueryMock('callbackGetHintWalkerFalse');
        $this->arrangeExecuteWithoutQueryMock();
        $this->initialise();

        $this->stubQueryWithGetScalarResult(10);
        $this->queryMock
            ->expects($this->atLeastOnce())
            ->method('setResultSetMapping');

        $this->stubQueryBuilderWithGetRootAliases();
        $this->stubQueryBuilderWithGetQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);

        $countResult = $this->entity->getTotalCount();

        $this->assertEquals(10, $countResult);
    }

    public function testTotalCountThrowExceptionReturnZero()
    {
        $this->prepareQueryMock();

        $this->queryMock 
            ->method('getScalarResult')
            ->will(
                $this->throwException(new NoResultException()
            )
        );

        $this->arrangeExecuteWithoutQueryMock();
        $this->initialise();

        $this->stubQueryBuilderWithGetRootAliases();
        $this->stubQueryBuilderWithGetQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);

        $countResult = $this->entity->getTotalCount();

        $this->assertEquals(0, $countResult);
    }

    public function callbackGetHintWalkerTrue($arg) {
        if ($arg == CountWalker::HINT_DISTINCT) {
            return false;
        }

        if ($arg == Query::HINT_CUSTOM_OUTPUT_WALKER) {
            return true;
        }

        if ($arg == Query::HINT_CUSTOM_TREE_WALKERS) {
            return false;
        }
    }

    public function callbackGetHintWalkerFalse($arg) {
        if ($arg == CountWalker::HINT_DISTINCT) {
            return false;
        }

        if ($arg == Query::HINT_CUSTOM_OUTPUT_WALKER) {
            return false;
        }

        if ($arg == Query::HINT_CUSTOM_TREE_WALKERS) {
            return false;
        }
    }

    public function testGetFieldsMetadata()
    {
        $this->initialise();
        $this->bindOrmMetaData('baz');

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithCompleteMappingArray()
    {
        $this->initialise();

        $this->stubOrmMetadataWithGetFieldNames('bar');
        $this->stubOrmMetadataWithGetFieldMapping([
            'type' => 'baz',
            'fieldName' => 'fieldName',
            'id' => 'id'
        ]);
    
        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'field' => 'fieldName',
                'id' => 'fieldName',
                'primary' => true
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithMappingTypeText()
    {
        $this->initialise();

        $type = 'text';
        $this->bindOrmMetaData($type);

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'type' => $type
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithMappingTypeDecimal()
    {
        $this->initialise();

        $type = 'decimal';
        $this->bindOrmMetaData($type);

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'type' => 'number'
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithMappingTypeBoolean()
    {
        $this->initialise();

        $type = 'boolean';
        $this->bindOrmMetaData($type);

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'type' => $type
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithMappingTypeDate()
    {
        $this->initialise();

        $type = 'date';
        $this->bindOrmMetaData($type);

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'type' => $type
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithMappingTypeDateTime()
    {
        $this->initialise();

        $type = 'datetime';
        $this->bindOrmMetaData($type);

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'type' => $type
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithMappingTypeTime()
    {
        $this->initialise();

        $type = 'time';
        $this->bindOrmMetaData($type);

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'type' => $type
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testGetFieldsMetadataWithMappingTypeObject()
    {
        $this->initialise();

        $type = 'object';
        $this->bindOrmMetaData($type);

        $expected = [
            'bar' => [
                'title' => 'bar',
                'source' => true,
                'type' => 'array'
            ]
        ];

        $metadata = $this->entity->getFieldsMetadata('foo');

        $this->assertEquals($expected, $metadata);
    }

    public function testPopulateSelectFiltersWithSelectFromSource()
    {
        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetType('array');
        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([]);

        $this->arrangeColumn();

        $this->arrangeExecuteWithoutColumnMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock);
    }

    public function testSelectFiltersWithSelectFromQueryConvertedToSourceIfOperatorIsCertainValue()
    {
        $this->stubFilterWithGetOperator(Column::OPERATOR_NEQ);

        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('query');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetFilters();

        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([]);

        $this->arrangeColumn();

        $this->arrangeExecuteWithoutColumnMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock);
    }

    public function testSelectFiltersWithSelectFromQueryWithHintsAndLoopFalse()
    {
        $this->entity->addHint('foo', 'bar');

        $this->stubFilterWithGetOperator('bar');

        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('query');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetFilters();

        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setSelectFrom')
            ->with('source');

        $this->arrangeColumn();

        $this->arrangeExecuteWithoutColumnMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock, false);
    }

    public function testSelectFiltersWithResultWithoutColumnTypeReturnDefaults()
    {
        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetId('bar');

        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([serialize('bar') => serialize('bar')]);

        $this->arrangeColumn();

        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult(['foo' => 
            [
                'bar' => serialize('bar')
            ]
        ]);

        $repositoryMock = $this->createMock(EntityRepository::class);

        $this->arrangeExecuteWithoutColumnMockAndQueryMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->stubManagerWithGetRepository($repositoryMock);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
        $this->entity->populateSelectFilters($this->columnMock, false);
    }

    public function testSelectFiltersWithResultWithArrayColumnType()
    {
        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetId('bar');
        $this->stubColumnSingleWithGetType('array');

        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with(['baz'=> 'baz']);

        $this->arrangeColumn();

        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult(['foo' => 
            [
                'bar' => serialize(['bar' => 'baz'])
            ]
        ]);

        $repositoryMock = $this->createMock(EntityRepository::class);

        $this->arrangeExecuteWithoutColumnMockAndQueryMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->stubManagerWithGetRepository($repositoryMock);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock, false);
    }

    public function testSelectFiltersWithResultWithSimpleArrayColumnType()
    {
        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetId('bar');
        $this->stubColumnSingleWithGetType('simple_array');

        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([serialize(['bar' => 'baz']) => serialize(['bar' => 'baz'])]);

        $this->arrangeColumn();

        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult(['foo' => 
            [
                'bar' => serialize(['bar' => 'baz'])
            ]
        ]);

        $repositoryMock = $this->createMock(EntityRepository::class);

        $this->arrangeExecuteWithoutColumnMockAndQueryMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->stubManagerWithGetRepository($repositoryMock);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
        $this->entity->populateSelectFilters($this->columnMock, false);
    }

    public function testSelectFiltersWithResultWithNumberColumnType()
    {
        $this->columnSingleMock = $this->createMock(NumberColumn::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetId('bar');
        $this->stubColumnSingleWithGetType('number');
        $this->stubColumnSingleWithGetDisplayedValue(1);
            
        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([serialize(['bar' => 'baz']) => 1]);

        $this->arrangeColumn();

        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult(['foo' => 
            [
                'bar' => serialize(['bar' => 'baz'])
            ]
        ]);

        $repositoryMock = $this->createMock(EntityRepository::class);

        $this->arrangeExecuteWithoutColumnMockAndQueryMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->stubManagerWithGetRepository($repositoryMock);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock, false);
    }

    public function testSelectFiltersWithResultWithTypeColumnType()
    {
        $this->columnSingleMock = $this->createMock(DateTimeColumn::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetId('bar');
        $this->stubColumnSingleWithGetType('time');
        $this->stubColumnSingleWithGetDisplayedValue(1);
            
        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([1 => 1]);

        $this->arrangeColumn();

        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult(['foo' => 
            [
                'bar' => serialize(['bar' => 'baz'])
            ]
        ]);

        $repositoryMock = $this->createMock(EntityRepository::class);

        $this->arrangeExecuteWithoutColumnMockAndQueryMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->stubManagerWithGetRepository($repositoryMock);

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock, false);
    }

    public function testPopulateSelectFiltersWithSelectFromSourceAndFieldNameWithDql()
    {
        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetType('array');
        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([]);
        $this->stubColumnSingleWithGetField('foo.bar');
        $this->stubColumnSingleWithHasDQLFunction('foo', 'bar', 'baz');

        $this->arrangeColumn();

        $this->arrangeExecuteWithoutColumnMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock);
    }


    public function testPopulateSelectFiltersWithSelectFromSourceAndFieldNameWithDqlAndDistinct()
    {
        $this->columnSingleMock = $this->createMock(Column::class);
        $this->stubColumnSingleWithGetSelectFrom('source');
        $this->stubColumnSingleWithGetFilterType('select');
        $this->stubColumnSingleWithGetType('array');
        $this->columnSingleMock
            ->expects($this->atLeastOnce())
            ->method('setValues')
            ->with([]);
        $this->stubColumnSingleWithGetField('foo:bar');
        $this->stubColumnSingleWithHasDQLFunction('distinct', 'bar', 'baz');
        
        $this->arrangeColumn();

        $this->arrangeExecuteWithoutColumnMock();
        $this->initialise();
        $this->queryBuilderMockDoQuery();

        $this->entity->initQueryBuilder($this->queryBuilderMock);
        $this->entity->execute($this->columnMock);
            
        $this->entity->populateSelectFilters($this->columnMock);
    }

    public function testPrepareCountQueryWithCallback()
    {
        $callback = function($val) { return true; };
        $this->entity->manipulateCountQuery($callback);
        
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->entity->prepareCountQuery($this->queryBuilderMock);

        $this->assertAttributeEquals($callback, 'prepareCountQueryCallback', $this->entity);
    }

    public function testDeleteObjectById()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubEntityRepositoryWithFind('foo');

        $this->stubManagerWithGetRepository($this->entityRepositoryMock);

        $this->managerMock
            ->expects($this->atLeastOnce())
            ->method('remove');

        $this->entity->delete([0]);
    }

    public function testDeleteObjectThrowExceptionIfNotIsAnObjectToDelete()
    {
        $this->arrangeExecute();
        $this->initialise();

        $this->stubEntityRepositoryWithFind([]);

        $this->stubManagerWithGetRepository($this->entityRepositoryMock);

        $this->expectException(\Exception::class);

        $this->entity->delete([0]);
    }

    public function testGetHash()
    {
        $this->entity = new Entity('entityName');

        $this->assertEquals('entityName', $this->entity->getHash());
    }

    public function testGetEntityName()
    {
        $this->entity = new Entity('entityName');

        $this->assertEquals('entityName', $this->entity->getEntityName());
    }

    public function testClearHints()
    {
        $this->entity->addHint('foo', 'bar');
        $this->assertAttributeEquals(['foo' => 'bar'], 'hints', $this->entity);

        $this->entity->clearHints();

        $this->assertAttributeEquals([], 'hints', $this->entity);
    }

    public function testSetGroupBy()
    {
        $this->entity = new Entity('entityName');
        $this->entity->setGroupBy('groupByField');

        $this->assertAttributeEquals('groupByField', 'groupBy', $this->entity);
    }

    private function queryBuilderMockDoQuery()
    {
        $this->queryBuilderMock 
            ->method('select')
            ->willReturn(
                $this->queryBuilderMock
        );

        $this->queryBuilderMock 
            ->method('distinct')
            ->willReturn(
                $this->queryBuilderMock
        );

        $this->queryBuilderMock 
            ->method('orderBy')
            ->willReturn(
                $this->queryBuilderMock
        );

        $this->queryBuilderMock 
            ->method('setFirstResult')
            ->willReturn(
                $this->queryBuilderMock
        );

        $this->queryBuilderMock 
            ->method('setMaxResults')
            ->willReturn(
                $this->queryBuilderMock
        );

        $this->stubQueryBuilderWithGetQuery();
    }

    private function bindOrmMetaData($type)
    {
        $this->stubOrmMetadataWithGetFieldNames('bar');
        $this->stubOrmMetadataWithGetFieldMapping(['type' => $type]);
    }

    private function initialiseObjectMock()
    {
        $this->objectMock = new \stdClass();
        $this->objectMock->name = 'foo';
    }

    private function initialiseOrmMetadata()
    {
        $this->ormMetadata = $this->createMock(ClassMetadataInfo::class);
        $this->ormMetadata
            ->method('getReflectionClass')
            ->willReturn($this->objectMock);
    }

    private function initialiseManager()
    {
        $this->managerMock = $this->createMock(EntityManagerInterface::class);
        $this->managerMock
            ->method('getClassMetadata')
            ->willReturn($this->ormMetadata);
    }

    private function initialiseDoctrine()
    {
        $doctrineMock = $this->createMock(ManagerRegistry::class);
        $doctrineMock
            ->method('getManager')
            ->willReturn($this->managerMock);

        $this->doctrineMock = $doctrineMock;
    }

    private function initialiseMetadata()
    {
        $this->metadataMock = $this->createMock(Metadata::class);
        $this->metadataMock
            ->method('getGroupBy')
            ->willReturn($this->groupBy);
    }

    private function initialiseMapping()
    {
        $mappingMock = $this->createMock(Manager::class);
        $mappingMock
            ->method('addDriver')
            ->willReturn('foo');
        $mappingMock
            ->method('getMetadata')
            ->willReturn($this->metadataMock);

        $this->mappingMock = $mappingMock;
    }

    private function initialiseContainer($self, $doctrineMock, $mappingMock)
    {
        $this->container = $this->createMock(Container::class);
        $this->container
            ->method('get')
            ->will($this->returnCallback(function ($param) use ($self, $doctrineMock, $mappingMock) {
                switch ($param) {
                    case 'doctrine':
                        return $doctrineMock;
                        break;
                    case 'grid.mapping.manager':
                        return $mappingMock;
                        break;
                }

            }));
    }

    private function initialiseEntity()
    {
        $this->entity->initialise($this->container);
    }

    private function initialise()
    {
        $self = $this;

        $this->initialiseObjectMock();
        $this->initialiseOrmMetadata();
        $this->initialiseManager();
        $this->initialiseDoctrine();
        $this->initialiseMetadata();
        $this->initialiseMapping();
        $this->initialiseContainer($self, $this->doctrineMock, $this->mappingMock);
        $this->initialiseEntity();
    }

    private function initialiseWithoutManager()
    {
        $self = $this;

        $this->initialiseObjectMock();
        $this->initialiseOrmMetadata();
        $this->initialiseDoctrine();
        $this->initialiseMetadata();
        $this->initialiseMapping();
        $this->initialiseContainer($self, $this->doctrineMock, $this->mappingMock);
        $this->initialiseEntity();
    }

    private function arrangeExecute($countWhere = 0)
    {
        $this->arrangeCompositeExpression($countWhere);
        $this->arrangeExpression();
        $this->arrangeQuery();
        $this->arrangeQueryBuilder();
        $this->arrangeColumnSingle();
        $this->arrangeColumn();
    }

    private function arrangeExecuteWithoutQueryMock($countWhere = 0)
    {
        $this->arrangeCompositeExpression($countWhere);
        $this->arrangeExpression();
        $this->arrangeQueryBuilder();
        $this->arrangeColumnSingle();
        $this->arrangeColumn();
    }

    private function arrangeExecuteWithoutColumnMock($countWhere = 0)
    {
        $this->arrangeCompositeExpression($countWhere);
        $this->arrangeExpression();
        $this->arrangeQuery();
        $this->arrangeQueryBuilder();
    }

    private function arrangeExecuteWithoutColumnMockAndQueryMock($countWhere = 0)
    {
        $this->arrangeCompositeExpression($countWhere);
        $this->arrangeExpression();
        $this->arrangeQueryBuilder();
    }

    private function arrangeCompositeExpression($countWhere = 0)
    {
        $this->compositeExpression = $this->createMock(CompositeExpression::class);
        $this->compositeExpression
            ->method('count')
            ->willReturn($countWhere);
    }

    private function arrangeExpression()
    {
        $this->expressionMock = $this->createMock(Expr::class);
        $this->expressionMock
            ->method('andx')
            ->willReturn($this->compositeExpression);
        $this->expressionMock
            ->method('orx')
            ->willReturn($this->compositeExpression);
    }

    private function arrangeQuery()
    {
        $this->queryMock = $this->createMock(AbstractQuery::class);
        $this->stubQueryWithGetResult([]);
    }

    private function arrangeQueryBuilder()
    {
        $this->queryBuilderMock = $this->createMock(QueryBuilder::class);
        $this->queryBuilderMock
            ->method('expr')
            ->willReturn($this->expressionMock);
        $this->stubQueryBuilderWithGetQuery();
    }

    private function arrangeColumnSingle()
    {
        $this->columnSingleMock = $this->createMock(Column::class);
    }

    private function arrangeColumn()
    {
        $this->columnMock = $this->createMock(Columns::class);
        $this->columnMock
            ->method('getIterator')
            ->willReturn(new \ArrayObject([$this->columnSingleMock]));
    }

    private function arrangeColumnSingleMock($isManualField, $isFiltered, $hasDqlFunction, $isSorted, $filterMock = null)
    {
        $this->columnSingleMock
            ->method('getIsManualField')
            ->willReturn($isManualField);
        $this->columnSingleMock
            ->method('isFiltered')
            ->willReturn($isFiltered);
        $this->columnSingleMock
            ->method('isSorted')
            ->willReturn($isSorted);
        $this->stubColumnSingleWithGetFilters();
        $this->columnSingleMock
            ->method('hasDQLFunction')
            ->willReturn($hasDqlFunction);
    }

    private function prepareQueryMock($hintWalker = 'callbackGetHintWalkerTrue')
    {
        $platformMock = $this->createMock(AbstractPlatform::class);
        $platformMock->method('getSQLResultCasing')
            ->willReturn('foo');

        $connectionMock = $this->createMock(Connection::class);
        $connectionMock
            ->method('getDatabasePlatform')
            ->willReturn($platformMock);

        $configurationMock = $this->createMock(Configuration::class);
        $configurationMock
            ->method('getDefaultQueryHints')
            ->willReturn('foo');

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock
            ->method('getConnection')
            ->willReturn($connectionMock);
        $entityManagerMock
            ->method('getConfiguration')
            ->willReturn($configurationMock);

        $this->queryMock = $this->createQueryMock($entityManagerMock);

        $this->queryMock
            ->method('getHint')
            ->with($this->logicalOr(
                 $this->equalTo(CountWalker::HINT_DISTINCT),
                 $this->equalTo(Query::HINT_CUSTOM_OUTPUT_WALKER),
                 $this->equalTo(Query::HINT_CUSTOM_TREE_WALKERS)
             ))
            ->will($this->returnCallback(array($this, $hintWalker)));
    }

    private function createQueryMock($em)
    {
        $originalQuery      = new Query($em);
        $allOriginalMethods = get_class_methods($originalQuery);

        $skipMethods = [
            '__construct',
            'staticProxyConstructor',
            '__get',
            '__set',
            '__isset',
            '__unset',
            '__clone',
            '__sleep',
            '__wakeup',
            'setProxyInitializer',
            'getProxyInitializer',
            'initializeProxy',
            'isProxyInitialized',
            'getWrappedValueHolderValue',
            'create',
        ];

        $originalMethods = [];
        foreach ($allOriginalMethods as $method) {
            if (!in_array($method, $skipMethods)) {
                $originalMethods[] = $method;
            }
        }

        $mock = $this
            ->getMockBuilder(\stdClass::class)
            ->disableOriginalConstructor()
            ->setMethods($originalMethods)
            ->getMock();

        $mock
            ->method('getEntityManager')
            ->willReturn($em);
        $mock 
            ->method('getResult')
            ->willReturn([]);
        $mock 
            ->method('setFirstResult')
            ->willReturn($mock);

        return $mock;
    }

    private function stubQueryBuilderWithGetRootAliases()
    {
        $this->queryBuilderMock
            ->method('getRootAliases')
            ->willReturn(['foo']);
    }

    private function stubManagerWithGetRepository($repository)
    {
        $this->managerMock
            ->method('getRepository')
            ->willReturn($repository);
    }

    private function stubFilterWithGetOperator($operator)
    {
        $this->filterMock = $this->createMock(Filter::class);
        $this->filterMock
            ->method('getOperator')
            ->willReturn($operator);
    }

    private function stubFilterGetValue($willReturnValue)
    {
        $this->filterMock
            ->method('getValue')
            ->willReturn($willReturnValue);
    }

    private function stubColumnSingleWithGetField($willReturnValue)
    {
        $this->columnSingleMock
            ->method('getField')
            ->willReturn($willReturnValue);
    }

    private function stubColumnSingleWithHasDQLFunction($parameters, $function, $field)
    {
        $this->columnSingleMock
            ->method('hasDQLFunction')
            ->with([])
            ->willReturnCallback(function(&$matches) use ($parameters, $function, $field){
                $matches =  [
                    'parameters' => $parameters,
                    'function' => $function,
                    'field' => $field
                ];
                return true;
            });
    }

    private function stubQueryWithGetResult($willReturnValue)
    {
        $this->queryMock
            ->method('getResult')
            ->willReturn($willReturnValue);
    }

    private function stubColumnSingleWithGetType($willReturnValue)
    {
        $this->columnSingleMock
            ->method('getType')
            ->willReturn($willReturnValue);
    }

    private function stubColumnSingleWithGetId($willReturnValue)
    {
        $this->columnSingleMock
            ->method('getId')
            ->willReturn($willReturnValue);
    }

    private function stubQueryWithGetScalarResult($willReturnValue)
    {
        $this->queryMock 
            ->method('getScalarResult')
            ->willReturn([
                [$willReturnValue]
        ]);
    }

    private function stubQueryBuilderWithGetQuery()
    {
        $this->queryBuilderMock
            ->method('getQuery')
            ->willReturn($this->queryMock);
    }

    private function stubOrmMetadataWithGetFieldNames($willReturnValue)
    {
        $this->ormMetadata
            ->method('getFieldNames')
            ->willReturn([$willReturnValue]);
    }

    private function stubOrmMetadataWithGetFieldMapping($willReturnValue)
    {
        $this->ormMetadata
            ->method('getFieldMapping')
            ->willReturn($willReturnValue);
    }

    private function stubColumnSingleWithGetSelectFrom($willReturnValue)
    {
        $this->columnSingleMock
            ->method('getSelectFrom')
            ->willReturn($willReturnValue);
    }

    private function stubColumnSingleWithGetFilterType($willReturnValue)
    {
        $this->columnSingleMock
            ->method('getFilterType')
            ->willReturn($willReturnValue);
    }

    private function stubColumnSingleWithGetFilters()
    {
        $this->columnSingleMock
            ->method('getFilters')
            ->willReturn([$this->filterMock]);
    }

    private function stubColumnSingleWithGetDisplayedValue($willReturnValue)
    {
        $this->columnSingleMock
            ->method('getDisplayedValue')
            ->willReturn($willReturnValue);
    }

    private function stubEntityRepositoryWithFind($willReturnValue)
    {
        $this->entityRepositoryMock = $this->createMock(EntityRepository::class);
        $this->entityRepositoryMock
            ->method('find')
            ->willReturn($willReturnValue);
    }

    public function setUp()
    {
        $this->managerName = $this->createMock(EntityManager::class);
        $this->managerName
            ->method('getClassMetadata')
            ->willReturn('Cms:Page');

        $this->entity = new Entity($this->entityName, $this->group, $this->managerName);
    }
}
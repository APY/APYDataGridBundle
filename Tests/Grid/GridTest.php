<?php

namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Action\MassAction;
use APY\DataGridBundle\Grid\Action\MassActionInterface;
use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Action\RowActionInterface;
use APY\DataGridBundle\Grid\Column\ActionsColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\MassActionColumn;
use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Export\Export;
use APY\DataGridBundle\Grid\Export\ExportInterface;
use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\GridConfigInterface;
use APY\DataGridBundle\Grid\Helper\ColumnsIterator;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Rows;
use APY\DataGridBundle\Grid\Source\Entity;
use APY\DataGridBundle\Grid\Source\Source;
use APY\DataGridBundle\Tests\PhpunitTrait;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Template;

class GridTest extends TestCase
{
    use PhpunitTrait;

    private Grid $grid;
    private MockObject|RouterInterface $router;
    private MockObject|AuthorizationCheckerInterface $authChecker;
    private MockObject|RequestStack $requestStack;
    private MockObject|Request $request;
    private MockObject|SessionInterface $session;
    private MockObject|Environment $engine;
    private ?string $gridId = null;
    private ?string $gridHash = null;

    public function testInitializeWithoutAnyConfiguration(): void
    {
        $this->arrange();

        $column = $this->stubColumn();
        $this->grid->addColumn($column);

        $this->grid->initialize();

        $this->assertFalse($this->grid->getPersistence());
        $this->assertEmpty($this->grid->getRouteParameters());
        $this->assertEmpty($this->grid->getRouteUrl());
        $this->assertEmpty($this->grid->getSource());
        $this->assertEmpty($this->grid->getLimits());
        $this->assertEmpty($this->grid->getPage());

        $this->router->expects($this->never())->method($this->anything());
        $column->expects($this->never())->method($this->anything());
    }

    public function testInitializePersistence(): void
    {
        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('isPersisted')
            ->willReturn(true);

        $this->arrange($gridConfig);

        $this->grid->initialize();

        $this->assertTrue($this->grid->getPersistence());
    }

    public function testInitializeRouteParams(): void
    {
        $routeParams = ['foo' => 1, 'bar' => 2];

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getRouteParameters')
            ->willReturn($routeParams);

        $this->arrange($gridConfig);

        $this->grid->initialize();

        $this->assertEquals($routeParams, $this->grid->getRouteParameters());
    }

    public function testInitializeRouteUrlWithoutParams(): void
    {
        $route = 'vendor.bundle.controller.route_name';
        $routeParams = ['foo' => 1, 'bar' => 2];
        $url = 'aRandomUrl';

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getRouteParameters')
            ->willReturn($routeParams);
        $gridConfig
            ->method('getRoute')
            ->willReturn($route);

        $this->arrange($gridConfig);

        $this
            ->router
            ->method('generate')
            ->with($route, $routeParams)
            ->willReturn($url);

        $this->grid->initialize();

        $this->assertEquals($url, $this->grid->getRouteUrl());
    }

    public function testInitializeRouteUrlWithParams(): void
    {
        $route = 'vendor.bundle.controller.route_name';
        $url = 'aRandomUrl';

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getRoute')
            ->willReturn($route);

        $this->arrange($gridConfig);
        $this
            ->router
            ->method('generate')
            ->with($route, [])
            ->willReturn($url);

        $this->grid->initialize();

        $this->assertEquals($url, $this->grid->getRouteUrl());
    }

    public function testInizializeColumnsNotFilterableAsGridIsNotFilterable(): void
    {
        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('isFilterable')
            ->willReturn(false);

        $column = $this->stubColumn();

        $this->arrange($gridConfig);
        $this->grid->addColumn($column);

        $column
            ->expects($this->atLeastOnce())
            ->method('setFilterable')
            ->with(false);

        $this->grid->initialize();
    }

    public function testInizializeColumnsNotSortableAsGridIsNotSortable(): void
    {
        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('isSortable')
            ->willReturn(false);

        $column = $this->stubColumn();

        $this->arrange($gridConfig);
        $this->grid->addColumn($column);

        $column
            ->expects($this->atLeastOnce())
            ->method('setSortable')
            ->with(false);

        $this->grid->initialize();
    }

    public function testInitializeNotEntitySource(): void
    {
        $source = $this->createMock(Source::class);

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getSource')
            ->willReturn($source);

        $this->arrange($gridConfig);

        $source
            ->expects($this->once())
            ->method('initialise');

        $this->grid->initialize();
    }

    public function testInitializeEntitySourceWithoutGroupByFunction(): void
    {
        $source = $this->createMock(Entity::class);

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getSource')
            ->willReturn($source);

        $this->arrange($gridConfig);

        $source
            ->expects($this->once())
            ->method('initialise');
        $source
            ->expects($this->never())
            ->method('setGroupBy');

        $this->grid->initialize();
    }

    public function testInitializeEntitySourceWithoutGroupByScalarValue(): void
    {
        $groupByField = 'groupBy';

        $source = $this->createMock(Entity::class);

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getSource')
            ->willReturn($source);
        $gridConfig
            ->method('getGroupBy')
            ->willReturn($groupByField);

        $this->arrange($gridConfig);

        $source
            ->expects($this->once())
            ->method('initialise');
        $source
            ->expects($this->atLeastOnce())
            ->method('setGroupBy')
            ->with([$groupByField]);

        $this->grid->initialize();
    }

    public function testInitializeEntitySourceWithoutGroupByArrayValues(): void
    {
        $groupByArray = ['groupByFoo', 'groupByBar'];

        $source = $this->createMock(Entity::class);

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getSource')
            ->willReturn($source);
        $gridConfig
            ->method('getGroupBy')
            ->willReturn($groupByArray);

        $this->arrange($gridConfig);

        $source
            ->expects($this->once())
            ->method('initialise');
        $source
            ->expects($this->atLeastOnce())
            ->method('setGroupBy')
            ->with($groupByArray);

        $this->grid->initialize();
    }

    public function testInizializeDefaultOrder(): void
    {
        $sortBy = 'SORTBY';
        $orderBy = 'ORDERBY';

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getSortBy')
            ->willReturn($sortBy);
        $gridConfig
            ->method('getOrder')
            ->willReturn($orderBy);

        $this->arrange($gridConfig);

        $this->grid->initialize();

        $this->assertEquals(\sprintf('%s|%s', $sortBy, \strtolower($orderBy)), $this->grid->getDefaultOrder());
    }

    public function testInizializeDefaultOrderWithoutOrder(): void
    {
        $sortBy = 'SORTBY';

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getSortBy')
            ->willReturn($sortBy);

        $this->arrange($gridConfig);

        $this->grid->initialize();

        // @todo: is this an admitted case?
        $this->assertEquals("$sortBy|", $this->grid->getDefaultOrder());
    }

    public function testInizializeLimits(): void
    {
        $maxPerPage = 10;

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getMaxPerPage')
            ->willReturn($maxPerPage);

        $this->arrange($gridConfig);

        $this->grid->initialize();

        $this->assertEquals([$maxPerPage => (string) $maxPerPage], $this->grid->getLimits());
    }

    public function testInizializeMaxResults(): void
    {
        $maxResults = 50;

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getMaxResults')
            ->willReturn($maxResults);

        $this->arrange($gridConfig);

        $this->grid->initialize();

        $this->assertEquals($maxResults, $this->grid->getMaxResults());
    }

    public function testInizializePage(): void
    {
        $page = 1;

        $gridConfig = $this->createMock(GridConfigInterface::class);
        $gridConfig
            ->method('getPage')
            ->willReturn($page);

        $this->arrange($gridConfig);

        $this->grid->initialize();

        $this->assertEquals($page, $this->grid->getPage());
    }

    public function testSetSourceOneThanOneTime(): void
    {
        $source = $this->createMock(Source::class);

        // @todo maybe this exception should not be \InvalidArgumentException?
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::SOURCE_ALREADY_SETTED_EX_MSG);

        $this->grid->setSource($source);
        $this->grid->setSource($source);
    }

    public function testSetSource(): void
    {
        $source = $this->createMock(Source::class);

        $source
            ->expects($this->once())
            ->method('initialise');
        $source
            ->expects($this->once())
            ->method('getColumns')
            ->with($this->isInstanceOf(Columns::class));

        $this->grid->setSource($source);

        $this->assertEquals($source, $this->grid->getSource());
    }

    public function testGetSource(): void
    {
        $source = $this->createMock(Source::class);

        $this->grid->setSource($source);

        $this->assertEquals($source, $this->grid->getSource());
    }

    public function testGetNullHashIfNotCreated(): void
    {
        $this->assertNull($this->grid->getHash());
    }

    public function testHandleRequestRaiseExceptionIfSourceNotSetted(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(Grid::SOURCE_NOT_SETTED_EX_MSG);

        $this->grid->handleRequest(
            $this->getMockBuilder(Request::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
    }

    public function testAddColumnToLazyColumnsWithoutPosition(): void
    {
        $column = $this->stubColumn();
        $this->grid->addColumn($column);

        $this->assertEquals([['column' => $column, 'position' => 0]], $this->grid->getLazyAddColumn());
    }

    public function testAddColumnToLazyColumnsWithPosition(): void
    {
        $column = $this->stubColumn();
        $this->grid->addColumn($column, 1);

        $this->assertEquals([['column' => $column, 'position' => 1]], $this->grid->getLazyAddColumn());
    }

    public function testAddColumnsToLazyColumnsWithSamePosition(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubColumn();

        $this->grid->addColumn($column1, 1);
        $this->grid->addColumn($column2, 1);

        $this->assertEquals([
            ['column' => $column1, 'position' => 1],
            ['column' => $column2, 'position' => 1], ],
            $this->grid->getLazyAddColumn()
        );
    }

    public function testGetColumnFromLazyColumns(): void
    {
        $columnId = 'foo';
        $column = $this->stubColumn($columnId);

        $this->grid->addColumn($column);

        $this->assertEquals($column, $this->grid->getColumn($columnId));
    }

    public function testGetColumnFromColumns(): void
    {
        $columnId = 'foo';
        $column = $this->stubColumn();

        $columns = $this->createMock(Columns::class);
        $columns
            ->method('getColumnById')
            ->with($columnId)
            ->willReturn($column);

        $this->grid->setColumns($columns);

        $this->assertEquals($column, $this->grid->getColumn($columnId));
    }

    public function testRaiseExceptionIfGetNonExistentColumn(): void
    {
        $columnId = 'foo';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Columns::MISSING_COLUMN_EX_MSG, $columnId));

        $this->grid->getColumn($columnId);
    }

    public function testGetColumns(): void
    {
        $this->assertInstanceOf(Columns::class, $this->grid->getColumns());
    }

    public function testHasColumnInLazyColumns(): void
    {
        $columnId = 'foo';
        $column = $this->stubColumn($columnId);
        $this->grid->addColumn($column);

        $this->assertTrue($this->grid->hasColumn($columnId));
    }

    public function testHasColumnInColumns(): void
    {
        $columnId = 'foo';

        $columns = $this->createMock(Columns::class);
        $columns
            ->method('hasColumnById')
            ->with($columnId)
            ->willReturn(true);

        $this->grid->setColumns($columns);

        $this->assertTrue($this->grid->hasColumn($columnId));
    }

    public function testSetColumns(): void
    {
        $columns = $this->createMock(Columns::class);
        $this->grid->setColumns($columns);

        $this->assertEquals($columns, $this->grid->getColumns());
    }

    public function testColumnsReorderAndKeepOtherColumns(): void
    {
        $ids = ['col1', 'col3', 'col2'];

        $columns = $this->createMock(Columns::class);
        $columns
            ->expects($this->once())
            ->method('setColumnsOrder')
            ->with($ids, true);

        $this->grid->setColumns($columns);

        $this->grid->setColumnsOrder($ids, true);
    }

    public function testColumnsReorderAndDontKeepOtherColumns(): void
    {
        $ids = ['col1', 'col3', 'col2'];

        $columns = $this->createMock(Columns::class);
        $columns
            ->expects($this->once())
            ->method('setColumnsOrder')
            ->with($ids, false);

        $this->grid->setColumns($columns);

        $this->grid->setColumnsOrder($ids, false);
    }

    public function testAddMassActionWithoutRole(): void
    {
        $massAction = $this->stubMassAction();
        $this->grid->addMassAction($massAction);

        $this->assertEquals([$massAction], $this->grid->getMassActions());
    }

    public function testAddMassActionWithGrantForActionRole(): void
    {
        $role = 'aRole';
        $massAction = $this->stubMassAction($role);

        $this
            ->authChecker
            ->method('isGranted')
            ->with($role)
            ->willReturn(true);

        $this->grid->addMassAction($massAction);

        $this->assertEquals([$massAction], $this->grid->getMassActions());
    }

    public function testAddMassActionWithoutGrantForActionRole(): void
    {
        $role = 'aRole';
        $massAction = $this->stubMassAction($role);

        $this
            ->authChecker
            ->method('isGranted')
            ->with($role)
            ->willReturn(false);

        $this->grid->addMassAction($massAction);

        $this->assertEmpty($this->grid->getMassActions());
    }

    public function testGetMassActions(): void
    {
        $massAction = $this->stubMassAction();
        $this->grid->addMassAction($massAction);

        $this->assertEquals([$massAction], $this->grid->getMassActions());
    }

    public function testRaiseExceptionIfAddTweakWithNotValidId(): void
    {
        $tweakId = '#tweakNotValidId';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Grid::TWEAK_MALFORMED_ID_EX_MSG, $tweakId));

        $this->grid->addTweak('title', [], $tweakId);
    }

    public function testAddTweakWithId(): void
    {
        $title = 'aTweak';
        $id = 'aValidTweakId';
        $tweak = ['url' => '?[__tweak_id]='.$id, 'filters' => [], 'order' => 'columnId', 'page' => 1, 'limit' => 50, 'export' => 1, 'massAction' => 1];
        $group = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $id, $group);

        $result = [$id => \array_merge(['title' => $title, 'id' => $id, 'group' => $group], $tweak)];

        $this->assertEquals($result, $this->grid->getTweaks());
    }

    public function testAddTweakWithoutId(): void
    {
        $title = 'aTweak';
        $tweak = ['url' => '?[__tweak_id]=0', 'filters' => [], 'order' => 'columnId', 'page' => 1, 'limit' => 50, 'export' => 1, 'massAction' => 1];
        $group = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, null, $group);

        $result = [0 => \array_merge(['title' => $title, 'id' => null, 'group' => $group], $tweak)];

        $this->assertEquals($result, $this->grid->getTweaks());
    }

    public function testAddRowActionWithoutRole(): void
    {
        $colId = 'aColId';
        $rowAction = $this->stubRowAction(null, $colId);
        $this->grid->addRowAction($rowAction);

        $this->assertEquals([$colId => [$rowAction]], $this->grid->getRowActions());
    }

    public function testAddRowActionWithGrantForActionRole(): void
    {
        $role = 'aRole';
        $colId = 'aColId';
        $rowAction = $this->stubRowAction($role, $colId);

        $this
            ->authChecker
            ->method('isGranted')
            ->with($role)
            ->willReturn(true);

        $this->grid->addRowAction($rowAction);

        $this->assertEquals([$colId => [$rowAction]], $this->grid->getRowActions());
    }

    public function testAddRowActionWithoutGrantForActionRole(): void
    {
        $role = 'aRole';
        $rowAction = $this->stubRowAction($role);

        $this
            ->authChecker
            ->method('isGranted')
            ->with($role)
            ->willReturn(false);

        $this->grid->addRowAction($rowAction);

        $this->assertEmpty($this->grid->getRowActions());
    }

    public function testGetRowActions(): void
    {
        $colId = 'aColId';
        $rowAction = $this->stubRowAction(null, $colId);
        $this->grid->addRowAction($rowAction);

        $this->assertEquals([$colId => [$rowAction]], $this->grid->getRowActions());
    }

    public function testSetExportTwigTemplateInstance(): void
    {
        $templateName = 'templateName';

        $template = $this
            ->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();
        $template
            ->method('getTemplateName')
            ->willReturn($templateName);

        $result = '__SELF__'.$templateName;

        $this
            ->session
            ->expects($this->once())
            ->method('set')
            ->with($this->anything(), [Grid::REQUEST_QUERY_TEMPLATE => $result]);

        $this->grid->setTemplate($template);
    }

    public function testSetExportStringTemplate(): void
    {
        $template = 'templateString';

        $this
            ->session
            ->expects($this->once())
            ->method('set')
            ->with($this->anything(), [Grid::REQUEST_QUERY_TEMPLATE => $template]);

        $this->grid->setTemplate($template);
    }

    public function testSetExportNullTemplate(): void
    {
        $this
            ->session
            ->expects($this->never())
            ->method('set')
            ->with($this->anything(), $this->anything());

        $this->grid->setTemplate(null);
    }

    public function testReturnTwigTemplate(): void
    {
        $templateName = 'templateName';

        $template = $this
            ->getMockBuilder(Template::class)
            ->disableOriginalConstructor()
            ->getMock();
        $template
            ->method('getTemplateName')
            ->willReturn($templateName);

        $result = '__SELF__'.$templateName;

        $this->grid->setTemplate($template);

        $this->assertEquals($result, $this->grid->getTemplate());
    }

    public function testReturnStringTemplate(): void
    {
        $template = 'templateString';

        $this->grid->setTemplate($template);

        $this->assertEquals($template, $this->grid->getTemplate());
    }

    public function testAddExportWithoutRole(): void
    {
        $export = $this->createMock(ExportInterface::class);
        $export
            ->method('getRole')
            ->willReturn(null);

        $this->grid->addExport($export);

        $this->assertEquals([$export], $this->grid->getExports());
    }

    public function testAddExportWithGrantForActionRole(): void
    {
        $role = 'aRole';

        $export = $this->createMock(ExportInterface::class);
        $export
            ->method('getRole')
            ->willReturn($role);

        $this
            ->authChecker
            ->method('isGranted')
            ->with($role)
            ->willReturn(true);

        $this->grid->addExport($export);

        $this->assertEquals([$export], $this->grid->getExports());
    }

    public function testAddExportWithoutGrantForActionRole(): void
    {
        $role = 'aRole';

        $export = $this->createMock(ExportInterface::class);
        $export
            ->method('getRole')
            ->willReturn($role);

        $this
            ->authChecker
            ->method('isGranted')
            ->with($role)
            ->willReturn(false);

        $this->grid->addExport($export);

        $this->assertEmpty($this->grid->getExports());
    }

    public function testGetExports(): void
    {
        $export = $this->createMock(ExportInterface::class);
        $export
            ->method('getRole')
            ->willReturn(null);

        $this->grid->addExport($export);

        $this->assertEquals([$export], $this->grid->getExports());
    }

    public function testSetRouteParameter(): void
    {
        $paramName = 'name';
        $paramValue = 'value';

        $otherParamName = 'name';
        $otherParamValue = 'value';

        $this->grid->setRouteParameter($paramName, $paramValue);
        $this->grid->setRouteParameter($otherParamName, $otherParamValue);

        $this->assertEquals(
            [$paramName => $paramValue, $otherParamName => $otherParamValue],
            $this->grid->getRouteParameters()
        );
    }

    public function testGetRouteParameters(): void
    {
        $paramName = 'name';
        $paramValue = 'value';

        $otherParamName = 'name';
        $otherParamValue = 'value';

        $this->grid->setRouteParameter($paramName, $paramValue);
        $this->grid->setRouteParameter($otherParamName, $otherParamValue);

        $this->assertEquals(
            [$paramName => $paramValue, $otherParamName => $otherParamValue],
            $this->grid->getRouteParameters()
        );
    }

    public function testGetRouteUrl(): void
    {
        $url = 'url';

        $this->grid->setRouteUrl($url);

        $this->assertEquals($url, $this->grid->getRouteUrl());
    }

    public function testGetRouteUrlFromRequest(): void
    {
        $url = 'url';

        $this
            ->request
            ->method('get')
            ->with('_route')
            ->willReturn($url);

        $this
            ->router
            ->method('generate')
            ->with($url, $this->anything())
            ->willReturn($url);

        $this->assertEquals($url, $this->grid->getRouteUrl());
    }

    public function testGetId(): void
    {
        $id = 'id';
        $this->grid->setId($id);

        $this->assertEquals($id, $this->grid->getId());
    }

    public function testGetPersistence(): void
    {
        $this->grid->setPersistence(true);

        $this->assertTrue($this->grid->getPersistence());
    }

    public function testGetDataJunction(): void
    {
        $this->grid->setDataJunction(Column::DATA_DISJUNCTION);

        $this->assertEquals(Column::DATA_DISJUNCTION, $this->grid->getDataJunction());
    }

    public function testSetInvalidLimitsRaiseException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::NOT_VALID_LIMIT_EX_MSG);

        $this->grid->setLimits('foo');
    }

    public function testGetLimits(): void
    {
        $limits = [10, 50, 100];
        $this->grid->setLimits($limits);

        $this->assertEquals(\array_combine($limits, $limits), $this->grid->getLimits());
    }

    public function testSetDefaultPage(): void
    {
        $page = 1;
        $this->grid->setDefaultPage($page);

        $this->assertEquals($page - 1, $this->grid->getPage());
    }

    public function testSetDefaultTweak(): void
    {
        $tweakId = 1;
        $this->grid->setDefaultTweak($tweakId);

        $this->assertEquals($tweakId, $this->grid->getDefaultTweak());
    }

    public function testSetPageWithInvalidValueRaiseException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::NOT_VALID_PAGE_NUMBER_EX_MSG);

        $page = '-1';
        $this->grid->setPage($page);
    }

    public function testSetPageWithZeroValue(): void
    {
        $page = 0;
        $this->grid->setPage($page);

        $this->assertEquals($page, $this->grid->getPage());
    }

    public function testGetPage(): void
    {
        $page = 10;
        $this->grid->setPage($page);

        $this->assertEquals($page, $this->grid->getPage());
    }

    public function testSetMaxResultWithNullValue(): void
    {
        $this->grid->setMaxResults();
        $this->assertNull($this->grid->getMaxResults());
    }

    public function testSetMaxResultWithInvalidValueRaiseException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::NOT_VALID_MAX_RESULT_EX_MSG);

        $this->grid->setMaxResults(-1);
    }

    public function testSetMaxResult(): void
    {
        $maxResult = 1;
        $this->grid->setMaxResults($maxResult);

        $this->assertEquals($maxResult, $this->grid->getMaxResults());
    }

    public function testIsNotFilteredIfNoColumnIsFiltered(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column1);
        $columns->addColumn($column2);

        $this->grid->setColumns($columns);

        $this->assertFalse($this->grid->isFiltered());
    }

    public function testIsFilteredIfAtLeastAColumnIsFiltered(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubFilteredColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column1);
        $columns->addColumn($column2);

        $this->grid->setColumns($columns);

        $this->assertTrue($this->grid->isFiltered());
    }

    public function testShowTitlesIfAtLeastOneColumnHasATitle(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubTitledColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column1);
        $columns->addColumn($column2);

        $this->grid->setColumns($columns);

        $this->assertTrue($this->grid->isTitleSectionVisible());
    }

    public function testDontShowTitlesIfNoColumnsHasATitle(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column1);
        $columns->addColumn($column2);

        $this->grid->setColumns($columns);

        $this->assertFalse($this->grid->isTitleSectionVisible());
    }

    public function testDontShowTitles(): void
    {
        $column = $this->stubTitledColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);

        $this->grid->setColumns($columns);

        $this->grid->hideTitles();
        $this->assertFalse($this->grid->isTitleSectionVisible());
    }

    public function testShowFilterSectionIfAtLeastOneColumnFilterable(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubFilterableColumn('text');

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column1);
        $columns->addColumn($column2);

        $this->grid->setColumns($columns);

        $this->assertTrue($this->grid->isFilterSectionVisible());
    }

    public function testDontShowFilterSectionIfColumnVisibleTypeIsMassAction(): void
    {
        $column = $this->stubFilterableColumn('massaction');

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);

        $this->grid->setColumns($columns);

        $this->assertFalse($this->grid->isFilterSectionVisible());
    }

    public function testDontShowFilterSectionIfColumnVisibleTypeIsActions(): void
    {
        $column = $this->stubFilterableColumn('actions');

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);

        $this->grid->setColumns($columns);

        $this->assertFalse($this->grid->isFilterSectionVisible());
    }

    public function testDontShowFilterSectionIfNoColumnFilterable(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column1);
        $columns->addColumn($column2);

        $this->grid->setColumns($columns);

        $this->assertFalse($this->grid->isFilterSectionVisible());
    }

    public function testDontShowFilterSection(): void
    {
        $this->grid->hideFilters();

        $this->assertFalse($this->grid->isFilterSectionVisible());
    }

    public function testHideFilters(): void
    {
        $this->grid->hideFilters();

        $this->assertFalse($this->grid->isShowFilters());
    }

    public function testHideTitles(): void
    {
        $this->grid->hideTitles();

        $this->assertFalse($this->grid->isShowTitles());
    }

    public function testAddsColumnExtension(): void
    {
        $extension = $this->stubColumn();

        $columns = $this
            ->getMockBuilder(Columns::class)
            ->disableOriginalConstructor()
            ->getMock();
        $columns
            ->expects($this->once())
            ->method('addExtension')
            ->with($extension);

        $this->grid->setColumns($columns);

        $this->grid->addColumnExtension($extension);
    }

    public function testSetPrefixTitle(): void
    {
        $prefixTitle = 'prefixTitle';
        $this->grid->setPrefixTitle($prefixTitle);

        $this->assertEquals($prefixTitle, $this->grid->getPrefixTitle());
    }

    public function testGetPrefixTitle(): void
    {
        $prefixTitle = 'prefixTitle';
        $this->grid->setPrefixTitle($prefixTitle);

        $this->assertEquals($prefixTitle, $this->grid->getPrefixTitle());
    }

    public function testGetNoDataMessage(): void
    {
        $message = 'foo';
        $this->grid->setNoDataMessage($message);

        $this->assertEquals($message, $this->grid->getNoDataMessage());
    }

    public function testGetNoResultMessage(): void
    {
        $message = 'foo';
        $this->grid->setNoResultMessage($message);

        $this->assertEquals($message, $this->grid->getNoResultMessage());
    }

    public function testSetHiddenColumnsWithIntegerId(): void
    {
        $id = 1;
        $this->grid->setHiddenColumns($id);

        $this->assertEquals([$id], $this->grid->getLazyHiddenColumns());
    }

    public function testSetHiddenColumnWithArrayOfIds(): void
    {
        $ids = [1, 2, 3];
        $this->grid->setHiddenColumns($ids);

        $this->assertEquals($ids, $this->grid->getLazyHiddenColumns());
    }

    public function testSetVisibleColumnsWithIntegerId(): void
    {
        $id = 1;
        $this->grid->setVisibleColumns($id);

        $this->assertEquals([$id], $this->grid->getLazyVisibleColumns());
    }

    public function testSetVisibleColumnWithArrayOfIds(): void
    {
        $ids = [1, 2, 3];
        $this->grid->setVisibleColumns($ids);

        $this->assertEquals($ids, $this->grid->getLazyVisibleColumns());
    }

    public function testShowColumnsWithIntegerId(): void
    {
        $id = 1;
        $this->grid->showColumns($id);

        $this->assertEquals([$id => true], $this->grid->getLazyHideShowColumns());
    }

    public function testShowColumnsArrayOfIds(): void
    {
        $ids = [1, 2, 3];
        $this->grid->showColumns($ids);

        $this->assertEquals([1 => true, 2 => true, 3 => true], $this->grid->getLazyHideShowColumns());
    }

    public function testHideColumnsWithIntegerId(): void
    {
        $id = 1;
        $this->grid->hideColumns($id);

        $this->assertEquals([$id => false], $this->grid->getLazyHideShowColumns());
    }

    public function testHideColumnsArrayOfIds(): void
    {
        $ids = [1, 2, 3];
        $this->grid->hideColumns($ids);

        $this->assertEquals([1 => false, 2 => false, 3 => false], $this->grid->getLazyHideShowColumns());
    }

    public function testSetActionsColumnSize(): void
    {
        $size = 2;
        $this->grid->setActionsColumnSize($size);

        $this->assertEquals($size, $this->grid->getActionsColumnSize());
    }

    public function testSetActionsColumnTitle(): void
    {
        $title = 'aTitle';
        $this->grid->setActionsColumnTitle($title);

        $this->assertEquals($title, $this->grid->getActionsColumnTitle());
    }

    public function testClone(): void
    {
        $column1 = $this->stubColumn();
        $column2 = $this->stubColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column1);
        $columns->addColumn($column2);

        $this->grid->setColumns($columns);
        $grid = clone $this->grid;

        $this->assertNotSame($columns, $grid->getColumns());
    }

    public function testRaiseExceptionDuringHandleRequestIfNoSourceSetted(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(Grid::SOURCE_NOT_SETTED_EX_MSG);

        $request = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->grid->handleRequest($request);
    }

    public function testCreateHashWithIdDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->grid->handleRequest($this->request);

        $this->assertEquals($this->gridHash, $this->grid->getHash());
    }

    public function testCreateHashWithMd5DuringHandleRequest(): void
    {
        $this->arrange($this->createMock(GridConfigInterface::class), null);

        $sourceHash = '4f403d7e887f7d443360504a01aaa30e';

        $this->arrangeGridSourceDataLoadedWithEmptyRows(0, $sourceHash);

        $column = $this->stubPrimaryColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);
        $this->grid->setColumns($columns);

        $controller = 'aController';

        $this
            ->request
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($controller);

        $this->grid->handleRequest($this->request);

        $this->assertEquals('grid_'.\md5($controller.$columns->getHash().$sourceHash), $this->grid->getHash());
    }

    public function testResetGridSessionWhenChangeGridDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->request->headers = $this->createMock(HeaderBag::class);
        $this->request->headers->method('get')->with('referer')->willReturn('previousGrid');

        $this
            ->session
            ->expects($this->once())
            ->method('remove')
            ->with($this->gridHash);

        $this->grid->handleRequest($this->request);
    }

    public function testResetGridSessionWhenResetFiltersIsPressedDuringHandleRequest(): void
    {
        $this->mockResetGridSessionWhenResetFilterIsPressed();

        $this->grid->handleRequest($this->request);
    }

    public function testNotResetGridSessionWhenXmlHttpRequestDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this
            ->session
            ->expects($this->never())
            ->method('remove')
            ->with($this->gridHash);

        $this->grid->handleRequest($this->request);
    }

    public function testNotResetGridSessionWhenPersistenceSettedDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this
            ->session
            ->expects($this->never())
            ->method('remove')
            ->with($this->gridHash);

        $this->grid->setPersistence(true);

        $this->grid->handleRequest($this->request);
    }

    public function testNotResetGridSessionWhenRefererIsSameGridDuringHandleRequest(): void
    {
        $this->mockNotResetGridSessionWhenSameGridReferer();

        $this->grid->handleRequest($this->request);
    }

    public function testStartNewSessionDuringHandleRequestOnFirstGridRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->grid->handleRequest($this->request);

        $this->assertTrue($this->grid->isNewSession());
    }

    public function testStartKeepSessionDuringHandleRequestNotOnFirstGridRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this
            ->session
            ->method('get')
            ->with($this->gridHash)
            ->willReturn('sessionData');

        $this->grid->handleRequest($this->request);

        $this->assertFalse($this->grid->isNewSession());
    }

    public function testMassActionRedirect(): void
    {
        $this->mockMassActionCallbackResponse();

        $this->grid->handleRequest($this->request);

        $this->assertTrue($this->grid->isMassActionRedirect());
    }

    public function testRaiseExceptionIfMassActionIdNotValidDuringHandleRequest(): void
    {
        $massActionId = 10;

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage(\sprintf(Grid::MASS_ACTION_NOT_DEFINED_EX_MSG, $massActionId));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_MASS_ACTION => $massActionId]);

        $this->grid->handleRequest($this->request);
    }

    public function testRaiseExceptionIfMassActionCallbackNotValidDuringHandleRequest(): void
    {
        $invalidCallback = 'invalidCallback';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(Grid::MASS_ACTION_CALLBACK_NOT_VALID_EX_MSG, $invalidCallback));

        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_MASS_ACTION => 0]);

        $massAction = $this->stubMassActionWithCallback($invalidCallback);

        $this->grid->addMassAction($massAction);

        $this->grid->handleRequest($this->request);
    }

    public function testResetPageAndLimitIfMassActionHandleAllDataDuringHandleRequest(): void
    {
        $this->mockResetPageAndLimitIfMassActionAndAllKeys();

        $this->grid->handleRequest($this->request);

        $this->assertEquals(0, $this->grid->getLimit());
    }

    public function testMassActionResponseFromCallbackDuringHandleRequest(): void
    {
        $callbackResponse = $this->mockMassActionCallbackResponse();

        $this->grid->handleRequest($this->request);

        $this->assertEquals($callbackResponse, $this->grid->getMassActionResponse());
    }

    public function testMassActionResponseFromControllerActionDuringHandleRequest(): void
    {
        $callbackResponse = $this->mockMassActionControllerResponse();

        $this->grid->handleRequest($this->request);

        $this->assertEquals($callbackResponse, $this->grid->getMassActionResponse());
    }

    public function testRaiseExceptionIfExportIdNotValidDuringHandleRequest(): void
    {
        $exportId = 10;

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage(\sprintf(Grid::EXPORT_NOT_DEFINED_EX_MSG, $exportId));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_EXPORT => $exportId]);

        $this->grid->handleRequest($this->request);
    }

    public function testProcessExportsDuringHandleRequest(): void
    {
        $response = $this->mockExports();

        $this->grid->handleRequest($this->request);

        $this->assertEquals(0, $this->grid->getPage());
        $this->assertEquals(0, $this->grid->getLimit());
        $this->assertTrue($this->grid->isReadyForExport());
        $this->assertEquals($response, $this->grid->getExportResponse());
    }

    public function testProcessExportsButNotFiltersPageOrderLimitDuringHandleRequest(): void
    {
        $this->mockExportsButNotFiltersPageOrderLimit();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessPageDuringHandleRequest(): void
    {
        $this->mockPageRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessPageWithQueryOrderingDuringHandleRequest(): void
    {
        $this->mockPageQueryOrderRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessPageWithQueryLimitDuringHandleRequest(): void
    {
        $this->mockPageLimitRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessPageWithMassActionDuringHandleRequest(): void
    {
        $this->mockPageMassActionRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessPageWithFiltersAndRequestDataDuringHandleRequest(): void
    {
        $this->mockPageFiltersRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessPageWithFiltersDifferentFromSelectDuringHandleRequest(): void
    {
        $this->mockPageNotSelectFilterRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessPageWithSelectFilterColumnNotSelectMultiDuringHandleRequest(): void
    {
        $this->mockPageColumnNotSelectMultiRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessOrderDescDuringHandleRequest(): void
    {
        $colId = 'colId';
        $order = 'desc';
        $queryOrder = "$colId|$order";

        $column = $this->mockOrderRequestData($colId, $order);

        $column
            ->expects($this->once())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_ORDER => $queryOrder, Grid::REQUEST_QUERY_PAGE => 0]);

        $this->grid->handleRequest($this->request);
    }

    public function testProcessOrderAscDuringHandleRequest(): void
    {
        $colId = 'colId';
        $order = 'asc';
        $queryOrder = "$colId|$order";

        $column = $this->mockOrderRequestData($colId, $order);

        $column
            ->expects($this->once())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_ORDER => $queryOrder, Grid::REQUEST_QUERY_PAGE => 0]);

        $this->grid->handleRequest($this->request);
    }

    public function testProcessOrderColumnNotSortableDuringHandleRequest(): void
    {
        $this->mockOrderColumnNotSortable();

        $this->grid->handleRequest($this->request);
    }

    public function testColumnsNotOrderedDuringHandleRequestIfNoOrderRequested(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->stubPrimaryColumn();
        $column
            ->method('isSortable')
            ->willReturn(true);

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);
        $this->grid->setColumns($columns);

        $this->stubRequestWithData([]);

        $column
            ->expects($this->never())
            ->method('setOrder');

        $this->grid->handleRequest($this->request);

        $this->assertEquals(0, $this->grid->getPage());
    }

    public function testProcessConfiguredLimitDuringHandleRequest(): void
    {
        $this->mockConfiguredLimitRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessNonConfiguredLimitDuringHandleRequest(): void
    {
        $this->mockNonConfiguredLimitRequestData();

        $this->grid->handleRequest($this->request);

        $this->assertEmpty($this->grid->getLimit());
    }

    public function testSetDefaultSessionFiltersDuringHandleRequest(): void
    {
        $this->mockDefaultSessionFiltersWithoutRequestData();

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultPageRaiseExceptionIfPageHasNegativeValueDuringHandleRequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::PAGE_NOT_VALID_EX_MSG);

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->grid->setDefaultPage(-1);

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultPageDuringHandleRequest(): void
    {
        $this->mockDefaultPage();

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultOrderRaiseExceptionIfOrderNotAscNeitherDescDuringHandleRequest(): void
    {
        $columnOrder = 'foo';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Grid::COLUMN_ORDER_NOT_VALID_EX_MSG, $columnOrder));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $colId = 'col';
        $column = $this->stubColumn($colId);
        $this->grid->addColumn($column);

        $this->grid->setDefaultOrder($colId, $columnOrder);

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultOrderRaiseExceptionIfColumnDoesNotExistsDuringHandleRequest(): void
    {
        $colId = 'col';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Columns::MISSING_COLUMN_EX_MSG, $colId));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->arrangeGridPrimaryColumn();

        $this->grid->setDefaultOrder($colId, 'asc');

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultOrderAscDuringHandleRequest(): void
    {
        $this->mockDefaultOrder('asc');

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultOrderDescDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $columnId = 'columnId';
        $order = 'desc';
        $column
            ->method('getId')
            ->willReturn($columnId);

        $this->grid->setDefaultOrder($columnId, $order);

        $column
            ->expects($this->once())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_ORDER => "$columnId|$order"]);

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultLimitRaiseExceptionIfLimitIsNotAPositiveDuringHandleRequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::DEFAULT_LIMIT_NOT_VALID_EX_MSG);

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->grid->setDefaultLimit(-1);

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultLimitRaiseExceptionIfLimitIsNotDefinedInGridLimitsDuringHandleRequest(): void
    {
        $limit = 2;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Grid::LIMIT_NOT_DEFINED_EX_MSG, $limit));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->grid->setDefaultLimit($limit);

        $this->grid->handleRequest($this->request);
    }

    public function testSetDefaultLimitDuringHandleRequest(): void
    {
        $this->mockDefaultLimit();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessDefaultTweaksDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $title = 'aTweak';
        $tweak = ['reset' => 1];
        $tweakId = 'aValidTweakId';

        $this->grid->addTweak($title, $tweak, $tweakId);

        $this->grid->setDefaultTweak($tweakId);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('remove')
            ->with($this->gridHash);

        $this->grid->handleRequest($this->request);
    }

    public function testSetPermanentSessionFiltersDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $col1Id = 'col1';
        $col1FilterValue = 'val1';
        $column1 = $this->stubColumn($col1Id);
        $this->grid->addColumn($column1);

        $col2Id = 'col2';
        $col2FilterValue = ['val2'];
        $column2 = $this->stubColumn($col2Id);
        $this->grid->addColumn($column2);

        $col3Id = 'col3';
        $col3FilterValue = ['from' => true];
        $column3 = $this->stubColumn($col3Id);
        $this->grid->addColumn($column3);

        $col4Id = 'col4';
        $col4FilterValue = ['from' => false];
        $column4 = $this->stubColumn($col4Id);
        $this->grid->addColumn($column4);

        $col5Id = 'col5';
        $col5FilterValue = ['from' => 'foo', 'to' => 'bar'];
        $column5 = $this
            ->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column5
            ->method('getId')
            ->willReturn($col5Id);
        $column5
            ->method('getFilterType')
            ->willReturn('select');

        $this->grid->addColumn($column5);

        $this->grid->setPermanentFilters([
            $col1Id => $col1FilterValue,
            $col2Id => $col2FilterValue,
            $col3Id => $col3FilterValue,
            $col4Id => $col4FilterValue,
            $col5Id => $col5FilterValue,
        ]);

        $column
            ->expects($this->never())
            ->method('setData')
            ->with($this->anything());
        $column1
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => $col1FilterValue]);
        $column2
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => $col2FilterValue]);
        $column3
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => 1]);
        $column4
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => 0]);
        $column5
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => ['foo'], 'to' => ['bar']]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [
                $col1Id => ['from' => $col1FilterValue],
                $col2Id => ['from' => $col2FilterValue],
                $col3Id => ['from' => 1],
                $col4Id => ['from' => 0],
                $col5Id => ['from' => ['foo'], 'to' => ['bar']],
            ]);

        $this->grid->handleRequest($this->request);
    }

    public function testPrepareRowsFromDataIfDataAlreadyLoadedDuringHandleRequest(): void
    {
        $source = $this->arrangeGridSourceDataLoadedWithoutRowsReturned();
        $columns = $this->arrangeGridWithColumnsIterator();

        $maxResults = 5;
        $limit = 10;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_LIMIT => $limit]);

        $this->grid->setLimits($limit);
        $this->grid->setMaxResults($maxResults);

        $source
            ->expects($this->once())
            ->method('executeFromData')
            ->with($columns->getIterator(), 0, $limit, $maxResults)
            ->willReturn(new Rows());

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_LIMIT => $limit, Grid::REQUEST_QUERY_PAGE => 0]);

        $this->grid->handleRequest($this->request);
    }

    public function testPrepareRowsFromExecutionIfDataNotLoadedDuringHandleRequest(): void
    {
        $source = $this->arrangeGridSourceDataNotLoadedWithoutRowsReturned();
        $columns = $this->arrangeGridWithColumnsIterator();

        $maxResults = 5;
        $limit = 10;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_LIMIT => $limit]);

        $this->grid->setLimits($limit);
        $this->grid->setMaxResults($maxResults);
        $this->grid->setDataJunction(Column::DATA_DISJUNCTION);

        $source
            ->expects($this->once())
            ->method('execute')
            ->with($columns->getIterator(), 0, $limit, $maxResults, Column::DATA_DISJUNCTION)
            ->willReturn(new Rows());

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_LIMIT => $limit, Grid::REQUEST_QUERY_PAGE => 0]);

        $this->grid->handleRequest($this->request);
    }

    public function testRaiseExceptionIfNotRowInstanceReturnedFromSurceIfDataAlreadyLoadedDuringHandleRequest(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Grid::NO_ROWS_RETURNED_EX_MSG);

        $this->arrangeGridSourceDataLoadedWithoutRowsReturned();

        $this->grid->handleRequest($this->request);
    }

    public function testRaiseExceptionIfNotRowInstanceReturnedFromSurceIfDataNotLoadedLoadedDuringHandleRequest(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Grid::NO_ROWS_RETURNED_EX_MSG);

        $this->arrangeGridSourceDataNotLoadedWithoutRowsReturned();

        $this->grid->handleRequest($this->request);
    }

    public function testSetFirstPageIfNoRowsFromSourceIfDataAlreadyDataAndRequestedPageNotFirst(): void
    {
        $source = $this->arrangeGridSourceDataLoadedWithoutRowsReturned();
        $columns = $this->arrangeGridWithColumnsIterator();

        $page = 2;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_PAGE => $page]);

        $executeFromDataMap = [
            [$columns->getIterator(), $page, null, null, new Rows()],
            [$columns->getIterator(), 0, null, null, new Rows()],
        ];

        $source
            ->expects($this->exactly(2))
            ->method('executeFromData')
            ->willReturnMap($executeFromDataMap);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);

        $this->grid->handleRequest($this->request);
    }

    public function testSetFirstPageIfNoRowsFromSourceIfDataNotLoadedAndRequestedPageNotFirst(): void
    {
        $source = $this->arrangeGridSourceDataNotLoadedWithoutRowsReturned();
        $columns = $this->arrangeGridWithColumnsIterator();

        $page = 2;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_PAGE => $page]);

        $executeMap = [
            [$columns->getIterator(), $page, null, null, Column::DATA_CONJUNCTION, new Rows()],
            [$columns->getIterator(), 0, null, null, Column::DATA_CONJUNCTION, new Rows()],
        ];

        $source
            ->expects($this->exactly($page))
            ->method('execute')
            ->willReturnMap($executeMap);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);

        $this->grid->handleRequest($this->request);
    }

    public function testAddRowActionsToAllColumnsDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $actionsColumnId1 = 'actionsColumnId';
        $actionsColumn1 = $this
            ->getMockBuilder(ActionsColumn::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionsColumn1
            ->method('getId')
            ->willReturn($actionsColumnId1);

        $rowAction1 = new RowAction('title', 'route');
        $rowAction1->setColumn($actionsColumnId1);

        $this->grid->addRowAction($rowAction1);

        $rowAction2 = new RowAction('title', 'route');
        $rowAction2->setColumn($actionsColumnId1);

        $this->grid->addRowAction($rowAction2);

        $actionsColumnId2 = 'actionsColumnId2';
        $actionsColumn2 = $this
            ->getMockBuilder(ActionsColumn::class)
            ->disableOriginalConstructor()
            ->getMock();
        $actionsColumn2
            ->method('getId')
            ->willReturn($actionsColumnId2);

        $rowAction3 = new RowAction('title', 'route');
        $rowAction3->setColumn($actionsColumnId2);

        $this->grid->addRowAction($rowAction3);

        $hasColumnByIdMap = [
            [$actionsColumnId1, true, $actionsColumn1],
            [$actionsColumnId2, true, $actionsColumn2],
        ];

        $columns = $this->arrangeGridWithColumnsIterator();
        $columns
            ->method('hasColumnById')
            ->willReturnMap($hasColumnByIdMap);

        $this->grid->setColumns($columns);

        $actionsColumn1
            ->expects($this->once())
            ->method('setRowActions')
            ->with([$rowAction1, $rowAction2]);

        $actionsColumn2
            ->expects($this->once())
            ->method('setRowActions')
            ->with([$rowAction3]);

        $this->grid->handleRequest($this->request);
    }

    public function testAddRowActionsToNotExistingColumnDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $actionsColumnId1 = 'actionsColumnId';

        $rowAction1 = new RowAction('title', 'route');
        $rowAction1->setColumn($actionsColumnId1);

        $this->grid->addRowAction($rowAction1);

        $actionsColumnId2 = 'actionsColumnId2';

        $rowAction2 = new RowAction('title', 'route');
        $rowAction2->setColumn($actionsColumnId2);

        $this->grid->addRowAction($rowAction2);

        $columns = $this->arrangeGridWithColumnsIterator();
        $this->grid->setColumns($columns);
        $this->grid->setActionsColumnSize(2);

        $actionsColumnTitle = 'aTitle';
        $this->grid->setActionsColumnTitle($actionsColumnTitle);

        $missingActionsColumn1 = new ActionsColumn($actionsColumnId1, $actionsColumnTitle, [$rowAction1]);
        $missingActionsColumn1->setSize(2);
        $missingActionsColumn2 = new ActionsColumn($actionsColumnId2, $actionsColumnTitle, [$rowAction2]);
        $missingActionsColumn2->setSize(2);

        $columns
            ->expects($this->exactly(2))
            ->method('addColumn')
            ->with(...self::withConsecutive([$missingActionsColumn1], [$missingActionsColumn2]));

        $this->grid->handleRequest($this->request);
    }

    public function testAddMassActionColumnsDuringHandleRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $columns = $this->arrangeGridWithColumnsIterator();

        $this->grid->addMassAction(new MassAction('title'));

        $columns
            ->expects($this->once())
            ->method('addColumn')
            ->with($this->isInstanceOf(MassActionColumn::class), 1);

        $this->grid->handleRequest($this->request);
    }

    public function testSetPrimaryFieldOnEachRow(): void
    {
        $row = $this->createMock(Row::class);
        $row2 = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);
        $rows->addRow($row2);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridWithColumnsIterator();

        $row
            ->expects($this->once())
            ->method('setPrimaryField')
            ->with('primaryID');

        $row2
            ->expects($this->once())
            ->method('setPrimaryField')
            ->with('primaryID');

        $this->grid->handleRequest($this->request);
    }

    public function testPopulateSelectFiltersInSourceFromDataIfDataLoadedDuringHandleRequest(): void
    {
        $columns = $this->arrangeGridWithColumnsIterator();

        $source = $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $source
            ->expects($this->once())
            ->method('populateSelectFiltersFromData')
            ->with($columns);

        $this->grid->handleRequest($this->request);
    }

    public function testPopulateSelectFiltersInSourceIfDataNotLoadedDuringHandleRequest(): void
    {
        $source = $this->arrangeGridSourceDataNotLoadedWithEmptyRows();

        $columns = $this->arrangeGridWithColumnsIterator();

        $source
            ->expects($this->once())
            ->method('populateSelectFilters')
            ->with($columns);

        $this->grid->handleRequest($this->request);
    }

    public function testSetTotalCountFromDataDuringHandleRequest(): void
    {
        $totalCount = 2;
        $this->arrangeGridSourceDataLoadedWithEmptyRows($totalCount);
        $this->arrangeGridWithColumnsIterator();

        $this->grid->handleRequest($this->request);

        $this->assertEquals($totalCount, $this->grid->getTotalCount());
    }

    public function testSetTotalCountDuringHandleRequest(): void
    {
        $totalCount = 2;
        $this->arrangeGridSourceDataNotLoadedWithEmptyRows($totalCount);
        $this->arrangeGridWithColumnsIterator();

        $this->grid->handleRequest($this->request);

        $this->assertEquals($totalCount, $this->grid->getTotalCount());
    }

    public function testRaiseExceptionIfTweakDoesNotExistsDuringHandleRequest(): void
    {
        $tweakId = 'aValidTweakId';

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage(\sprintf(Grid::TWEAK_NOT_DEFINED_EX_MSG, $tweakId));

        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakResetDuringHandleRequest(): void
    {
        $this->mockTweakReset();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakFiltersDuringHandleRequest(): void
    {
        $this->mockTweakFilters();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakOrderDuringHandleRequest(): void
    {
        $this->mockTweakOrder();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakMassActionDuringHandleRequest(): void
    {
        $this->mockTweakMassAction();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakPageDuringHandleRequest(): void
    {
        $this->mockTweakPage();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakLimitDuringHandleRequest(): void
    {
        $this->mockTweakLimit();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakExportDuringHandleRequest(): void
    {
        $this->mockTweakExport();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessTweakExportButNotFiltersPageOrderLimitDuringHandleRequest(): void
    {
        $this->mockTweakExportButNotFiltersPageOrderLimit();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessRemoveActiveTweakGroupsDuringHandleRequest(): void
    {
        $this->mockRemoveActiveTweakGroups();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessRemoveActiveTweakDuringHandleRequest(): void
    {
        $this->mockRemoveActiveTweak();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessAddActiveTweakDuringHandleRequest(): void
    {
        $this->mockAddActiveTweak();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessHiddenColumnsDuringHandleRequest(): void
    {
        $this->mockHiddenColumns();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessVisibleColumnsDuringHandleRequest(): void
    {
        $this->mockVisibleColumns();

        $this->grid->handleRequest($this->request);
    }

    public function testProcessColumnVisibilityDuringHandleRequest(): void
    {
        $this->mockColumnVisibility();

        $this->grid->handleRequest($this->request);
    }

    public function testGetTweaksWithUrlWithoutGetParameters(): void
    {
        $routeUrl = 'http://www.foo.com';

        $title = 'aTweak';
        $tweak = ['filters' => [], 'order' => 'columnId', 'page' => 1, 'limit' => 50, 'export' => 1, 'massAction' => 1];
        $id = 'aValidTweakId';
        $group = 'tweakGroup';
        $tweakUrl = \sprintf('%s?[%s]=%s', $routeUrl, Grid::REQUEST_QUERY_TWEAK, $id);

        $this->grid->addTweak($title, $tweak, $id, $group);

        $title2 = 'aTweak';
        $tweak2 = ['filters' => [], 'order' => 'columnId2', 'page' => 2, 'limit' => 100, 'export' => 0, 'massAction' => 0];
        $id2 = 'aValidTweakId2';
        $group2 = 'tweakGroup2';
        $tweakUrl2 = \sprintf('%s?[%s]=%s', $routeUrl, Grid::REQUEST_QUERY_TWEAK, $id2);

        $this->grid->setRouteUrl($routeUrl);

        $this->grid->addTweak($title2, $tweak2, $id2, $group2);

        $result = [
            $id => \array_merge(['title' => $title, 'id' => $id, 'group' => $group, 'url' => $tweakUrl], $tweak),
            $id2 => \array_merge(['title' => $title2, 'id' => $id2, 'group' => $group2, 'url' => $tweakUrl2], $tweak2),
        ];

        $this->assertEquals($result, $this->grid->getTweaks());
    }

    public function testGetTweaksWithUrlWithGetParameters(): void
    {
        $routeUrl = 'http://www.foo.com?foo=foo';

        $title = 'aTweak';
        $tweak = ['filters' => [], 'order' => 'columnId', 'page' => 1, 'limit' => 50, 'export' => 1, 'massAction' => 1];
        $id = 'aValidTweakId';
        $group = 'tweakGroup';
        $tweakUrl = \sprintf('%s&[%s]=%s', $routeUrl, Grid::REQUEST_QUERY_TWEAK, $id);

        $this->grid->addTweak($title, $tweak, $id, $group);

        $title2 = 'aTweak';
        $tweak2 = ['filters' => [], 'order' => 'columnId2', 'page' => 2, 'limit' => 100, 'export' => 0, 'massAction' => 0];
        $id2 = 'aValidTweakId2';
        $group2 = 'tweakGroup2';
        $tweakUrl2 = \sprintf('%s&[%s]=%s', $routeUrl, Grid::REQUEST_QUERY_TWEAK, $id2);

        $this->grid->setRouteUrl($routeUrl);

        $this->grid->addTweak($title2, $tweak2, $id2, $group2);

        $result = [
            $id => \array_merge(['title' => $title, 'id' => $id, 'group' => $group, 'url' => $tweakUrl], $tweak),
            $id2 => \array_merge(['title' => $title2, 'id' => $id2, 'group' => $group2, 'url' => $tweakUrl2], $tweak2),
        ];

        $this->assertEquals($result, $this->grid->getTweaks());
    }

    public function testRaiseExceptionIfGetNonExistentTweak(): void
    {
        $nonExistentTweak = 'aNonExistentTweak';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Grid::NOT_VALID_TWEAK_ID_EX_MSG, $nonExistentTweak));

        $tweakId = 'aValidTweakId';
        $tweak = ['filters' => [], 'order' => 'columnId', 'page' => 1, 'limit' => 50, 'export' => 1, 'massAction' => 1];

        $this->grid->addTweak('title', $tweak, $tweakId, 'group');

        $this->grid->getTweak($nonExistentTweak);
    }

    public function testGetTweak(): void
    {
        $title = 'aTweak';
        $id = 'aValidTweakId';
        $group = 'tweakGroup';
        $tweak = ['filters' => [], 'order' => 'columnId', 'page' => 1, 'limit' => 50, 'export' => 1, 'massAction' => 1];
        $tweakUrl = \sprintf('?[%s]=%s', Grid::REQUEST_QUERY_TWEAK, $id);

        $this->grid->addTweak($title, $tweak, $id, $group);

        $tweakResult = \array_merge(['title' => $title, 'id' => $id, 'group' => $group, 'url' => $tweakUrl], $tweak);

        $this->assertEquals($tweakResult, $this->grid->getTweak($id));
    }

    public function testGetTweaksByGroupExcludingThoseWhoDoNotHaveTheGroup(): void
    {
        $title = 'aTweak';
        $id = 'aValidTweakId';
        $group = 'tweakGroup';
        $tweak = ['filters' => [], 'order' => 'columnId', 'page' => 1, 'limit' => 50, 'export' => 1, 'massAction' => 1];
        $tweakUrl = \sprintf('?[%s]=%s', Grid::REQUEST_QUERY_TWEAK, $id);
        $tweakResult = [$id => \array_merge(['title' => $title, 'id' => $id, 'group' => $group, 'url' => $tweakUrl], $tweak)];

        $this->grid->addTweak($title, $tweak, $id, $group);

        $tweak2 = ['filters' => [], 'order' => 'columnId', 'page' => 2, 'limit' => 100, 'export' => 0, 'massAction' => 0];

        $this->grid->addTweak('aTweak2', $tweak2, 'aValidTweakId2', 'tweakGroup2');

        $this->assertEquals($tweakResult, $this->grid->getTweaksGroup($group));
    }

    public function testGetActiveTweaks(): void
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $colFilter = ['from' => 'foo', 'to' => 'bar'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilterType')
            ->willReturn('select');

        $title = 'aTweak';
        $tweak = ['filters' => [$colId => $colFilter]];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this->grid->handleRequest($this->request);

        $this->assertEquals([$tweakGroup => $tweakId], $this->grid->getActiveTweaks());
    }

    public function testGetActiveTweakGroup(): void
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $colFilter = ['from' => 'foo', 'to' => 'bar'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilterType')
            ->willReturn('select');

        $title = 'aTweak';
        $tweak = ['filters' => [$colId => $colFilter]];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this->grid->handleRequest($this->request);

        $this->assertEquals($tweakId, $this->grid->getActiveTweakGroup($tweakGroup));
        $this->assertEquals(-1, $this->grid->getActiveTweakGroup('invalidGroup'));
    }

    public function testGetExportResponse(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_EXPORT => 0]);

        $response = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $export = $this->createMock(Export::class);
        $export
            ->method('getResponse')
            ->willReturn($response);

        $this->grid->addExport($export);

        $this->grid->handleRequest($this->request);

        $this->assertEquals($response, $this->grid->getExportResponse());
    }

    public function testIsReadyForExport(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_EXPORT => 0]);

        $response = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $export = $this->createMock(Export::class);
        $export
            ->method('getResponse')
            ->willReturn($response);

        $this->grid->addExport($export);

        $this->grid->handleRequest($this->request);

        $this->assertTrue($this->grid->isReadyForExport());
    }

    public function testSetPermanentFilters(): void
    {
        $filters = [
            'colId1' => 'value',
            'colId2' => 'value',
        ];

        $this->grid->setPermanentFilters($filters);

        $this->assertEquals($filters, $this->grid->getPermanentFilters());
    }

    public function testSetDefaultFilters(): void
    {
        $filters = [
            'colId1' => 'value',
            'colId2' => 'value',
        ];

        $this->grid->setDefaultFilters($filters);

        $this->assertEquals($filters, $this->grid->getDefaultFilters());
    }

    public function testSetDefaultOrder(): void
    {
        $colId = 'COLID';
        $order = 'ASC';

        $this->grid->setDefaultOrder($colId, $order);

        $this->assertEquals(\sprintf("$colId|%s", \strtolower($order)), $this->grid->getDefaultOrder());
    }

    public function testGetRows(): void
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->grid->handleRequest($this->request);

        $this->assertEquals($rows, $this->grid->getRows());
    }

    public function testGetTotalCount(): void
    {
        $totalCount = 20;
        $this->arrangeGridSourceDataLoadedWithEmptyRows($totalCount);
        $this->arrangeGridWithColumnsIterator();

        $this->grid->handleRequest($this->request);

        $this->assertEquals($totalCount, $this->grid->getTotalCount());
    }

    public function testGetPageCountWithoutLimit(): void
    {
        $this->assertEquals(1, $this->grid->getPageCount());
    }

    public function testGetPageCount(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows(29);
        $this->arrangeGridWithColumnsIterator();

        $limit = 10;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_LIMIT => $limit]);

        $this->grid->setLimits($limit);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_LIMIT => $limit, Grid::REQUEST_QUERY_PAGE => 0]);

        $this->grid->handleRequest($this->request);

        $this->assertEquals(3, $this->grid->getPageCount());
    }

    public function testIsPagerSectionNotVisibleWhenNoLimitsSetted(): void
    {
        $this->assertFalse($this->grid->isPagerSectionVisible());
    }

    public function testIsPagerSectionNotVisibleWhenSmallestLimitGreaterThanTotalCount(): void
    {
        $this->grid->setLimits([10, 20, 30]);

        $this->assertFalse($this->grid->isPagerSectionVisible());
    }

    public function testIsPagerSectionVisibleWhenSmallestLimitLowestThanTotalCount(): void
    {
        $this->grid->setLimits([10, 20, 30]);

        $this->assertFalse($this->grid->isPagerSectionVisible());
    }

    public function testDeleteAction(): void
    {
        $source = $this->createMock(Source::class);

        $this->grid->setSource($source);

        $deleteIds = [1, 2, 3];
        $source
            ->expects($this->once())
            ->method('delete')
            ->with($deleteIds);

        $this->grid->deleteAction($deleteIds);
    }

    public function testGetRawDataWithAllColumnsIfNoColumnsRequested(): void
    {
        $rows = new Rows();

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column1 = $this->arrangeGridPrimaryColumn();
        $col1Id = 'col1Id';
        $column1
            ->method('getId')
            ->willReturn($col1Id);

        $col2Id = 'col2Id';
        $column2 = $this->stubColumn($col2Id);
        $this->grid->addColumn($column2);

        $rowCol1Field = 'rowCol1Field';
        $rowCol2Field = 'rowCol2Field';

        $getFieldRowMap = [
            [$col1Id, $rowCol1Field],
            [$col2Id, $rowCol2Field],
        ];

        $row = $this->createMock(Row::class);
        $row
            ->method('getField')
            ->willReturnMap($getFieldRowMap);

        $rows->addRow($row);

        $row2Col1Field = 'row2Col1Field';
        $row2Col2Field = 'row2Col2Field';

        $getFieldRow2Map = [
            [$col1Id, $row2Col1Field],
            [$col2Id, $row2Col2Field],
        ];

        $row2 = $this->createMock(Row::class);
        $row2
            ->method('getField')
            ->willReturnMap($getFieldRow2Map);

        $rows->addRow($row2);

        $this->grid->handleRequest($this->request);

        $this->assertEquals(
            [
                [$col1Id => $rowCol1Field, $col2Id => $rowCol2Field],
                [$col1Id => $row2Col1Field, $col2Id => $row2Col2Field],
            ],
            $this->grid->getRawData()
        );
    }

    public function testGetRawDataWithSubsetOfColumns(): void
    {
        $rows = new Rows();

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column1 = $this->arrangeGridPrimaryColumn();
        $col1Id = 'col1Id';
        $column1
            ->method('getId')
            ->willReturn($col1Id);

        $col2Id = 'col2Id';
        $column2 = $this->stubColumn($col2Id);
        $this->grid->addColumn($column2);

        $rowCol1Field = 'rowCol1Field';
        $rowCol2Field = 'rowCol2Field';

        $getFieldRowMap = [
            [$col1Id, $rowCol1Field],
            [$col2Id, $rowCol2Field],
        ];

        $row = $this->createMock(Row::class);
        $row
            ->method('getField')
            ->willReturnMap($getFieldRowMap);

        $rows->addRow($row);

        $row2Col1Field = 'row2Col1Field';
        $row2Col2Field = 'row2Col2Field';

        $getFieldRow2Map = [
            [$col1Id, $row2Col1Field],
            [$col2Id, $row2Col2Field],
        ];

        $row2 = $this->createMock(Row::class);
        $row2
            ->method('getField')
            ->willReturnMap($getFieldRow2Map);

        $rows->addRow($row2);

        $this->grid->handleRequest($this->request);

        $this->assertEquals(
            [
                [$col1Id => $rowCol1Field],
                [$col1Id => $row2Col1Field],
            ],
            $this->grid->getRawData($col1Id)
        );
    }

    public function testGetRawDataWithoutNamedIndexesResult(): void
    {
        $rows = new Rows();

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();
        $colId = 'colId';
        $column
            ->method('getId')
            ->willReturn($colId);

        $rowColField = 'rowColField';
        $row = $this->createMock(Row::class);
        $row
            ->method('getField')
            ->with($colId)
            ->willReturn($rowColField);

        $rows->addRow($row);

        $row2ColField = 'row2ColField';
        $row2 = $this->createMock(Row::class);
        $row2
            ->method('getField')
            ->with($colId)
            ->willReturn($row2ColField);

        $rows->addRow($row2);

        $this->grid->handleRequest($this->request);

        $this->assertEquals(
            [
                [$rowColField],
                [$row2ColField],
            ],
            $this->grid->getRawData($colId, false)
        );
    }

    public function testGetFiltersRaiseExceptionIfNoRequestProcessed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Grid::GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG);

        $this->grid->getFilters();
    }

    public function testGetFilters(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $col1Id = 'col1Id';
        $column1 = $this->stubColumn($col1Id);
        $this->grid->addColumn($column1);

        $col2Id = 'col2Id';
        $column2 = $this->stubColumnWithDefaultOperator(Column::OPERATOR_GT, $col2Id);
        $this->grid->addColumn($column2);

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED => true,
            Grid::REQUEST_QUERY_MASS_ACTION => -1,
            Grid::REQUEST_QUERY_EXPORT => -1,
            Grid::REQUEST_QUERY_PAGE => 1,
            Grid::REQUEST_QUERY_LIMIT => 10,
            Grid::REQUEST_QUERY_ORDER => null,
            Grid::REQUEST_QUERY_TEMPLATE => 'aTemplate',
            Grid::REQUEST_QUERY_RESET => false,
            MassActionColumn::ID => 'massActionColId',
        ]);

        $filter1Operator = Column::OPERATOR_BTW;
        $filter1From = 'from1';
        $filter1To = 'to1';
        $filter1 = new Filter($filter1Operator, ['from' => $filter1From, 'to' => $filter1To]);

        $filter2Operator = Column::OPERATOR_GT;
        $filter2From = 'from2';
        $filter2 = new Filter($filter2Operator, $filter2From);

        $this->grid->setDefaultFilters([
            $col1Id => ['operator' => $filter1Operator, 'from' => $filter1From, 'to' => $filter1To],
            $col2Id => ['from' => $filter2From],
        ]);

        $hash = $this->gridHash;
        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with(...self::withConsecutive(
                [$hash, [Grid::REQUEST_QUERY_PAGE => 0]],
                [$hash, [
                    Grid::REQUEST_QUERY_PAGE => 0,
                    $col1Id => ['operator' => $filter1Operator, 'from' => $filter1From, 'to' => $filter1To],
                    $col2Id => ['from' => $filter2From], ],
                ],
                [$hash, [
                    Grid::REQUEST_QUERY_PAGE => 0,
                    $col1Id => ['operator' => $filter1Operator, 'from' => $filter1From, 'to' => $filter1To],
                    $col2Id => ['from' => $filter2From], ],
                ],
            ));

        $this->grid->handleRequest($this->request);

        $this->assertEquals(
            [$col1Id => $filter1, $col2Id => $filter2],
            $this->grid->getFilters()
        );
    }

    public function testGetFilterRaiseExceptionIfNoRequestProcessed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Grid::GET_FILTERS_NO_REQUEST_HANDLED_EX_MSG);

        $this->grid->getFilter('foo');
    }

    public function testGetFilterReturnNullIfRequestedColumnHasNoFilter(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->grid->handleRequest($this->request);

        $this->assertNull($this->grid->getFilter('foo'));
    }

    public function testGetFilter(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $colId = 'col1Id';
        $column = $this->stubColumn($colId);
        $this->grid->addColumn($column);

        $filterOperator = Column::OPERATOR_BTW;
        $filterFrom = 'from1';
        $filterTo = 'to1';
        $filter = new Filter($filterOperator, ['from' => $filterFrom, 'to' => $filterTo]);

        $this->grid->setDefaultFilters([
            $colId => ['operator' => $filterOperator, 'from' => $filterFrom, 'to' => $filterTo],
        ]);

        $this->grid->handleRequest($this->request);

        $this->assertEquals($filter, $this->grid->getFilter($colId));
    }

    public function testHasFilterRaiseExceptionIfNoRequestProcessed(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Grid::HAS_FILTER_NO_REQUEST_HANDLED_EX_MSG);

        $this->grid->hasFilter('foo');
    }

    public function testHasFilterReturnNullIfRequestedColumnHasNoFilter(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->grid->handleRequest($this->request);

        $this->assertFalse($this->grid->hasFilter('foo'));
    }

    public function testHasFilter(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $colId = 'col1Id';
        $column = $this->stubColumn($colId);
        $this->grid->addColumn($column);

        $filterOperator = Column::OPERATOR_BTW;
        $filterFrom = 'from1';
        $filterTo = 'to1';

        $this->grid->setDefaultFilters([
            $colId => ['operator' => $filterOperator, 'from' => $filterFrom, 'to' => $filterTo],
        ]);

        $this->grid->handleRequest($this->request);

        $this->assertTrue($this->grid->hasFilter($colId));
    }

    public function testRaiseExceptionIfNoSourceSettedDuringRedirect(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(Grid::SOURCE_NOT_SETTED_EX_MSG);

        $this->grid->isReadyForRedirect();
    }

    public function testCreateHashWithIdDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->grid->isReadyForRedirect();

        $this->assertEquals($this->gridHash, $this->grid->getHash());
    }

    public function testCreateHashWithMd5DuringRedirect(): void
    {
        $this->arrange($this->createMock(GridConfigInterface::class), null);

        $sourceHash = '4f403d7e887f7d443360504a01aaa30e';

        $this->arrangeGridSourceDataLoadedWithEmptyRows(0, $sourceHash);

        $column = $this->stubPrimaryColumn();

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);
        $this->grid->setColumns($columns);

        $controller = 'aController';

        $this
            ->request
            ->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($controller);

        $this->grid->isReadyForRedirect();

        $this->assertEquals('grid_'.\md5($controller.$columns->getHash().$sourceHash), $this->grid->getHash());
    }

    public function testResetGridSessionWhenResetFiltersIsPressedDuringRedirect(): void
    {
        $this->mockResetGridSessionWhenResetFilterIsPressed();

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotResetGridSessionWhenXmlHttpRequestDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this
            ->session
            ->expects($this->never())
            ->method('remove')
            ->with($this->gridHash);

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotResetGridSessionWhenPersistenceSettedDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this
            ->session
            ->expects($this->never())
            ->method('remove')
            ->with($this->gridHash);

        $this->grid->setPersistence(true);

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotResetGridSessionWhenRefererIsSameGridDuringRedirect(): void
    {
        $this->mockNotResetGridSessionWhenSameGridReferer();

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testStartNewSessionDuringRedirectOnFirstRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->grid->isReadyForRedirect();

        $this->assertTrue($this->grid->isNewSession());
    }

    public function testStartKeepSessionDuringRedirectNotOnFirstRequest(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this
            ->session
            ->method('get')
            ->with($this->gridHash)
            ->willReturn('sessionData');

        $this->grid->isReadyForRedirect();

        $this->assertFalse($this->grid->isNewSession());
    }

    public function testProcessHiddenColumnsDuringRedirect(): void
    {
        $this->mockHiddenColumns();

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testProcessVisibleColumnsDuringRedirect(): void
    {
        $this->mockVisibleColumns();

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testProcessColumnVisibilityDuringRedirect(): void
    {
        $this->mockColumnVisibility();

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testRaiseExceptionIfMassActionIdNotValidDuringRedirect(): void
    {
        $massActionId = 10;

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage(\sprintf(Grid::MASS_ACTION_NOT_DEFINED_EX_MSG, $massActionId));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_MASS_ACTION => $massActionId]);

        $this->grid->isReadyForRedirect();
    }

    public function testRaiseExceptionIfMassActionCallbackNotValidDuringRedirect(): void
    {
        $invalidCallback = 'invalidCallback';

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf(Grid::MASS_ACTION_CALLBACK_NOT_VALID_EX_MSG, $invalidCallback));

        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_MASS_ACTION => 0]);

        $massAction = $this->stubMassActionWithCallback($invalidCallback);

        $this->grid->addMassAction($massAction);

        $this->grid->isReadyForRedirect();
    }

    public function testResetPageAndLimitIfMassActionHandleAllDataDuringRedirect(): void
    {
        $this->mockResetPageAndLimitIfMassActionAndAllKeys();

        $this->assertTrue($this->grid->isReadyForRedirect());

        $this->assertEquals(0, $this->grid->getLimit());
    }

    public function testMassActionResponseFromCallbackDuringRedirect(): void
    {
        $callbackResponse = $this->mockMassActionCallbackResponse();

        $this->assertTrue($this->grid->isReadyForRedirect());

        $this->assertEquals($callbackResponse, $this->grid->getMassActionResponse());
    }

    public function testMassActionResponseFromControllerActionDuringRedirect(): void
    {
        $callbackResponse = $this->mockMassActionControllerResponse();

        $this->assertTrue($this->grid->isReadyForRedirect());

        $this->assertEquals($callbackResponse, $this->grid->getMassActionResponse());
    }

    public function testRaiseExceptionIfExportIdNotValidDuringRedirect(): void
    {
        $exportId = 10;

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage(\sprintf(Grid::EXPORT_NOT_DEFINED_EX_MSG, $exportId));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_EXPORT => $exportId]);

        $this->grid->isReadyForRedirect();
    }

    public function testProcessExportsDuringRedirect(): void
    {
        $response = $this->mockExports();

        $this->assertTrue($this->grid->isReadyForRedirect());

        $this->assertEquals(0, $this->grid->getPage());
        $this->assertEquals(0, $this->grid->getLimit());
        $this->assertTrue($this->grid->isReadyForExport());
        $this->assertEquals($response, $this->grid->getExportResponse());
    }

    public function testProcessExportsButNotFiltersPageOrderLimitDuringRedirect(): void
    {
        $this->mockExportsButNotFiltersPageOrderLimit();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testRaiseExceptionIfTweakDoesNotExistsDuringRedirect(): void
    {
        $tweakId = 'aValidTweakId';

        $this->expectException(\OutOfBoundsException::class);
        $this->expectExceptionMessage(\sprintf(Grid::TWEAK_NOT_DEFINED_EX_MSG, $tweakId));

        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakResetDuringRedirect(): void
    {
        $this->mockTweakReset();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakFiltersDuringRedirect(): void
    {
        $this->mockTweakFilters();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakOrderDuringRedirect(): void
    {
        $this->mockTweakOrder();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakMassActionDuringRedirect(): void
    {
        $this->mockTweakMassAction();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakPageDuringRedirect(): void
    {
        $this->mockTweakPage();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakLimitDuringRedirect(): void
    {
        $this->mockTweakLimit();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakExportDuringRedirect(): void
    {
        $this->mockTweakExport();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessTweakExportButNotFiltersPageOrderLimitDuringRedirect(): void
    {
        $this->mockTweakExportButNotFiltersPageOrderLimit();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessRemoveActiveTweakGroupsDuringRedirect(): void
    {
        $this->mockRemoveActiveTweakGroups();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessRemoveActiveTweakDuringRedirect(): void
    {
        $this->mockRemoveActiveTweak();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessAddActiveTweakDuringRedirect(): void
    {
        $this->mockAddActiveTweak();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessPageDuringRedirect(): void
    {
        $this->mockPageRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessPageWithQueryOrderingDuringRedirect(): void
    {
        $this->mockPageQueryOrderRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessPageWithQueryLimitDuringRedirect(): void
    {
        $this->mockPageLimitRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessPageWithMassActionDuringRedirect(): void
    {
        $this->mockPageMassActionRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessPageWithFiltersAndRequestDataDuringRedirect(): void
    {
        $this->mockPageFiltersRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessPageWithFiltersDifferentFromSelectDuringRedirect(): void
    {
        $this->mockPageNotSelectFilterRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessPageWithSelectFilterColumnNotSelectMultiDuringRedirect(): void
    {
        $this->mockPageColumnNotSelectMultiRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessOrderDescDuringRedirect(): void
    {
        $colId = 'colId';
        $order = 'desc';
        $queryOrder = "$colId|$order";

        $column = $this->mockOrderRequestData($colId, $order);

        $column
            ->expects($this->never())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_ORDER => $queryOrder, Grid::REQUEST_QUERY_PAGE => 0]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessOrderAscDuringRedirect(): void
    {
        $colId = 'colId';
        $order = 'asc';
        $queryOrder = "$colId|$order";

        $column = $this->mockOrderRequestData($colId, $order);

        $column
            ->expects($this->never())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_ORDER => $queryOrder, Grid::REQUEST_QUERY_PAGE => 0]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessOrderColumnNotSortableDuringRedirect(): void
    {
        $this->mockOrderColumnNotSortable();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testColumnsNotOrderedIfNoOrderRequestedDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->stubPrimaryColumn();
        $column
            ->method('isSortable')
            ->willReturn(true);

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);
        $this->grid->setColumns($columns);

        $this->stubRequestWithData([]);

        $column
            ->expects($this->never())
            ->method('setOrder');

        $this->assertFalse($this->grid->isReadyForRedirect());

        $this->assertEquals(0, $this->grid->getPage());
    }

    public function testProcessConfiguredLimitDuringRedirect(): void
    {
        $this->mockConfiguredLimitRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessNonConfiguredLimitDuringRedirect(): void
    {
        $this->mockNonConfiguredLimitRequestData();

        $this->assertTrue($this->grid->isReadyForRedirect());

        $this->assertEmpty($this->grid->getLimit());
    }

    public function testSetDefaultSessionFiltersIfNotRequestDataDuringRedirect(): void
    {
        $this->mockDefaultSessionFiltersWithoutRequestData();

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testSetDefaultSessionFiltersIfSessionDataXmlHttpRequestAndNotExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $col1Id = 'col1';
        $col2Id = 'col2';
        $col3Id = 'col3';
        $col4Id = 'col4';
        $col5Id = 'col5';

        $col1FilterValue = 'val1';
        $col2FilterValue = ['val2'];

        $col5From = 'foo';
        $col5To = 'bar';

        [$column1, $column2, $column3, $column4, $column5] = $this->arrangeColumnsFilters(
            $col1Id,
            $col2Id,
            $col3Id,
            $col4Id,
            $col5Id,
            $col1FilterValue,
            $col2FilterValue,
            $col5From,
            $col5To
        );

        $page = 1;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);
        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $column
            ->expects($this->never())
            ->method('setData')
            ->with($this->anything());
        $column1
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => $col1FilterValue]);
        $column2
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => $col2FilterValue]);
        $column3
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => 1]);
        $column4
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => 0]);
        $column5
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => [$col5From], 'to' => [$col5To]]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with(...self::withConsecutive(
                [$this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]],
                [$this->gridHash, [
                    $col1Id => ['from' => $col1FilterValue],
                    $col2Id => ['from' => $col2FilterValue],
                    $col3Id => ['from' => 1],
                    $col4Id => ['from' => 0],
                    $col5Id => ['from' => [$col5From], 'to' => [$col5To]],
                    Grid::REQUEST_QUERY_PAGE => $page, ],
                ],
                [$this->gridHash, [
                    $col1Id => ['from' => $col1FilterValue],
                    $col2Id => ['from' => $col2FilterValue],
                    $col3Id => ['from' => 1],
                    $col4Id => ['from' => 0],
                    $col5Id => ['from' => [$col5From], 'to' => [$col5To]],
                    Grid::REQUEST_QUERY_PAGE => $page, ],
                ],
            ));

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultSessionFiltersIfHasRequestDataNotXmlHttpButExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $col1Id = 'col1';
        $col2Id = 'col2';
        $col3Id = 'col3';
        $col4Id = 'col4';
        $col5Id = 'col5';

        $col1FilterValue = 'val1';
        $col2FilterValue = ['val2'];

        $col5From = 'foo';
        $col5To = 'bar';

        [$column1, $column2, $column3, $column4, $column5] = $this->arrangeColumnsFilters(
            $col1Id,
            $col2Id,
            $col3Id,
            $col4Id,
            $col5Id,
            $col1FilterValue,
            $col2FilterValue,
            $col5From,
            $col5To
        );

        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_EXPORT => 0]);

        $this->grid->addExport($this->createMock(Export::class));

        $column
            ->expects($this->never())
            ->method('setData')
            ->with($this->anything());
        $column1
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => $col1FilterValue]);
        $column2
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => $col2FilterValue]);
        $column3
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => 1]);
        $column4
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => 0]);
        $column5
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => [$col5From], 'to' => [$col5To]]);

        $this
            ->session
            ->expects($this->never())
            ->method('set');

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultSessionFiltersIfHasRequestDataNotXmlHttpAndNotExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $col1Id = 'col1';
        $col2Id = 'col2';
        $col3Id = 'col3';
        $col4Id = 'col4';
        $col5Id = 'col5';

        $col1FilterValue = 'val1';
        $col2FilterValue = ['val2'];

        $col5From = 'foo';
        $col5To = 'bar';

        [$column1, $column2, $column3, $column4, $column5] = $this->arrangeColumnsFilters(
            $col1Id,
            $col2Id,
            $col3Id,
            $col4Id,
            $col5Id,
            $col1FilterValue,
            $col2FilterValue,
            $col5From,
            $col5To
        );

        $page = 0;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);

        $column
            ->expects($this->never())
            ->method('setData')
            ->with($this->anything());
        $column1
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => $col1FilterValue]);
        $column2
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => $col2FilterValue]);
        $column3
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => 1]);
        $column4
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => 0]);
        $column5
            ->expects($this->never())
            ->method('setData')
            ->with(['from' => [$col5From], 'to' => [$col5To]]);

        $this
            ->session
            ->expects($this->once())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testSetDefaultPageRaiseExceptionIfPageHasNegativeValueDuringRedirect(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::PAGE_NOT_VALID_EX_MSG);

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->grid->setDefaultPage(-1);

        $this->grid->isReadyForRedirect();
    }

    public function testSetDefaultPageIfNotRequestDataDuringRedirect(): void
    {
        $this->mockDefaultPage();

        $this->grid->isReadyForRedirect();
    }

    public function testSetDefaultPageIfRequestDataXmlHttpRequestAndNotExportDuringRedirect(): void
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->grid->setDefaultPage(2);

        $page = 1;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);
        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 1]);

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultPageIfHasRequestDataNotXmlHttpButExportDuringRedirect(): void
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->grid->setDefaultPage(2);

        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_EXPORT => 0]);

        $this->grid->addExport($this->createMock(Export::class));

        $this
            ->session
            ->expects($this->never())
            ->method('set');

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultPageIfHasRequestDataNotXmlHttpAndNotExportDuringRedirect(): void
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->grid->setDefaultPage(2);

        $page = 1;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);

        $this
            ->session
            ->expects($this->once())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testSetDefaultOrderRaiseExceptionIfOrderNotAscNeitherDescDuringRedirect(): void
    {
        $columnOrder = 'foo';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Grid::COLUMN_ORDER_NOT_VALID_EX_MSG, $columnOrder));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $colId = 'col';
        $column = $this->stubColumn($colId);
        $this->grid->addColumn($column);

        $this->grid->setDefaultOrder($colId, $columnOrder);

        $this->grid->isReadyForRedirect();
    }

    public function testSetDefaultOrderRaiseExceptionIfColumnDoesNotExistsDuringRedirect(): void
    {
        $colId = 'col';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Columns::MISSING_COLUMN_EX_MSG, $colId));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->arrangeGridPrimaryColumn();

        $this->grid->setDefaultOrder($colId, 'asc');

        $this->grid->isReadyForRedirect();
    }

    public function testSetDefaultOrderAscIfNotRequestDataDuringRedirect(): void
    {
        $this->mockDefaultOrder('asc');

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testSetDefaultOrderDescIfNotRequestDataDuringRedirect(): void
    {
        $this->mockDefaultOrder('desc');

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testSetDefaultOrderIfRequestDataXmlHttpRequestAndNotExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $columnId = 'columnId';
        $order = 'desc';
        $column
            ->method('getId')
            ->willReturn($columnId);

        $this->grid->setDefaultOrder($columnId, $order);

        $page = 1;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);
        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $column
            ->expects($this->once())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with(...self::withConsecutive(
                [$this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]],
                [$this->gridHash, [Grid::REQUEST_QUERY_ORDER => "$columnId|$order", Grid::REQUEST_QUERY_PAGE => $page]],
                [$this->gridHash, [Grid::REQUEST_QUERY_ORDER => "$columnId|$order", Grid::REQUEST_QUERY_PAGE => $page]]
            ));

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultOrderIfHasRequestDataNotXmlHttpButExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $columnId = 'columnId';
        $order = 'desc';
        $column
            ->method('getId')
            ->willReturn($columnId);

        $this->grid->setDefaultOrder($columnId, $order);

        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_EXPORT => 0]);

        $this->grid->addExport($this->createMock(Export::class));

        $column
            ->expects($this->never())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->never())
            ->method('set');

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultOrderIfHasRequestDataNotXmlHttpAndNotExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $columnId = 'columnId';
        $order = 'desc';
        $column
            ->method('getId')
            ->willReturn($columnId);

        $this->grid->setDefaultOrder($columnId, $order);

        $page = 1;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);

        $column
            ->expects($this->never())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->once())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testSetDefaultLimitRaiseExceptionIfLimitIsNotAPositiveDuringRedirect(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(Grid::DEFAULT_LIMIT_NOT_VALID_EX_MSG);

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->grid->setDefaultLimit(-1);

        $this->grid->isReadyForRedirect();
    }

    public function testSetDefaultLimitRaiseExceptionIfLimitIsNotDefinedInGridLimitsDuringRedirect(): void
    {
        $limit = 2;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(\sprintf(Grid::LIMIT_NOT_DEFINED_EX_MSG, $limit));

        $source = $this->createMock(Source::class);
        $this->grid->setSource($source);

        $this->grid->setDefaultLimit($limit);

        $this->grid->isReadyForRedirect();
    }

    public function testSetDefaultLimitIfNotSessionDataDuringHandleRedirect(): void
    {
        $this->mockDefaultLimit();

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testSetDefaultLimitIfRequestDataXmlHttpRequestAndNotExportDuringHandleRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $limit = 2;
        $this->grid->setLimits([$limit => "$limit"]);
        $this->grid->setDefaultLimit($limit);

        $page = 1;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);
        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with(...self::withConsecutive(
                [$this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]],
                [$this->gridHash, [Grid::REQUEST_QUERY_LIMIT => $limit, Grid::REQUEST_QUERY_PAGE => $page]],
                [$this->gridHash, [Grid::REQUEST_QUERY_LIMIT => $limit, Grid::REQUEST_QUERY_PAGE => $page]]
            ));

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultLimitIfHasRequestDataNotXmlHttpButExportDuringHandleRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $limit = 2;
        $this->grid->setLimits([$limit => "$limit"]);
        $this->grid->setDefaultLimit($limit);

        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_EXPORT => 0]);

        $this->grid->addExport($this->createMock(Export::class));

        $this
            ->session
            ->expects($this->never())
            ->method('set');

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testNotSetDefaultLimitIfHasRequestDataNotXmlHttpAndNotExportDuringHandleRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $limit = 2;
        $this->grid->setLimits([$limit => "$limit"]);
        $this->grid->setDefaultLimit($limit);

        $page = 1;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $page]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testProcessDefaultTweaksIfNotRequestDataDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        [$group, $tweakId] = $this->arrangeDefaultTweaks(1);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, ['tweaks' => [$group => $tweakId], Grid::REQUEST_QUERY_PAGE => 1]);

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testProcessDefaultTweaksIfRequestDataXmlHttpRequestAndNotExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $tweakPage = 1;
        [$group, $tweakId] = $this->arrangeDefaultTweaks($tweakPage);

        $requestPage = 2;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $requestPage]);
        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with(...self::withConsecutive(
                [$this->gridHash, [Grid::REQUEST_QUERY_PAGE => $requestPage]],
                [$this->gridHash, ['tweaks' => [$group => $tweakId], Grid::REQUEST_QUERY_PAGE => $tweakPage]],
                [$this->gridHash, ['tweaks' => [$group => $tweakId], Grid::REQUEST_QUERY_PAGE => $tweakPage]],
                [$this->gridHash, ['tweaks' => [$group => $tweakId], Grid::REQUEST_QUERY_PAGE => $tweakPage]]
            ));

        $this->assertFalse($this->grid->isReadyForRedirect());
    }

    public function testNotProcessDefaultTweaksIfHasRequestDataNotXmlHttpButExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->arrangeDefaultTweaks(1);

        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_EXPORT => 0]);

        $this->grid->addExport($this->createMock(Export::class));

        $this
            ->session
            ->expects($this->never())
            ->method('set');

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testNotProcessDefaultTweaksIfHasRequestDataNotXmlHttpAndNotExportDuringRedirect(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->arrangeDefaultTweaks(1);

        $requestPage = 2;
        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => $requestPage]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $requestPage]);

        $this->assertTrue($this->grid->isReadyForRedirect());
    }

    public function testGetGridRedirectResponse(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $this
            ->request
            ->method('get')
            ->willReturn([Grid::REQUEST_QUERY_PAGE => 10]);

        $this->grid->setRouteUrl('aRouteUrl');

        $this->assertInstanceOf(RedirectResponse::class, $this->grid->getGridResponse());
    }

    public function testGetGridExportResponse(): void
    {
        $exportResponse = $this->mockExports();
        $this->grid->setRouteUrl('banana');

        $this->assertEquals($exportResponse, $this->grid->getGridResponse());
    }

    public function testGetGridMassActionCallbackRedirectResponse(): void
    {
        $response = $this->mockMassActionCallbackResponse();

        $this->assertEquals($response, $this->grid->getGridResponse());
    }

    public function testGetGridMassActionControllerResponse(): void
    {
        $response = $this->mockMassActionControllerResponse();

        $this->assertEquals($response, $this->grid->getGridResponse());
    }

    public function testGetGridWithoutParams(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->assertEquals(['grid' => $this->grid], $this->grid->getGridResponse());
    }

    public function testGetGridWithoutView(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $param1 = 'foo';
        $param2 = 'bar';
        $params = [$param1, $param2];
        $this->assertEquals(['grid' => $this->grid, $param1, $param2], $this->grid->getGridResponse($params));
    }

    public function testGetGridWithViewWithoutParams(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $view = 'aView';

        $this
            ->engine
            ->method('render')
            ->with($view, ['grid' => $this->grid])
            ->willReturn('string');

        $response = $this->grid->getGridResponse($view);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('string', $response->getContent());
    }

    public function testGetGridWithViewWithViewAndParams(): void
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $view = 'aView';

        $param1 = 'foo';
        $param2 = 'bar';
        $params = [$param1, $param2];

        $this
            ->engine
            ->method('render')
            ->with($view, ['grid' => $this->grid, $param1, $param2])
            ->willReturn('string');

        $response = $this->grid->getGridResponse($view, $params);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('string', $response->getContent());
    }

    protected function setUp(): void
    {
        $this->arrange($this->createMock(GridConfigInterface::class));
    }

    private function arrange($gridConfigInterface = null, $id = 'id', $httpKernel = null)
    {
        $session = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->session = $session;

        $this->request = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->request
            ->method('getSession')
            ->willReturn($session);
        $this->request->headers = $this
            ->getMockBuilder(HeaderBag::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->request->attributes = new ParameterBag([]);

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->router = $this
            ->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();

        $authChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->authChecker = $authChecker;

        $engine = $this->createMock(Environment::class);
        $this->engine = $engine;

        $registry = $this->createMock(ManagerRegistry::class);
        $manager = $this->createMock(Manager::class);
        $this->router = $this->createMock(RouterInterface::class);

        $this->gridId = $id;
        $this->gridHash = 'grid_'.$this->gridId;

        $httpKernel = $httpKernel ?? $this->createMock(HttpKernelInterface::class);
        $this->grid = new Grid($this->router, $this->authChecker, $registry, $manager, $httpKernel, $this->engine, $this->requestStack, $this->gridId, $gridConfigInterface);
    }

    private function mockResetGridSessionWhenResetFilterIsPressed()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_RESET => true]);

        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);
        $this->request->headers = $this->createMock(HeaderBag::class);
        $this->request->headers->method('get')->with('referer')->willReturn('aReferer');

        $this
            ->session
            ->expects($this->once())
            ->method('remove')
            ->with($this->gridHash);

        $this->grid->setPersistence(true);
    }

    private function mockNotResetGridSessionWhenSameGridReferer()
    {
        $scheme = 'http';
        $host = 'www.foo.com/';
        $basUrl = 'baseurl';
        $pathInfo = '/info';

        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this
            ->request
            ->method('isXmlHttpRequest')
            ->willReturn(true);
        $this
            ->request
            ->method('getScheme')
            ->willReturn($scheme);
        $this
            ->request
            ->method('getHttpHost')
            ->willReturn($host);
        $this
            ->request
            ->method('getBaseUrl')
            ->willReturn($basUrl);
        $this
            ->request
            ->method('getPathInfo')
            ->willReturn($pathInfo);

        $this->request->headers = $this->createMock(HeaderBag::class);
        $this->request->headers->method('get')->with('referer')->willReturn($scheme.'//'.$host.$basUrl.$pathInfo);

        $this
            ->session
            ->expects($this->never())
            ->method('remove')
            ->with($this->gridHash);
    }

    private function mockHiddenColumns()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $column1Id = 'col1Id';
        $column
            ->method('getId')
            ->willReturn($column1Id);

        $column2Id = 'col2Id';
        $column2 = $this->stubColumn($column2Id);
        $this->grid->addColumn($column2);

        $this->grid->setHiddenColumns([$column1Id, $column2Id]);

        $column
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(false);

        $column2
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(false);
    }

    private function mockVisibleColumns()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $column1Id = 'col1Id';
        $column
            ->method('getId')
            ->willReturn($column1Id);

        $column2Id = 'col2Id';
        $column2 = $this->stubColumn($column2Id);
        $this->grid->addColumn($column2);

        $column3Id = 'col3Id';
        $column3 = $this->stubColumn($column3Id);
        $this->grid->addColumn($column3);

        $this->grid->setVisibleColumns([$column1Id]);

        $column2
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(false);

        $column3
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(false);
    }

    private function mockColumnVisibility()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $column1Id = 'col1Id';
        $column
            ->method('getId')
            ->willReturn($column1Id);

        $column2Id = 'col2Id';
        $column2 = $this->stubColumn($column2Id);
        $this->grid->addColumn($column2);

        $column3Id = 'col3Id';
        $column3 = $this->stubColumn($column3Id);
        $this->grid->addColumn($column3);

        $column4Id = 'col4Id';
        $column4 = $this->stubColumn($column4Id);
        $this->grid->addColumn($column4);

        $this->grid->showColumns([$column1Id, $column2Id]);
        $this->grid->hideColumns([$column3Id, $column4Id]);

        $column
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(true);

        $column2
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(true);

        $column3
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(false);

        $column4
            ->expects($this->atLeastOnce())
            ->method('setVisible')
            ->with(false);
    }

    private function mockResetPageAndLimitIfMassActionAndAllKeys()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_MASS_ACTION => 0,
            Grid::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED => true,
        ]);

        $massAction = $this->stubMassActionWithCallback(static function() {
        });
        $this->grid->addMassAction($massAction);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);
    }

    /**
     * @return MockObject
     */
    private function mockMassActionCallbackResponse()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $callbackResponse = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_MASS_ACTION => 0]);

        $massAction = $this->stubMassActionWithCallback(
            static function() use ($callbackResponse) {
                return $callbackResponse;
            }
        );
        $this->grid->addMassAction($massAction);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);

        return $callbackResponse;
    }

    /**
     * @return MockObject
     */
    private function mockMassActionControllerResponse()
    {
        $httpKernel = $this
            ->getMockBuilder(HttpKernel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $subRequest = $this
            ->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->getMock();

        $callbackResponse = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $httpKernel
            ->method('handle')
            ->with($subRequest, HttpKernelInterface::SUB_REQUEST)
            ->willReturn($callbackResponse);

        $this->arrange(null, 'id', $httpKernel);

        $rows = new Rows();

        $rowPrimaryFieldValue = 'pfv1';
        $row = $this->createMock(Row::class);
        $row
            ->method('getPrimaryFieldValue')
            ->willReturn($rowPrimaryFieldValue);
        $rows->addRow($row);

        $rowPrimaryFieldValue2 = 'pfv2';
        $row2 = $this->createMock(Row::class);
        $row2
            ->method('getPrimaryFieldValue')
            ->willReturn($rowPrimaryFieldValue2);
        $rows->addRow($row2);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_MASS_ACTION => 0,
            Grid::REQUEST_QUERY_MASS_ACTION_ALL_KEYS_SELECTED => true,
        ]);

        $controllerCb = 'VendorBundle:Controller:Action';
        $param1 = 'param1';
        $param1Val = 1;
        $param2 = 'param2';
        $param2Val = 2;
        $massAction = $this->stubMassActionWithCallback($controllerCb, [$param1 => $param1Val, $param2 => $param2Val]);

        $this
            ->request
            ->method('duplicate')
            ->with([], null, [
                'primaryKeys' => [$rowPrimaryFieldValue, $rowPrimaryFieldValue2],
                'allPrimaryKeys' => true,
                '_controller' => $controllerCb,
                $param1 => $param1Val,
                $param2 => $param2Val, ]
            )
            ->willReturn($subRequest);

        $this->grid->addMassAction($massAction);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);

        return $callbackResponse;
    }

    private function mockExports()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_EXPORT => 0]);

        $response = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $export = $this->createMock(Export::class);
        $export
            ->method('getResponse')
            ->willReturn($response);

        $this->grid->addExport($export);

        $export
            ->expects($this->once())
            ->method('computeData')
            ->with($this->grid);

        return $response;
    }

    private function mockExportsButNotFiltersPageOrderLimit()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $colData = ['colData'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isFilterable')
            ->willReturn(true);
        $column
            ->method('isSortable')
            ->willReturn(true);

        $limit = 10;
        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_EXPORT => 0,
            Grid::REQUEST_QUERY_ORDER => "$colId|ASC",
            Grid::REQUEST_QUERY_LIMIT => $limit,
            $colId => $colData,
        ]);

        $response = $this
            ->getMockBuilder(Response::class)
            ->disableOriginalConstructor()
            ->getMock();

        $export = $this->createMock(Export::class);
        $export
            ->method('getResponse')
            ->willReturn($response);

        $this->grid->setLimits($limit);

        $this->grid->addExport($export);

        $export
            ->expects($this->once())
            ->method('computeData')
            ->with($this->grid);

        $this
            ->session
            ->expects($this->never())
            ->method('set');
    }

    private function mockTweakReset()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $title = 'aTweak';
        $tweak = ['reset' => 1];
        $tweakId = 'aValidTweakId';

        $this->grid->addTweak($title, $tweak, $tweakId);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('remove')
            ->with($this->gridHash);
    }

    private function mockTweakFilters()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $colFilter = ['from' => 'foo', 'to' => 'bar'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilterType')
            ->willReturn('select');

        $title = 'aTweak';
        $tweak = ['filters' => [$colId => $colFilter]];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, ['tweaks' => [$tweakGroup => $tweakId], $colId => ['from' => ['foo'], 'to' => ['bar']]]);
    }

    private function mockTweakOrder()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $order = 'ASC';
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isSortable')
            ->willReturn(true);

        $title = 'aTweak';
        $tweak = ['order' => "$colId|$order"];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, ['tweaks' => [$tweakGroup => $tweakId], Grid::REQUEST_QUERY_ORDER => "$colId|$order"]);
    }

    private function mockTweakMassAction()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $title = 'aTweak';
        $tweak = ['massAction' => -1];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->never())
            ->method('set');
    }

    private function mockTweakPage()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();
        $column
            ->method('isSortable')
            ->willReturn(true);

        $title = 'aTweak';
        $page = 10;
        $tweak = ['page' => $page];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, ['tweaks' => [$tweakGroup => $tweakId], Grid::REQUEST_QUERY_PAGE => $page]);
    }

    private function mockTweakLimit()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();
        $column
            ->method('isSortable')
            ->willReturn(true);

        $title = 'aTweak';
        $limit = 10;
        $tweak = ['limit' => $limit];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->grid->setLimits([$limit]);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, ['tweaks' => [$tweakGroup => $tweakId], Grid::REQUEST_QUERY_LIMIT => $limit]);
    }

    private function mockTweakExport()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $title = 'aTweak';
        $tweak = ['export' => -1];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->never())
            ->method('set');
    }

    private function mockTweakExportButNotFiltersPageOrderLimit()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $colData = ['colData'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isFilterable')
            ->willReturn(true);
        $column
            ->method('isSortable')
            ->willReturn(true);

        $title = 'aTweak';
        $tweak = ['export' => -1];
        $tweakId = 'aValidTweakId';
        $tweakGroup = 'tweakGroup';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_TWEAK => $tweakId,
            Grid::REQUEST_QUERY_ORDER => "$colId|ASC",
            Grid::REQUEST_QUERY_LIMIT => 10,
            $colId => $colData,
        ]);

        $this
            ->session
            ->expects($this->never())
            ->method('set');
    }

    private function mockRemoveActiveTweakGroups()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $order = 'ASC';
        $colFilter = ['from' => 'foo', 'to' => 'bar'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilterType')
            ->willReturn('select');

        $title = 'aTweak';
        $tweakGroup = 'tweakGroup';
        $page = 10;
        $limit = 15;
        $tweak = [
            'filters' => [$colId => $colFilter],
            'order' => "$colId|$order",
            'removeActiveTweaksGroups' => $tweakGroup,
            'page' => $page,
            'limit' => $limit,
        ];
        $tweakId = 'aValidTweakId';

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->grid->setLimits($limit);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [
                'tweaks' => [],
                $colId => ['from' => ['foo'], 'to' => ['bar']],
                Grid::REQUEST_QUERY_PAGE => $page,
                Grid::REQUEST_QUERY_LIMIT => $limit,
            ]);
    }

    private function mockRemoveActiveTweak()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $order = 'ASC';
        $colFilter = ['from' => 'foo', 'to' => 'bar'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilterType')
            ->willReturn('select');

        $title = 'aTweak';
        $tweakGroup = 'tweakGroup';
        $tweakId = 'aValidTweakId';
        $page = 10;
        $limit = 15;
        $tweak = [
            'filters' => [$colId => $colFilter],
            'order' => "$colId|$order",
            'removeActiveTweaks' => $tweakId,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->grid->setLimits($limit);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [
                'tweaks' => [],
                $colId => ['from' => ['foo'], 'to' => ['bar']],
                Grid::REQUEST_QUERY_PAGE => $page,
                Grid::REQUEST_QUERY_LIMIT => $limit,
            ]);
    }

    private function mockAddActiveTweak()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $order = 'ASC';
        $colFilter = ['from' => 'foo', 'to' => 'bar'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('getFilterType')
            ->willReturn('select');

        $title = 'aTweak';
        $tweakGroup = 'tweakGroup';
        $tweakId = 'aValidTweakId';
        $page = 10;
        $limit = 15;
        $tweak = [
            'filters' => [$colId => $colFilter],
            'order' => "$colId|$order",
            'addActiveTweaks' => $tweakId,
            'page' => $page,
            'limit' => $limit,
        ];

        $this->grid->addTweak($title, $tweak, $tweakId, $tweakGroup);

        $this->grid->setLimits($limit);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_TWEAK => $tweakId]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [
                'tweaks' => [$tweakGroup => $tweakId],
                $colId => ['from' => ['foo'], 'to' => ['bar']],
                Grid::REQUEST_QUERY_PAGE => $page,
                Grid::REQUEST_QUERY_LIMIT => $limit,
            ]);
    }

    private function mockPageRequestData()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $page = 2;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_PAGE => $page]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);
    }

    private function mockPageQueryOrderRequestData()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->stubPrimaryColumn();
        $column
            ->method('getId')
            ->willReturn('order');

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);
        $this->grid->setColumns($columns);

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_ORDER => 'order|foo',
            Grid::REQUEST_QUERY_PAGE => 2,
        ]);

        $column
            ->expects($this->never())
            ->method('setOrder');

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);
    }

    private function mockPageLimitRequestData()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_LIMIT => 50,
            Grid::REQUEST_QUERY_PAGE => 2,
        ]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);
    }

    private function mockPageMassActionRequestData()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $massAction = $this->stubMassActionWithCallback(static function() {
        });
        $this->grid->addMassAction($massAction);

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_MASS_ACTION => 0,
            Grid::REQUEST_QUERY_PAGE => 2,
        ]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);
    }

    private function mockPageFiltersRequestData()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $colData = ['colData'];
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isFilterable')
            ->willReturn(true);

        $this->stubRequestWithData([
            Grid::REQUEST_QUERY_PAGE => 2,
            $colId => $colData,
        ]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [$colId => $colData, Grid::REQUEST_QUERY_PAGE => 0]);
    }

    private function mockPageNotSelectFilterRequestData()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isFilterable')
            ->willReturn(true);
        $column
            ->method('getFilterType')
            ->willReturn('differentThanSelect');

        $page = 2;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_PAGE => $page]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);
    }

    private function mockPageColumnNotSelectMultiRequestData()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);

        $column = $this->arrangeGridPrimaryColumn();

        $colId = 'colId';
        $column
            ->method('getId')
            ->willReturn($colId);
        $column
            ->method('isFilterable')
            ->willReturn(true);
        $column
            ->method('getFilterType')
            ->willReturn('select');
        $column
            ->method('getSelectMulti')
            ->willReturn(false);

        $page = 2;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_PAGE => $page]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => $page]);
    }

    /**
     * @param string $columnId
     * @param string $order
     *
     * @return MockObject
     */
    private function mockOrderRequestData($columnId, $order)
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->stubPrimaryColumn();
        $column
            ->method('getId')
            ->willReturn($columnId);
        $column
            ->method('isSortable')
            ->willReturn(true);

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);
        $this->grid->setColumns($columns);

        $queryOrder = "$columnId|$order";
        $this->stubRequestWithData([Grid::REQUEST_QUERY_ORDER => $queryOrder]);

        return $column;
    }

    private function mockOrderColumnNotSortable()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $columnId = 'columnId';

        $column = $this->stubPrimaryColumn();
        $column
            ->method('getId')
            ->willReturn($columnId);
        $column
            ->method('isSortable')
            ->willReturn(false);

        $columns = new Columns($this->authChecker);
        $columns->addColumn($column);
        $this->grid->setColumns($columns);

        $this->stubRequestWithData([Grid::REQUEST_QUERY_ORDER => $columnId.'|asc']);

        $column
            ->expects($this->never())
            ->method('setOrder');

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);
    }

    private function mockConfiguredLimitRequestData()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $limit = 10;
        $this->stubRequestWithData([Grid::REQUEST_QUERY_LIMIT => $limit]);

        $this->grid->setLimits($limit);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_LIMIT => $limit, Grid::REQUEST_QUERY_PAGE => 0]);
    }

    private function mockNonConfiguredLimitRequestData()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $this->stubRequestWithData([Grid::REQUEST_QUERY_LIMIT => 10]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 0]);
    }

    private function mockDefaultSessionFiltersWithoutRequestData()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $col1Id = 'col1';
        $col2Id = 'col2';
        $col3Id = 'col3';
        $col4Id = 'col4';
        $col5Id = 'col5';

        $col1FilterValue = 'val1';
        $col2FilterValue = ['val2'];

        $col5From = 'foo';
        $col5To = 'bar';

        [$column1, $column2, $column3, $column4, $column5] = $this->arrangeColumnsFilters(
            $col1Id,
            $col2Id,
            $col3Id,
            $col4Id,
            $col5Id,
            $col1FilterValue,
            $col2FilterValue,
            $col5From,
            $col5To
        );

        $column
            ->expects($this->never())
            ->method('setData')
            ->with($this->anything());
        $column1
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => $col1FilterValue]);
        $column2
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => $col2FilterValue]);
        $column3
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => 1]);
        $column4
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => 0]);
        $column5
            ->expects($this->once())
            ->method('setData')
            ->with(['from' => [$col5From], 'to' => [$col5To]]);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [
                $col1Id => ['from' => $col1FilterValue],
                $col2Id => ['from' => $col2FilterValue],
                $col3Id => ['from' => 1],
                $col4Id => ['from' => 0],
                $col5Id => ['from' => [$col5From], 'to' => [$col5To]],
            ]);
    }

    private function arrangeColumnsFilters(string $col1Id, string $col2Id, string $col3Id, string $col4Id, string $col5Id, string $col1FilterValue, array $col2FilterValue, string $col5From, string $col5To): array
    {
        $column1 = $this->stubColumn($col1Id);
        $this->grid->addColumn($column1);

        $column2 = $this->stubColumn($col2Id);
        $this->grid->addColumn($column2);

        $col3FilterValue = ['from' => true];
        $column3 = $this->stubColumn($col3Id);
        $this->grid->addColumn($column3);

        $col4FilterValue = ['from' => false];
        $column4 = $this->stubColumn($col4Id);
        $this->grid->addColumn($column4);

        $col5FilterValue = ['from' => $col5From, 'to' => $col5To];
        $column5 = $this
            ->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column5
            ->method('getId')
            ->willReturn($col5Id);
        $column5
            ->method('getFilterType')
            ->willReturn('select');

        $this->grid->addColumn($column5);

        $this->grid->setDefaultFilters([
            $col1Id => $col1FilterValue,
            $col2Id => $col2FilterValue,
            $col3Id => $col3FilterValue,
            $col4Id => $col4FilterValue,
            $col5Id => $col5FilterValue,
        ]);

        return [$column1, $column2, $column3, $column4, $column5];
    }

    private function mockDefaultPage()
    {
        $row = $this->createMock(Row::class);
        $rows = new Rows();
        $rows->addRow($row);

        $this->arrangeGridSourceDataLoadedWithRows($rows);
        $this->arrangeGridPrimaryColumn();

        $this->grid->setDefaultPage(2);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_PAGE => 1]);
    }

    /**
     * @param string $order
     */
    private function mockDefaultOrder($order)
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();

        $column = $this->arrangeGridPrimaryColumn();

        $columnId = 'columnId';
        $column
            ->method('getId')
            ->willReturn('columnId');

        $this->grid->setDefaultOrder($columnId, $order);

        $column
            ->expects($this->once())
            ->method('setOrder')
            ->with($order);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_ORDER => "$columnId|$order"]);
    }

    private function mockDefaultLimit()
    {
        $this->arrangeGridSourceDataLoadedWithEmptyRows();
        $this->arrangeGridPrimaryColumn();

        $limit = 2;
        $this->grid->setLimits([$limit => "$limit"]);
        $this->grid->setDefaultLimit($limit);

        $this
            ->session
            ->expects($this->atLeastOnce())
            ->method('set')
            ->with($this->gridHash, [Grid::REQUEST_QUERY_LIMIT => $limit]);
    }

    /**
     * @param int    $totalCount
     * @param string $sourceHash
     *
     * @return MockObject
     */
    private function arrangeGridSourceDataLoadedWithEmptyRows($totalCount = 0, $sourceHash = null)
    {
        $source = $this->createMock(Source::class);
        $source
            ->method('isDataLoaded')
            ->willReturn(true);
        $source
            ->method('executeFromData')
            ->willReturn(new Rows());
        $source
            ->method('getTotalCountFromData')
            ->willReturn($totalCount);
        $source
            ->method('getHash')
            ->willReturn($sourceHash);

        $this->grid->setSource($source);

        return $source;
    }

    /**
     * @param int $totalCount
     */
    private function arrangeGridSourceDataLoadedWithRows(Rows $rows, $totalCount = 0)
    {
        $source = $this->createMock(Source::class);
        $source
            ->method('isDataLoaded')
            ->willReturn(true);
        $source
            ->method('executeFromData')
            ->willReturn($rows);
        $source
            ->method('getTotalCountFromData')
            ->willReturn($totalCount);

        $this->grid->setSource($source);
    }

    /**
     * @param int $totalCount
     *
     * @return MockObject
     */
    private function arrangeGridSourceDataLoadedWithoutRowsReturned($totalCount = 0)
    {
        $source = $this->createMock(Source::class);
        $source
            ->method('isDataLoaded')
            ->willReturn(true);
        $source
            ->method('getTotalCountFromData')
            ->willReturn($totalCount);

        $this->grid->setSource($source);

        return $source;
    }

    /**
     * @return MockObject
     */
    private function arrangeGridSourceDataNotLoadedWithoutRowsReturned()
    {
        $source = $this->createMock(Source::class);
        $source
            ->method('isDataLoaded')
            ->willReturn(false);
        $source
            ->method('getTotalCount')
            ->willReturn(0);

        $this->grid->setSource($source);

        return $source;
    }

    /**
     * @param int $totalCount
     *
     * @return MockObject
     */
    private function arrangeGridSourceDataNotLoadedWithEmptyRows($totalCount = 0)
    {
        $source = $this->createMock(Source::class);
        $source
            ->method('isDataLoaded')
            ->willReturn(false);
        $source
            ->method('getTotalCount')
            ->willReturn($totalCount);
        $source
            ->method('execute')
            ->willReturn(new Rows());

        $this->grid->setSource($source);

        return $source;
    }

    /**
     * @return MockObject
     */
    private function arrangeGridPrimaryColumn()
    {
        $column = $this->stubPrimaryColumn();
        $this->grid->addColumn($column);

        return $column;
    }

    /**
     * @return MockObject|Column
     */
    private function stubPrimaryColumn()
    {
        $column = $this
            ->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column
            ->method('isPrimary')
            ->willReturn(true);

        return $column;
    }

    /**
     * @param string $columnId
     *
     * @return MockObject
     */
    private function stubFilteredColumn($columnId = null)
    {
        $column = $this->stubColumn($columnId);
        $column
            ->method('isFiltered')
            ->willReturn(true);

        return $column;
    }

    /**
     *
     * @return MockObject
     */
    private function stubTitledColumn($columnId = null)
    {
        $column = $this->stubColumn($columnId);
        $column
            ->method('getTitle')
            ->willReturn('title');

        return $column;
    }

    /**
     * @param string $type
     *
     * @return MockObject
     */
    private function stubFilterableColumn($type, $columnId = null)
    {
        $column = $this->stubColumn($columnId);
        $column
            ->method('isFilterable')
            ->willReturn(true);
        $column
            ->method('getType')
            ->willReturn($type);

        return $column;
    }

    /**
     * @param string $defaultOp
     *
     * @return MockObject
     */
    private function stubColumnWithDefaultOperator($defaultOp, $columnId = null)
    {
        $column = $this->stubColumn($columnId);
        $column
            ->method('getDefaultOperator')
            ->willReturn($defaultOp);

        return $column;
    }

    /**
     *
     * @return MockObject|Column
     */
    private function stubColumn($columnId = null)
    {
        $column = $this
            ->getMockBuilder(Column::class)
            ->disableOriginalConstructor()
            ->getMock();
        $column
            ->method('getId')
            ->willReturn($columnId);

        return $column;
    }

    /**
     * @return MockObject|Columns
     */
    private function arrangeGridWithColumnsIterator()
    {
        $column = $this->stubColumn('primaryID');

        $columnIterator = $this
            ->getMockBuilder(ColumnsIterator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $columns = $this
            ->getMockBuilder(Columns::class)
            ->disableOriginalConstructor()
            ->getMock();
        $columns
            ->method('getIterator')
            ->willReturn($columnIterator);
        $columns
            ->method('getPrimaryColumn')
            ->willReturn($column);

        $this->grid->setColumns($columns);

        return $columns;
    }

    /**
     *
     * @return MockObject|MassActionInterface
     */
    private function stubMassActionWithCallback($aCallback, array $params = [])
    {
        $massAction = $this->stubMassAction();
        $massAction
            ->method('getCallback')
            ->willReturn($aCallback);
        $massAction
            ->method('getParameters')
            ->willReturn($params);

        return $massAction;
    }

    /**
     * @param string $role
     *
     * @return MockObject|MassActionInterface
     */
    private function stubMassAction($role = null)
    {
        // @todo: It seems that MassActionInterface does not have getRole in it. is that fine?
        $massAction = $this
            ->getMockBuilder(MassAction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $massAction
            ->method('getRole')
            ->willReturn($role);

        return $massAction;
    }

    /**
     * @param string $role
     *
     * @return MockObject|RowActionInterface
     */
    private function stubRowAction($role = null, $colId = null)
    {
        // @todo: It seems that RowActionInterface does not have getRole in it. is that fine?
        $rowAction = $this
            ->getMockBuilder(RowAction::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rowAction
            ->method('getRole')
            ->willReturn($role);
        $rowAction
            ->method('getColumn')
            ->willReturn($colId);

        return $rowAction;
    }

    private function stubRequestWithData(array $requestData): void
    {
        $this
            ->request
            ->method('get')
            ->with($this->gridHash)
            ->willReturn($requestData);
    }

    private function arrangeDefaultTweaks(int $tweakPage): array
    {
        $group = 'aGroup';
        $title = 'aTweak';
        $tweak = ['page' => $tweakPage, 'group' => $group];
        $tweakId = 'aValidTweakId';

        $this->grid->addTweak($title, $tweak, $tweakId);

        $this->grid->setDefaultTweak($tweakId);

        return [$group, $tweakId];
    }
}

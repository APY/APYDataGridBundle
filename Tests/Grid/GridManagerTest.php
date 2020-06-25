<?php

namespace APY\DataGridBundle\Grid\Tests;

use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\GridManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Container;
use Twig\Environment;

class GridManagerTest extends TestCase
{
    /**
     * @var GridManager
     */
    private $gridManager;

    /**
     * @var MockObject
     */
    private $container;

    public function testGetIterator()
    {
        $this->assertInstanceOf(\SplObjectStorage::class, $this->gridManager->getIterator());
    }

    public function testCreateGridWithoutId()
    {
        $grid = $this->createMock(Grid::class);
        $this
            ->container
            ->method('get')
            ->with('grid')
            ->willReturn($grid);

        $grids = new \SplObjectStorage();
        $grids->attach($grid);

        $grid
            ->expects($this->never())
            ->method('setId');

        $this->assertEquals($grid, $this->gridManager->createGrid());

        $this->assertAttributeEquals($grids, 'grids', $this->gridManager);
    }

    public function testCreateGridWithId()
    {
        $grid = $this->createMock(Grid::class);
        $this
            ->container
            ->method('get')
            ->with('grid')
            ->willReturn($grid);

        $grids = new \SplObjectStorage();
        $grids->attach($grid);

        $gridId = 'gridId';
        $grid
            ->expects($this->atLeastOnce())
            ->method('setId')
            ->with($gridId);

        $this->assertEquals($grid, $this->gridManager->createGrid($gridId));

        $this->assertAttributeEquals($grids, 'grids', $this->gridManager);
    }

    public function testReturnsManagedGridCount()
    {
        $grid = $this->createMock(Grid::class);
        $this
            ->container
            ->method('get')
            ->with('grid')
            ->willReturn($grid);

        $this->gridManager->createGrid();

        $this->assertEquals(1, $this->gridManager->count());
    }

    public function testSetRouteUrl()
    {
        $routeUrl = 'aRouteUrl';
        $this->gridManager->setRouteUrl($routeUrl);

        $this->assertAttributeEquals($routeUrl, 'routeUrl', $this->gridManager);
    }

    public function testGetRouteUrl()
    {
        $routeUrl = 'aRouteUrl';
        $this->gridManager->setRouteUrl($routeUrl);

        $this->assertEquals($routeUrl, $this->gridManager->getRouteUrl());
    }

    public function testItThrowsExceptionWhenCheckForRedirectAndGridsNotSetted()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::NO_GRID_EX_MSG);

        $this->gridManager->isReadyForRedirect();
    }

    public function testItThrowsExceptionWhenTwoDifferentGridsReturnsSameHashDuringCheckForRedirect()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::SAME_GRID_HASH_EX_MSG);

        $sameHash = 'hashValue';

        $this->stubTwoGridsForRedirect($sameHash, null, null, $sameHash, null, null);

        $this->gridManager->isReadyForRedirect();
    }

    public function testNoGridsReadyForRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForRedirect($grid1Hash, null, false, $grid2Hash, null, false);

        $this->assertFalse($this->gridManager->isReadyForRedirect());
    }

    public function testAtLeastOneGridReadyForRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForRedirect($grid1Hash, null, false, $grid2Hash, null, true);

        $this->assertTrue($this->gridManager->isReadyForRedirect());
    }

    public function testItRewindGridListWhenCheckingTwoTimesIfReadyForRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $grid2 = $this->createMock(Grid::class);
        $grid2
            ->method('getHash')
            ->willReturn($grid2Hash);

        $this
            ->container
            ->method('get')
            ->with('grid')
            ->willReturnOnConsecutiveCalls($grid, $grid2);

        $grid
            ->expects($this->exactly(2))
            ->method('isReadyForRedirect');
        $grid2
            ->expects($this->exactly(2))
            ->method('isReadyForRedirect');

        $this->gridManager->createGrid();
        $this->gridManager->createGrid();

        $this->gridManager->isReadyForRedirect();
        $this->gridManager->isReadyForRedirect();
    }

    public function testItTakesFirstGridUrlAsGlobalRouteUrl()
    {
        $grid1Hash = 'hashValue1';
        $route1Url = 'route1Url';

        $grid2Hash = 'hashValue2';
        $route2Url = 'route2Url';

        $this->stubTwoGridsForRedirect($grid1Hash, $route1Url, null, $grid2Hash, $route2Url, null);

        $this->gridManager->isReadyForRedirect();

        $this->assertAttributeEquals($route1Url, 'routeUrl', $this->gridManager);
    }

    public function testItIgnoresEveryGridUrlIfRouteUrlAlreadySetted()
    {
        $grid1Hash = 'hashValue1';
        $route1Url = 'route1Url';

        $grid2Hash = 'hashValue2';
        $route2Url = 'route2Url';

        $this->stubTwoGridsForRedirect($grid1Hash, $route1Url, null, $grid2Hash, $route2Url, null);

        $settedRouteUrl = 'settedRouteUrl';
        $this->gridManager->setRouteUrl($settedRouteUrl);

        $this->gridManager->isReadyForRedirect();

        $this->assertAttributeEquals($settedRouteUrl, 'routeUrl', $this->gridManager);
    }

    public function testItThrowsExceptionWhenCheckForExportAndGridsNotSetted()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::NO_GRID_EX_MSG);

        $this->gridManager->isReadyForExport();
    }

    public function testItThrowsExceptionWhenTwoDifferentGridsReturnsSameHashDuringCheckForExport()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::SAME_GRID_HASH_EX_MSG);

        $sameHash = 'hashValue';

        $this->stubTwoGridsForExport($sameHash, null, $sameHash, null);

        $this->gridManager->isReadyForExport();
    }

    public function testNoGridsReadyForExport()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForExport($grid1Hash, false, $grid2Hash, false);

        $this->assertFalse($this->gridManager->isReadyForExport());
    }

    public function testAtLeastOneGridReadyForExport()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        list($grid, $grid2) = $this->stubTwoGridsForExport($grid1Hash, false, $grid2Hash, true);

        $this->assertTrue($this->gridManager->isReadyForExport());

        $this->assertAttributeEquals($grid2, 'exportGrid', $this->gridManager);
    }

    public function testItRewindGridListWhenCheckingTwoTimesIfReadyForExport()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $grid2 = $this->createMock(Grid::class);
        $grid2
            ->method('getHash')
            ->willReturn($grid2Hash);

        $this
            ->container
            ->method('get')
            ->with('grid')
            ->willReturnOnConsecutiveCalls($grid, $grid2);

        $grid
            ->expects($this->exactly(2))
            ->method('isReadyForExport');
        $grid2
            ->expects($this->exactly(2))
            ->method('isReadyForExport');

        $this->gridManager->createGrid();
        $this->gridManager->createGrid();

        $this->gridManager->isReadyForExport();
        $this->gridManager->isReadyForExport();
    }

    public function testNoGridsHasMassActionRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridForMassAction($grid1Hash, false, $grid2Hash, false);

        $this->assertFalse($this->gridManager->isMassActionRedirect());
    }

    public function testAtLeastOneGridHasMassActionRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        list($grid, $grid2) = $this->stubTwoGridForMassAction($grid1Hash, false, $grid2Hash, true);

        $this->assertTrue($this->gridManager->isMassActionRedirect());

        $this->assertAttributeEquals($grid2, 'massActionGrid', $this->gridManager);
    }

    public function testItRewindGridListWhenCheckingTwoTimesIfHasMassActionRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $grid2 = $this->createMock(Grid::class);
        $grid2
            ->method('getHash')
            ->willReturn($grid2Hash);

        $this
            ->container
            ->method('get')
            ->with('grid')
            ->willReturnOnConsecutiveCalls($grid, $grid2);

        $grid
            ->expects($this->exactly(2))
            ->method('isMassActionRedirect');
        $grid2
            ->expects($this->exactly(2))
            ->method('isMassActionRedirect');

        $this->gridManager->createGrid();
        $this->gridManager->createGrid();

        $this->gridManager->isMassActionRedirect();
        $this->gridManager->isMassActionRedirect();
    }

    public function testGridResponseRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForRedirect($grid1Hash, null, false, $grid2Hash, null, true);

        $routeUrl = 'aRouteUrl';
        $this->gridManager->setRouteUrl($routeUrl);

        $this->assertEquals($routeUrl, $this->gridManager->getGridManagerResponse()->getTargetUrl());
    }

    public function testGridResponseExport()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        list($grid, $grid2) = $this->stubTwoGridsForExport($grid1Hash, false, $grid2Hash, true);

        $response = new Response();
        $grid2
            ->method('getExportResponse')
            ->willReturn($response);

        $this->assertEquals($response, $this->gridManager->getGridManagerResponse());
    }

    public function testGridResponseMassActionRedirect()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        list($grid, $grid2) = $this->stubTwoGridForMassAction($grid1Hash, false, $grid2Hash, true);

        $response = new Response();
        $grid2
            ->method('getMassActionResponse')
            ->willReturn($response);

        $this->assertEquals($response, $this->gridManager->getGridManagerResponse());
    }

    public function testGetGridResponseWithoutParams()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        list($grid, $grid2) = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $this->assertEquals(['grid1' => $grid, 'grid2' => $grid2], $this->gridManager->getGridManagerResponse());
    }

    public function testGetGridResponseWithoutView()
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        list($grid, $grid2) = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $param1 = 'foo';
        $param2 = 'bar';
        $params = [$param1, $param2];
        $this->assertEquals(['grid1' => $grid, 'grid2' => $grid2, $param1, $param2], $this->gridManager->getGridManagerResponse($params));
    }

    public function testGetGridWithViewWithoutParams()
    {
        $grid1Hash = 'hashValue1';

        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $twig = $this->createMock(Environment::class);

        $containerGetMap = [
            ['grid', Container::EXCEPTION_ON_INVALID_REFERENCE, $grid],
            ['twig', Container::EXCEPTION_ON_INVALID_REFERENCE, $twig],
        ];

        $this
            ->container
            ->method('get')
            ->will($this->returnValueMap($containerGetMap));

        $this->gridManager->createGrid();

        $view = 'aView';

        $response = 'some content';
        $twig
            ->method('render')
            ->with($view, ['grid1' => $grid])
            ->willReturn($response);

        $this->assertEquals($response, $this->gridManager->getGridManagerResponse($view)->getContent());
    }

    public function testGetGridWithViewWithViewAndParams()
    {
        $grid1Hash = 'hashValue1';

        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $twig = $this->createMock(Environment::class);

        $containerGetMap = [
            ['grid', Container::EXCEPTION_ON_INVALID_REFERENCE, $grid],
            ['twig', Container::EXCEPTION_ON_INVALID_REFERENCE, $twig],
        ];

        $this
            ->container
            ->method('get')
            ->will($this->returnValueMap($containerGetMap));

        $this->gridManager->createGrid();

        $view = 'aView';

        $param1 = 'foo';
        $param2 = 'bar';
        $params = [$param1, $param2];

        $response = 'some content';
        $twig
            ->method('render')
            ->with($view, ['grid1' => $grid, $param1, $param2])
            ->willReturn($response);

        $this->assertEquals($response, $this->gridManager->getGridManagerResponse($view, $params)->getContent());
    }

    public function setUp()
    {
        $this->container = $this->createMock(Container::class);
        $this->gridManager = new GridManager($this->container);
    }

    /**
     * @param string $grid1Hash
     * @param string $route1Url
     * @param bool   $grid1ReadyForRedirect
     * @param string $grid2Hash
     * @param string $route2Url
     * @param bool   $grid2ReadyForRedirect
     */
    private function stubTwoGridsForRedirect(
        $grid1Hash,
        $route1Url,
        $grid1ReadyForRedirect,
        $grid2Hash,
        $route2Url,
        $grid2ReadyForRedirect
    ) {
        list($grid, $grid2) = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $grid
            ->method('isReadyForRedirect')
            ->willReturn($grid1ReadyForRedirect);
        $grid
            ->method('getRouteUrl')
            ->willReturn($route1Url);

        $grid2
            ->method('isReadyForRedirect')
            ->willReturn($grid2ReadyForRedirect);
        $grid2
            ->method('getRouteUrl')
            ->willReturn($route2Url);
    }

    /**
     * @param string $grid1Hash
     * @param bool   $grid1ReadyForExport
     * @param string $grid2Hash
     * @param bool   $grid2ReadyForExport
     *
     * @return array
     */
    private function stubTwoGridsForExport($grid1Hash, $grid1ReadyForExport, $grid2Hash, $grid2ReadyForExport)
    {
        list($grid, $grid2) = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $grid
            ->method('isReadyForExport')
            ->willReturn($grid1ReadyForExport);

        $grid2
            ->method('isReadyForExport')
            ->willReturn($grid2ReadyForExport);

        return [$grid, $grid2];
    }

    /**
     * @param string $grid1Hash
     * @param bool   $grid1IsMassActionRedirect
     * @param string $grid2Hash
     * @param bool   $grid2IsMassActionRedirect
     *
     * @return array
     */
    private function stubTwoGridForMassAction($grid1Hash, $grid1IsMassActionRedirect, $grid2Hash, $grid2IsMassActionRedirect)
    {
        list($grid, $grid2) = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $grid
            ->method('isMassActionRedirect')
            ->willReturn($grid1IsMassActionRedirect);

        $grid2
            ->method('isMassActionRedirect')
            ->willReturn($grid2IsMassActionRedirect);

        return [$grid, $grid2];
    }

    /**
     * @param string $grid1Hash
     * @param string $grid2Hash
     *
     * @return array
     */
    private function stubTwoGrids($grid1Hash, $grid2Hash)
    {
        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $grid2 = $this->createMock(Grid::class);
        $grid2
            ->method('getHash')
            ->willReturn($grid2Hash);

        $this
            ->container
            ->method('get')
            ->with('grid')
            ->willReturnOnConsecutiveCalls($grid, $grid2);

        $this->gridManager->createGrid();
        $this->gridManager->createGrid();

        return [$grid, $grid2];
    }
}

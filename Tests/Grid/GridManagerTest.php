<?php

namespace APY\DataGridBundle\Tests\Grid;

use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\GridManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class GridManagerTest extends TestCase
{
    private GridManager $gridManager;
    private Environment|MockObject $twig;

    public function testGetIterator(): void
    {
        $this->assertInstanceOf(\SplObjectStorage::class, $this->gridManager->getIterator());
    }

    public function testCreateGridWithoutId(): void
    {
        $grid = $this->createMock(Grid::class);

        $grids = new \SplObjectStorage();
        $grids->attach($grid);

        $grid
            ->expects($this->never())
            ->method('setId');

        $this->assertEquals($grid, $this->gridManager->createGrid($grid));
        $this->assertEquals($grids, $this->gridManager->getIterator());
    }

    public function testCreateGridWithId(): void
    {
        $grid = $this->createMock(Grid::class);

        $grids = new \SplObjectStorage();
        $grids->attach($grid);

        $gridId = 'gridId';
        $grid
            ->expects($this->atLeastOnce())
            ->method('setId')
            ->with($gridId);

        $this->assertEquals($grid, $this->gridManager->createGrid($grid, $gridId));
        $this->assertEquals($grids, $this->gridManager->getIterator());
    }

    public function testReturnsManagedGridCount(): void
    {
        $grid = $this->createMock(Grid::class);
        $this->gridManager->createGrid($grid);

        $this->assertEquals(1, $this->gridManager->count());
    }

    public function testGetRouteUrl(): void
    {
        $routeUrl = 'aRouteUrl';
        $this->gridManager->setRouteUrl($routeUrl);

        $this->assertEquals($routeUrl, $this->gridManager->getRouteUrl());
    }

    public function testItThrowsExceptionWhenCheckForRedirectAndGridsNotSetted(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::NO_GRID_EX_MSG);

        $this->gridManager->isReadyForRedirect();
    }

    public function testItThrowsExceptionWhenTwoDifferentGridsReturnsSameHashDuringCheckForRedirect(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::SAME_GRID_HASH_EX_MSG);

        $sameHash = 'hashValue';

        $this->stubTwoGridsForRedirect($sameHash, null, false, $sameHash, null, false);

        $this->gridManager->isReadyForRedirect();
    }

    public function testNoGridsReadyForRedirect(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForRedirect($grid1Hash, null, false, $grid2Hash, null, false);

        $this->assertFalse($this->gridManager->isReadyForRedirect());
    }

    public function testAtLeastOneGridReadyForRedirect(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForRedirect($grid1Hash, null, false, $grid2Hash, null, true);

        $this->assertTrue($this->gridManager->isReadyForRedirect());
    }

    public function testItRewindGridListWhenCheckingTwoTimesIfReadyForRedirect(): void
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

        $grid
            ->expects($this->exactly(2))
            ->method('isReadyForRedirect');
        $grid2
            ->expects($this->exactly(2))
            ->method('isReadyForRedirect');

        $this->gridManager->createGrid($grid);
        $this->gridManager->createGrid($grid2);

        $this->gridManager->isReadyForRedirect();
        $this->gridManager->isReadyForRedirect();
    }

    public function testItTakesFirstGridUrlAsGlobalRouteUrl(): void
    {
        $grid1Hash = 'hashValue1';
        $route1Url = 'route1Url';

        $grid2Hash = 'hashValue2';
        $route2Url = 'route2Url';

        $this->stubTwoGridsForRedirect($grid1Hash, $route1Url, false, $grid2Hash, $route2Url, false);

        $this->gridManager->isReadyForRedirect();

        $this->assertEquals($route1Url, $this->gridManager->getRouteUrl());
    }

    public function testItIgnoresEveryGridUrlIfRouteUrlAlreadySetted(): void
    {
        $grid1Hash = 'hashValue1';
        $route1Url = 'route1Url';

        $grid2Hash = 'hashValue2';
        $route2Url = 'route2Url';

        $this->stubTwoGridsForRedirect($grid1Hash, $route1Url, false, $grid2Hash, $route2Url, false);

        $settedRouteUrl = 'settedRouteUrl';
        $this->gridManager->setRouteUrl($settedRouteUrl);

        $this->gridManager->isReadyForRedirect();

        $this->assertEquals($settedRouteUrl, $this->gridManager->getRouteUrl());
    }

    public function testItThrowsExceptionWhenCheckForExportAndGridsNotSetted(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::NO_GRID_EX_MSG);

        $this->gridManager->isReadyForExport();
    }

    public function testItThrowsExceptionWhenTwoDifferentGridsReturnsSameHashDuringCheckForExport(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(GridManager::SAME_GRID_HASH_EX_MSG);

        $sameHash = 'hashValue';

        $this->stubTwoGridsForExport($sameHash, false, $sameHash, false);

        $this->gridManager->isReadyForExport();
    }

    public function testNoGridsReadyForExport(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForExport($grid1Hash, false, $grid2Hash, false);

        $this->assertFalse($this->gridManager->isReadyForExport());
    }

    public function testAtLeastOneGridReadyForExport(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        [, $grid2] = $this->stubTwoGridsForExport($grid1Hash, false, $grid2Hash, true);

        $this->assertTrue($this->gridManager->isReadyForExport());

        $this->assertEquals($grid2, $this->gridManager->getExportGrid());
    }

    public function testItRewindGridListWhenCheckingTwoTimesIfReadyForExport(): void
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

        $grid
            ->expects($this->exactly(2))
            ->method('isReadyForExport');
        $grid2
            ->expects($this->exactly(2))
            ->method('isReadyForExport');

        $this->gridManager->createGrid($grid);
        $this->gridManager->createGrid($grid2);

        $this->gridManager->isReadyForExport();
        $this->gridManager->isReadyForExport();
    }

    public function testNoGridsHasMassActionRedirect(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridForMassAction($grid1Hash, false, $grid2Hash, false);

        $this->assertFalse($this->gridManager->isMassActionRedirect());
    }

    public function testAtLeastOneGridHasMassActionRedirect(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        [, $grid2] = $this->stubTwoGridForMassAction($grid1Hash, false, $grid2Hash, true);

        $this->assertTrue($this->gridManager->isMassActionRedirect());

        $this->assertEquals($grid2, $this->gridManager->getMassActionGrid());
    }

    public function testItRewindGridListWhenCheckingTwoTimesIfHasMassActionRedirect(): void
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

        $grid
            ->expects($this->exactly(2))
            ->method('isMassActionRedirect');
        $grid2
            ->expects($this->exactly(2))
            ->method('isMassActionRedirect');

        $this->gridManager->createGrid($grid);
        $this->gridManager->createGrid($grid2);

        $this->gridManager->isMassActionRedirect();
        $this->gridManager->isMassActionRedirect();
    }

    public function testGridResponseRedirect(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        $this->stubTwoGridsForRedirect($grid1Hash, null, false, $grid2Hash, null, true);

        $routeUrl = 'aRouteUrl';
        $this->gridManager->setRouteUrl($routeUrl);

        $this->assertEquals($routeUrl, $this->gridManager->getGridManagerResponse()->getTargetUrl());
    }

    public function testGridResponseExport(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        [, $grid2] = $this->stubTwoGridsForExport($grid1Hash, false, $grid2Hash, true);

        $response = new Response();
        $grid2
            ->method('getExportResponse')
            ->willReturn($response);

        $this->assertEquals($response, $this->gridManager->getGridManagerResponse());
    }

    public function testGridResponseMassActionRedirect(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        [, $grid2] = $this->stubTwoGridForMassAction($grid1Hash, false, $grid2Hash, true);

        $response = new Response();
        $grid2
            ->method('getMassActionResponse')
            ->willReturn($response);

        $this->assertEquals($response, $this->gridManager->getGridManagerResponse());
    }

    public function testGetGridResponseWithoutParams(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        [$grid, $grid2] = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $this->assertEquals(['grid1' => $grid, 'grid2' => $grid2], $this->gridManager->getGridManagerResponse());
    }

    public function testGetGridResponseWithoutView(): void
    {
        $grid1Hash = 'hashValue1';
        $grid2Hash = 'hashValue2';

        [$grid, $grid2] = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $param1 = 'foo';
        $param2 = 'bar';
        $params = [$param1, $param2];
        $this->assertEquals(['grid1' => $grid, 'grid2' => $grid2, $param1, $param2], $this->gridManager->getGridManagerResponse($params));
    }

    public function testGetGridWithViewWithoutParams(): void
    {
        $grid1Hash = 'hashValue1';

        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $this->gridManager->createGrid($grid);

        $view = 'aView';

        $this->twig
            ->method('render')
            ->with($view, ['grid1' => $grid])
            ->willReturn('string');

        $response = $this->gridManager->getGridManagerResponse($view);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('string', $response->getContent());
    }

    public function testGetGridWithViewWithViewAndParams(): void
    {
        $grid1Hash = 'hashValue1';

        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $this->gridManager->createGrid($grid);

        $view = 'aView';

        $param1 = 'foo';
        $param2 = 'bar';
        $params = [$param1, $param2];

        $this->twig
            ->method('render')
            ->with($view, ['grid1' => $grid, $param1, $param2])
            ->willReturn('string');

        $response = $this->gridManager->getGridManagerResponse($view, $params);
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('string', $response->getContent());
    }

    protected function setUp(): void
    {
        $this->twig = $this->createMock(Environment::class);
        $this->gridManager = new GridManager($this->twig);
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
        $grid2ReadyForRedirect,
    ) {
        [$grid, $grid2] = $this->stubTwoGrids($grid1Hash, $grid2Hash);

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
        [$grid, $grid2] = $this->stubTwoGrids($grid1Hash, $grid2Hash);

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
        [$grid, $grid2] = $this->stubTwoGrids($grid1Hash, $grid2Hash);

        $grid
            ->method('isMassActionRedirect')
            ->willReturn($grid1IsMassActionRedirect);

        $grid2
            ->method('isMassActionRedirect')
            ->willReturn($grid2IsMassActionRedirect);

        return [$grid, $grid2];
    }

    private function stubTwoGrids(string $grid1Hash, string $grid2Hash): array
    {
        $grid = $this->createMock(Grid::class);
        $grid
            ->method('getHash')
            ->willReturn($grid1Hash);

        $grid2 = $this->createMock(Grid::class);
        $grid2
            ->method('getHash')
            ->willReturn($grid2Hash);

        $this->gridManager->createGrid($grid);
        $this->gridManager->createGrid($grid2);

        return [$grid, $grid2];
    }
}

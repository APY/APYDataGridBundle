<?php

namespace APY\DataGridBundle\Tests\Twig;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Twig\DataGridExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class DataGridExtensionTest.
 *
 *
 * @author Quentin FERRER
 */
class DataGridExtensionTest extends TestCase
{
    /**
     * @var DataGridExtension
     */
    private $extension;

    public function setUp()
    {
        $router = $this->createMock(RouterInterface::class);
        $this->extension = new DataGridExtension($router, '');
    }

    public function testGetGridUrl()
    {
        $baseUrl = 'http://localhost';
        $gridHash = 'my_grid';

        // Creates grid
        $grid = $this->createMock(Grid::class, [], [], '', false);
        $grid->expects($this->any())->method('getRouteUrl')->willReturn($baseUrl);
        $grid->expects($this->any())->method('getHash')->willReturn($gridHash);

        $prefix = $baseUrl . '?' . $gridHash;

        // Creates column
        $column = $this->createMock(Column::class);

        // Limit
        $this->assertEquals($prefix . '[_limit]=', $this->extension->getGridUrl('limit', $grid, $column));

        // Reset
        $this->assertEquals($prefix . '[_reset]=', $this->extension->getGridUrl('reset', $grid, $column));

        // Page
        $this->assertEquals($prefix . '[_page]=2', $this->extension->getGridUrl('page', $grid, 2));

        // Export
        $this->assertEquals($prefix . '[__export_id]=pdf', $this->extension->getGridUrl('export', $grid, 'pdf'));

        // Default order
        $column->expects($this->any())->method('getId')->willReturn('foo');
        $this->assertEquals($prefix . '[_order]=foo|asc', $this->extension->getGridUrl('order', $grid, $column));

        // Order
        $column->expects($this->any())->method('isSorted')->willReturn(true);
        $column->expects($this->any())->method('getOrder')->willReturn('asc');
        $this->assertEquals($prefix . '[_order]=foo|desc', $this->extension->getGridUrl('order', $grid, $column));

        // Unknown section
        $this->assertNull($this->extension->getGridUrl('', $grid, $column));
    }
}

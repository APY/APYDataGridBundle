<?php

namespace APY\DataGridBundle\Tests\Twig;

use APY\DataGridBundle\Grid\Column\ArrayColumn;
use APY\DataGridBundle\Grid\Column\BlankColumn;
use APY\DataGridBundle\Grid\Column\BooleanColumn;
use APY\DataGridBundle\Grid\Column\DateColumn;
use APY\DataGridBundle\Grid\Column\DateTimeColumn;
use APY\DataGridBundle\Grid\Column\JoinColumn;
use APY\DataGridBundle\Grid\Column\NumberColumn;
use APY\DataGridBundle\Grid\Column\RankColumn;
use APY\DataGridBundle\Grid\Column\TimeColumn;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Twig\DataGridExtension;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author Pierre-Louis FORT
 */
class DataGridExtensionFunctionalTest extends KernelTestCase
{
    /**
     * @var DataGridExtension
     */
    private $extension;

    /**
     * @var \Twig_Environment
     */
    private $twig;


    public function setUp()
    {
        ini_set('intl.default_locale', 'en-US');
        $kernel = self::createKernel(['debug' => true]);
        $kernel->boot();
        $this->extension = $kernel->getContainer()->get('grid.twig_extension');
        $this->twig = $kernel->getContainer()->get('twig');
    }


    protected function getMockGridAndInit($id, $hash, $theme = null)
    {
        $grid = $this->getMockBuilder(Grid::class)
            ->disableOriginalConstructor()
            ->getMock();
        $grid->expects($this->any())
            ->method('getHash')
            ->willReturn($hash);
        $grid->expects($this->any())
            ->method('getId')
            ->willReturn($id);
        $this->extension->initGrid($grid, $theme);

        return $grid;
    }

    public function testRenderCellArrayColumn()
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new ArrayColumn();
        $column->setId('array_col');
        $row = new Row();
        $row->setField('array_col', ['foo', 'bar', 'foo_bar']);
        $this->assertRegExp(
            '#foo\s+\<br /\>\s*bar\s+\<br /\>\s*foo_bar#',
            $this->extension->getGridCell($this->twig, $column, $row, $grid)
        );
    }

    public function arrayColumnCustomCellProvider()
    {
        //grid id, column id, theme, expected
        return [

            // grid_column_%column_id%_cell
            [
                '',
                'myarraycolid',
                'theme_column_id.html.twig',
                'grid_column_myarraycolid_cell'
            ],
            // grid_%id%_column_%column_id%_cell
            [
                'mygridid',
                'myarraycolid',
                'theme_column_id.html.twig',
                'grid_mygridid_column_myarraycolid_cell'
            ],

            // grid_column_id_%column_id%_cell
            [
                '',
                'myarraycolid',
                'theme_column_id_colid.html.twig',
                'grid_column_id_myarraycolid_cell'
            ],
            // grid_%id%_column_id_%column_id%_cell
            [
                'mygridid',
                'myarraycolid',
                'theme_column_id_colid.html.twig',
                'grid_mygridid_column_id_myarraycolid_cell'
            ],

            // grid_column_%column_type%_cell
            [
                '',
                'myarraycolid',
                'theme_column_type.html.twig',
                'grid_column_array_cell'
            ],
            // grid_%id%_column_%column_type%_cell
            [
                'mygridid',
                'myarraycolid',
                'theme_column_type.html.twig',
                'grid_mygridid_column_array_cell'
            ],
            // grid_column_type_%column_type%_cell
            [
                '',
                'myarraycolid',
                'theme_column_type_coltype.html.twig',
                'grid_column_type_array_cell'
            ],
            // grid_%id%_column_type_%column_type%_cell
            [
                'mygridid',
                'myarraycolid',
                'theme_column_type_coltype.html.twig',
                'grid_mygridid_column_type_array_cell'
            ],
        ];
    }


    /**
     * @dataProvider arrayColumnCustomCellProvider
     */
    public function testRenderCellArrayColumnCustomCell($gridId, $colId, $theme, $expected)
    {
        $grid = $this->getMockGridAndInit($gridId, 'GRID_HASH', $theme);

        $column = new ArrayColumn();
        $column->setId($colId);
        $row = new Row();
        $row->setField($colId, ['foo', 'bar', 'foo_bar']);
        $this->assertEquals(
            $expected,
            $this->extension->getGridCell($this->twig, $column, $row, $grid)
        );
    }

    public function testRenderCellBlankColumn()
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new BlankColumn(
            [
                'id' => 'blank_col'
            ]
        );
        $this->assertEquals(
            '',
            $this->extension->getGridCell($this->twig, $column, new Row(), $grid)
        );
    }

    public function testRenderCellBooleanColumn()
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new BooleanColumn(
            [
                'id' => 'bool_col'
            ]
        );
        $row = new Row();
        $row->setField('bool_col', true);
        $this->assertEquals(
            '<span class="grid_boolean_true" title="true">true</span>',
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

        $row->setField('bool_col', false);
        $this->assertEquals(
            '<span class="grid_boolean_false" title="false">false</span>',
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );
    }

    public function testRenderCellDateColumnNoFormat()
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new DateColumn(
            [
                'id' => 'date_col',
                'timezone' => "Europe/Paris"
            ]
        );
        $row = new Row();
        $row->setField('date_col', new \DateTime('2017-02-02 23:00:00'));

        $this->assertEquals(
            'Feb 3, 2017',
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }


    public function dateColumnProvider()
    {
        return [
            [null, null, new \DateTime('2017-02-02 23:00:00'), 'Feb 2, 2017'],
            ['d/m/Y', null, new \DateTime('2017-02-02 23:00:00'), '02/02/2017'],
            [null, 'Europe/Paris', new \DateTime('2017-02-02 23:00:00'), 'Feb 3, 2017'],
            ['d/m/Y', 'Europe/Paris', new \DateTime('2017-02-02 23:00:00'), '03/02/2017']
        ];
    }


    /**
     * @dataProvider dateColumnProvider
     */
    public function testRenderCellDateColumn($format, $timezone, $date, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new DateColumn(
            [
                'id' => 'date_col',
                'format' => $format,
                'timezone' => $timezone
            ]
        );
        $row = new Row();
        $row->setField('date_col', $date);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }

    public function dateTimeColumnProvider()
    {
        return [
            [null, null, new \DateTime('2017-02-02 23:00:00'), 'Feb 2, 2017, 11:00:00 PM'],
            ['d/m/Y H:i:s', null, new \DateTime('2017-02-02 23:00:00'), '02/02/2017 23:00:00'],
            [null, 'Europe/Paris', new \DateTime('2017-02-02 23:00:00'), 'Feb 3, 2017, 12:00:00 AM'],
            ['d/m/Y H:i:s', 'Europe/Paris', new \DateTime('2017-02-02 23:00:00'), '03/02/2017 00:00:00']
        ];
    }

    /**
     * @dataProvider dateTimeColumnProvider
     */
    public function testRenderCellDateTimeColumn($format, $timezone, $date, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new DateTimeColumn(
            [
                'id' => 'datetime_col',
                'format' => $format,
                'timezone' => $timezone
            ]
        );

        $row = new Row();
        $row->setField('datetime_col', $date);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }

    public function timeColumnProvider()
    {
        return [
            [null, null, new \DateTime('2017-02-02 23:00:00'), '11:00:00 PM'],
            ['H:i:s', null, new \DateTime('2017-02-02 23:00:00'), '23:00:00'],
            [null, 'Europe/Paris', new \DateTime('2017-02-02 23:00:00'), '12:00:00 AM'],
            ['H:i:s', 'Europe/Paris', new \DateTime('2017-02-02 23:00:00'), '00:00:00']
        ];
    }

    /**
     * @dataProvider timeColumnProvider
     */
    public function testRenderCellTimeColumn($format, $timezone, $date, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new TimeColumn(
            [
                'id' => 'time_col',
                'format' => $format,
                'timezone' => $timezone
            ]
        );

        $row = new Row();
        $row->setField('time_col', $date);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }


    public function numberColumnDecimalProvider()
    {
        return [
            [null, null, 1, false, 1234.55, '1234.6'],
            ['fr_FR', null, 1, false, 1234.54, '1234,5'],
            ['fr_FR', null, 1, true, 1234.55, '1 234,6'],

            ['fr_FR', \NumberFormatter::ROUND_CEILING, 1, false, 1234.68, '1234,7'],
            ['fr_FR', \NumberFormatter::ROUND_CEILING, 1, true, 1234.53, '1 234,6'],
            ['fr_FR', \NumberFormatter::ROUND_CEILING, 1, true, -1234.58, '-1 234,5'],
            ['fr_FR', \NumberFormatter::ROUND_FLOOR, 1, false, 1234.58, '1234,5'],
            ['fr_FR', \NumberFormatter::ROUND_FLOOR, 1, false, -1234.58, '-1234,6'],
            ['fr_FR', \NumberFormatter::ROUND_DOWN, 3, false, 0.5673, '0,567'],
            ['fr_FR', \NumberFormatter::ROUND_DOWN, 3, false, -0.5678, '-0,567'],
            ['fr_FR', \NumberFormatter::ROUND_HALFDOWN, 0, false, 1234.51, '1235'],
            ['fr_FR', \NumberFormatter::ROUND_HALFDOWN, 0, false, 1234.5, '1234'],
            ['fr_FR', \NumberFormatter::ROUND_HALFUP, 0, false, 1234.49, '1234'],
            ['fr_FR', \NumberFormatter::ROUND_HALFUP, 0, false, 1234.5, '1235'],
            ['fr_FR', \NumberFormatter::ROUND_HALFEVEN, 0, false, 1234.5, '1234'],
            ['fr_FR', \NumberFormatter::ROUND_HALFEVEN, 0, false, 1234.56, '1235'],
        ];
    }

    /**
     * @dataProvider numberColumnDecimalProvider
     */
    public function testRenderCellNumberColumnDecimal($locale, $roundingMode, $precision, $grouping, $number, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new NumberColumn(
            [
                'id' => 'number_col',
                'locale' => $locale,
                'style' => 'decimal',
                'roundingMode' => $roundingMode,
                'precision' => $precision,
                'grouping' => $grouping

            ]
        );

        $row = new Row();
        $row->setField('number_col', $number);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );
    }

    public function numberColumnCurrencyProvider()
    {
        return [
            [null, 1, false, 1234.55, '$1234.6'],
            ['fr_FR', 0, false, 1234.54, '1235 €'],
            ['fr_FR', 1, true, 1234.55, '1 234,6 €'],
        ];
    }

    /**
     * @dataProvider numberColumnCurrencyProvider
     */
    public function testRenderCellNumberCurrencyColumn($locale, $precision, $grouping, $number, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new NumberColumn(
            [
                'id' => 'number_col',
                'locale' => $locale,
                'style' => 'currency',
                'precision' => $precision,
                'grouping' => $grouping

            ]
        );

        $row = new Row();
        $row->setField('number_col', $number);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }

    /**
     * @dataProvider numberColumnCurrencyProvider
     */
    public function testRenderCellNumberMoneyColumn($locale, $precision, $grouping, $number, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new NumberColumn(
            [
                'id' => 'number_col',
                'locale' => $locale,
                'style' => 'money',
                'precision' => $precision,
                'grouping' => $grouping

            ]
        );

        $row = new Row();
        $row->setField('number_col', $number);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }


    public function numberColumnPercentProvider()
    {
        return [
            [null, 1, false, false, 1234.55, '1234.6%'],
            ['fr_FR', 0, false, false, 1234.54, '1235 %'],
            ['fr_FR', 1, true, false, 1234.55, '1 234,6 %'],
            ['fr_FR', 1, true, true, 0.5567, '55,7 %'],
        ];
    }

    /**
     * @dataProvider numberColumnPercentProvider
     */
    public function testRenderCellNumberPercentColumn($locale, $precision, $grouping, $fractional, $number, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new NumberColumn(
            [
                'id' => 'number_col',
                'locale' => $locale,
                'style' => 'percent',
                'precision' => $precision,
                'grouping' => $grouping,
                'fractional' => $fractional

            ]
        );

        $row = new Row();
        $row->setField('number_col', $number);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }


    public function numberColumnDurationProvider()
    {
        return [

            [null, false, 59, '59 sec.'],
            [null, false, 0.8, '1 sec.'],
            [null, false, 61, '1:01'],
            ['fr_FR', false, 3601, '1:00:01'],
        ];
    }

    /**
     * @dataProvider numberColumnDurationProvider
     */
    public function testRenderCellNumberDurationColumn($locale, $grouping, $number, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new NumberColumn(
            [
                'id' => 'number_col',
                'locale' => $locale,
                'style' => 'duration',
                'grouping' => $grouping,

            ]
        );

        $row = new Row();
        $row->setField('number_col', $number);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }


    public function numberColumnSpellOutProvider()
    {
        return [

            [null, 2, true, 1234.543, 'one thousand two hundred thirty-four point five four three'],
            ['fr_FR', 1, false, 1234.543, 'mille deux cent trente-quatre virgule cinq quatre trois'],
        ];
    }

    /**
     * @dataProvider numberColumnSpellOutProvider
     */
    public function testRenderCellNumberSpellOutColumn($locale, $precision, $grouping, $number, $expected)
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new NumberColumn(
            [
                'id' => 'number_col',
                'locale' => $locale,
                'precision' => $precision,
                'style' => 'spellout',
                'grouping' => $grouping,

            ]
        );

        $row = new Row();
        $row->setField('number_col', $number);

        $this->assertEquals(
            $expected,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }


    public function testRenderCellNumberRankColumn()
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new RankColumn();
        $row = new Row();

        $this->assertEquals(
            1,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

        $this->assertEquals(
            2,
            trim($this->extension->getGridCell($this->twig, $column, $row, $grid))
        );

    }

    public function testRenderCellJoinColumn()
    {
        $grid = $this->getMockGridAndInit('', 'GRID_HASH');

        $column = new JoinColumn(
            [
                'id' => 'join_col',
                'columns' => ['text_col1', 'text_col2'],
                'separator' => '-'
            ]
        );

        $row = new Row();
        $row->setField('text_col1', 'text_col1_value');
        $row->setField('text_col2', 'text_col2_value');

        $this->assertRegExp(
            '/\s*text_col1_value-\s*text_col2_value-/',
            $this->extension->getGridCell($this->twig, $column, $row, $grid)
        );


    }

}

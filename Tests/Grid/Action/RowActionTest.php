<?php

namespace APY\DataGridBundle\Tests\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;

class RowActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var RowAction */
    private $rowAction;

    private $row;

    public function testExecuteAllCallbacks()
    {
        $this->addCalbacks();

        $this->row
            ->expects($this->exactly(2))
            ->method('getField')
            ->with($this->logicalOr('foo', 'bar'))
            ->willReturn(1);

        $this->assertEquals($this->rowAction, $this->rowAction->render($this->row));
    }

    public function testStopOnFirstCallbackFailed()
    {
        $this->addCalbacks();

        $this->row
            ->expects($this->exactly(1))
            ->method('getField')
            ->with('foo')
            ->willReturn(0);

        $this->assertEquals(null, $this->rowAction->render($this->row));
    }

    private function addCalbacks()
    {
        $this->rowAction->addManipulateRender(function ($action, $row) {
            if ($row->getField('foo') == 0) {
                return;
            }

            return $action;
        });

        $this->rowAction->addManipulateRender(function ($action, $row) {
            if ($row->getField('bar') == 0) {
                return;
            }

            return $action;
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->rowAction = new RowAction('foo', 'foo_route');
        $this->row = $this->createMock('APY\DataGridBundle\Grid\Row');
    }

    protected function tearDown()
    {
        $this->rowAction = null;
    }
}

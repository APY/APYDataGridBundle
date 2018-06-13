<?php

namespace APY\DataGridBundle\Tests\Grid\Action;

use APY\DataGridBundle\Grid\Action\DeleteMassAction;
use PHPUnit\Framework\TestCase;

class DeleteMassActionTest extends TestCase
{
    public function testConstructWithConfirmation()
    {
        $ma = new DeleteMassAction(true);
        $this->assertAttributeEquals(true, 'confirm', $ma);
    }

    public function testConstructWithoutConfirmation()
    {
        $ma = new DeleteMassAction();
        $this->assertAttributeEquals(false, 'confirm', $ma);
    }
}

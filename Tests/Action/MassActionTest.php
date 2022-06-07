<?php

namespace APY\DataGridBundle\Tests\Grid\Action;

use APY\DataGridBundle\Grid\Action\MassAction;
use PHPUnit\Framework\TestCase;

class MassActionTest extends TestCase
{
    /** @var MassAction */
    private $massAction;

    /** @var string */
    private $title = 'foo';

    /** @var string */
    private $callback = 'static::massAction';

    /** @var bool */
    private $confirm = true;

    /** @var array */
    private $parameters = ['foo' => 'foo', 'bar' => 'bar'];

    /** @var string */
    private $role = 'ROLE_FOO';

    public function testMassActionConstruct()
    {
        $this->assertEquals($this->title, $this->massAction->getTitle());
        $this->assertEquals($this->callback, $this->massAction->getCallback());
        $this->assertEquals($this->confirm, $this->massAction->getConfirm());
        $this->assertEquals($this->parameters, $this->massAction->getParameters());
        $this->assertEquals($this->role, $this->massAction->getRole());
    }

    public function testGetTitle()
    {
        $title = 'foobar';
        $this->massAction->setTitle($title);

        $this->assertEquals($title, $this->massAction->getTitle());
    }

    public function testGetCallback()
    {
        $callback = 'self::barMassAction';
        $this->massAction->setCallback($callback);

        $this->assertEquals($callback, $this->massAction->getCallback());
    }

    public function testGetConfirm()
    {
        $confirm = false;
        $this->massAction->setConfirm($confirm);

        $this->assertFalse($this->massAction->getConfirm());
    }

    public function testDefaultConfirmMessage()
    {
        $this->assertIsString($this->massAction->getConfirmMessage());
    }

    public function testGetConfirmMessage()
    {
        $message = 'A bar test message';
        $this->massAction->setConfirmMessage($message);

        $this->assertEquals($message, $this->massAction->getConfirmMessage());
    }

    public function testGetParameters()
    {
        $params = [1, 2, 3];
        $this->massAction->setParameters($params);

        $this->assertEquals($params, $this->massAction->getParameters());
    }

    public function testGetRole()
    {
        $role = 'ROLE_SUPER_ADMIN';
        $this->massAction->setRole($role);

        $this->assertEquals($role, $this->massAction->getRole());
    }

    protected function setUp(): void
    {
        $this->massAction = new MassAction($this->title, $this->callback, $this->confirm, $this->parameters, $this->role);
    }
}

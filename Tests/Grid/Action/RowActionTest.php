<?php

namespace APY\DataGridBundle\Tests\Grid\Action;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Row;

class RowActionTest extends \PHPUnit\Framework\TestCase
{
    private string $title = 'title';

    private string $route = 'vendor.bundle.controller.route_name';

    private bool $confirm = true;

    private string $target = '_parent';

    private array $attributes = ['foo' => 'foo', 'bar' => 'bar'];

    private string $role = 'ROLE_FOO';

    private array $callbacks = [];

    private \APY\DataGridBundle\Grid\Action\RowAction $rowAction;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $row;

    public function testSetTitle()
    {
        $title = 'foo_title';
        $this->rowAction->setTitle($title);

        $this->assertEquals($title, $this->rowAction->getTitle());
    }

    public function testGetTitle()
    {
        $title = 'foo_title';
        $this->rowAction->setTitle($title);

        $this->assertEquals($title, $this->rowAction->getTitle());
    }

    public function testSetRoute()
    {
        $route = 'another_vendor.another_bundle.controller.route_name';
        $this->rowAction->setRoute($route);

        $this->assertEquals($route, $this->rowAction->getRoute());
    }

    public function testGetRoute()
    {
        $route = 'another_vendor.another_bundle.controller.route_name';
        $this->rowAction->setRoute($route);

        $this->assertEquals($route, $this->rowAction->getRoute());
    }

    public function testSetConfirm()
    {
        $confirm = true;
        $this->rowAction->setConfirm($confirm);

        $this->assertEquals(true, $this->rowAction->getConfirm());
    }

    public function testGetConfirmation()
    {
        $confirm = true;
        $this->rowAction->setConfirm($confirm);

        $this->assertTrue($this->rowAction->getConfirm());
    }

    public function testDefaultConfirmMessage()
    {
        $this->assertInternalType('string', $this->rowAction->getConfirmMessage());
    }

    public function testSetConfirmMessage()
    {
        $message = 'A foo test message';
        $this->rowAction->setConfirmMessage($message);

        $this->assertEquals($message, $this->rowAction->getConfirmMessage());
    }

    public function testGetConfirmMessage()
    {
        $message = 'A bar test message';
        $this->rowAction->setConfirmMessage($message);

        $this->assertEquals($message, $this->rowAction->getConfirmMessage());
    }

    public function testSetTarget()
    {
        $target = '_self';
        $this->rowAction->setTarget($target);

        $this->assertEquals($target, $this->rowAction->getTarget());
    }

    public function testGetTarget()
    {
        $target = '_blank';
        $this->rowAction->setTarget($target);

        $this->assertEquals($target, $this->rowAction->getTarget());
    }

    public function testSetColumn()
    {
        $col = 'foo';
        $this->rowAction->setColumn($col);

        $this->assertEquals($col, $this->rowAction->getColumn());
    }

    public function testGetColumn()
    {
        $col = 'bar';
        $this->rowAction->setColumn($col);

        $this->assertEquals($col, $this->rowAction->getColumn());
    }

    public function testAddRouteParameters()
    {
        $stringParam = 'aParam';
        $this->rowAction->addRouteParameters($stringParam);

        $string2Param = 'secondStringParam';
        $this->rowAction->addRouteParameters($string2Param);

        $intKeyParam = [1 => 'paramOne', 2 => 'paramTwo'];
        $this->rowAction->addRouteParameters($intKeyParam);

        $associativeParam = ['foo' => 'fooParam', 'bar' => 'barParam'];
        $this->rowAction->addRouteParameters($associativeParam);

        $this->assertAttributeEquals(
            array_merge([0 => $stringParam, 1 => $string2Param, 2 => $intKeyParam[1], 3 => $intKeyParam[2]], $associativeParam),
            'routeParameters',
            $this->rowAction
        );
    }

    public function testSetStringRouteParameters()
    {
        $param = 'param';
        $this->rowAction->setRouteParameters($param);

        $this->assertAttributeEquals([0 => $param], 'routeParameters', $this->rowAction);
    }

    public function testSetArrayRouteParameters()
    {
        $params = ['foo' => 'foo_param', 'bar' => 'bar_param'];
        $this->rowAction->setRouteParameters($params);

        $this->assertEquals($params, $this->rowAction->getRouteParameters());
    }

    public function testGetRouteParameters()
    {
        $params = ['foo' => 'foo_param', 'bar' => 'bar_param'];
        $this->rowAction->setRouteParameters($params);

        $this->assertEquals($params, $this->rowAction->getRouteParameters());
    }

    public function testSetRouteParametersMapping()
    {
        $routeParamsMapping = ['foo.bar.city' => 'cityId', 'foo.bar.country' => 'countryId'];
        $this->rowAction->setRouteParametersMapping($routeParamsMapping);

        $this->assertEquals($routeParamsMapping, $this->rowAction->getRouteParametersMapping());
    }

    public function testGetRouteParametersMapping()
    {
        $routeParamKey = 'foo.bar.city';
        $routeParamValue = 'cityId';
        $routeParamsMapping = [$routeParamKey => $routeParamValue];
        $this->rowAction->setRouteParametersMapping($routeParamsMapping);

        $this->assertEquals('cityId', $this->rowAction->getRouteParametersMapping('foo.bar.city'));
        $this->assertNull($this->rowAction->getRouteParametersMapping('foo.bar.country'));
    }

    public function testSetAttributes()
    {
        $attr = ['foo' => 'foo_val', 'bar' => 'bar_val'];
        $this->rowAction->setAttributes($attr);

        $this->assertEquals($attr, $this->rowAction->getAttributes());
    }

    public function testAddAttribute()
    {
        $attrName = 'foo1';
        $attrVal = 'foo_val1';
        $this->rowAction->addAttribute($attrName, $attrVal);

        $this->assertAttributeEquals(
            array_merge($this->attributes, [$attrName => $attrVal]),
            'attributes',
            $this->rowAction
        );
    }

    public function testGetAttributes()
    {
        $this->assertEquals($this->attributes, $this->rowAction->getAttributes());
    }

    public function testSetRole()
    {
        $role = 'ROLE_ADMIN';
        $this->rowAction->setRole($role);

        $this->assertEquals($role, $this->rowAction->getRole());
    }

    public function testGetRole()
    {
        $role = 'ROLE_SUPER_ADMIN';
        $this->rowAction->setRole($role);

        $this->assertEquals($role, $this->rowAction->getRole());
    }

    public function testManipulateRender()
    {
        $callback1 = fn() => 1;
        $callback2 = fn() => 2;

        $this->rowAction->manipulateRender($callback1);
        $this->rowAction->manipulateRender($callback2);

        $this->assertEquals([$callback1, $callback2], $this->rowAction->getCallbacks());
    }

    public function testAddManipulateRender()
    {
        $this->addCalbacks();
        $this->assertAttributeEquals($this->callbacks, 'callbacks', $this->rowAction);
    }

    private function addCalbacks()
    {
        $callback1 = function ($action, $row) {
            /** @var $row Row */
            if ($row->getField('foo') == 0) {
                return;
            }

            return $action;
        };

        $this->rowAction->addManipulateRender($callback1);

        $callback2 = function ($action, $row) {
            /** @var $row Row */
            if ($row->getField('bar') == 0) {
                return;
            }

            return $action;
        };

        $this->rowAction->addManipulateRender($callback2);

        $this->callbacks = [$callback1, $callback2];
    }

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

    public function testSetEnabled()
    {
        $enabled = true;
        $this->rowAction->setEnabled($enabled);

        $this->assertEquals($enabled, $this->rowAction->getEnabled());
    }

    public function testGetEnabled()
    {
        $enabled = true;
        $this->rowAction->setEnabled($enabled);

        $this->assertTrue($this->rowAction->getEnabled());
    }

    protected function setUp(): void
    {
        $this->rowAction = new RowAction(
            $this->title, $this->route, $this->confirm, $this->target, $this->attributes, $this->role
        );
        $this->row = $this->createMock(Row::class);
    }
}

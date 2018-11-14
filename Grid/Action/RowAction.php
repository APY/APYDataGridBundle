<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace APY\DataGridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Row;

class RowAction implements RowActionInterface
{
    /** @var string */
    protected $title;

    /** @var string */
    protected $route;

    /** @var bool */
    protected $confirm;

    /** @var string */
    protected $confirmMessage;

    /** @var string */
    protected $target;

    /** @var string */
    protected $column = '__actions';

    /** @var array */
    protected $routeParameters = [];

    /** @var array */
    protected $routeParametersMapping = [];

    /** @var array */
    protected $attributes = [];

    /** @var string|null */
    protected $role;

    /** @var array */
    protected $callbacks = [];

    /** @var bool */
    protected $enabled = true;

    /**
     * Default RowAction constructor.
     *
     * @param string $title      Title of the row action
     * @param string $route      Route to the row action
     * @param bool   $confirm    Show confirm message if true
     * @param string $target     Set the target of this action (_self,_blank,_parent,_top)
     * @param array  $attributes Attributes of the anchor tag
     * @param string $role       Security role
     *
     * @return \APY\DataGridBundle\Grid\Action\RowAction
     */
    public function __construct($title, $route, $confirm = false, $target = '_self', $attributes = [], $role = null)
    {
        $this->title = $title;
        $this->route = $route;
        $this->confirm = $confirm;
        $this->confirmMessage = 'Do you want to ' . strtolower($title) . ' this row?';
        $this->target = $target;
        $this->attributes = $attributes;
        $this->role = $role;
    }

    // @todo: has this setter real sense? we passed this value from constructor
    /**
     * Set action title.
     *
     * @param string $title
     *
     * @return self
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    // @todo: has this setter real sense? we passed this value from constructor
    /**
     * Set action route.
     *
     * @param string $route
     *
     * @return self
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute()
    {
        return $this->route;
    }

    // @todo: we should change this to something like "enableConfirm" as "false" is the default value and has pretty much
    // nosense to use setConfirm with false parameter.
    /**
     * Set action confirm.
     *
     * @param bool $confirm
     *
     * @return self
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * Set action confirmMessage.
     *
     * @param string $confirmMessage
     *
     * @return self
     */
    public function setConfirmMessage($confirmMessage)
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfirmMessage()
    {
        return $this->confirmMessage;
    }

    /**
     * Set action target.
     *
     * @param string $target
     *
     * @return self
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set action column.
     *
     * @param string $column Identifier of the action column
     *
     * @return self
     */
    public function setColumn($column)
    {
        $this->column = $column;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Add route parameter.
     *
     * @param array|string $routeParameters
     *
     * @return self
     */
    public function addRouteParameters($routeParameters)
    {
        $routeParameters = (array) $routeParameters;

        foreach ($routeParameters as $key => $routeParameter) {
            if (is_int($key)) {
                $this->routeParameters[] = $routeParameter;
            } else {
                $this->routeParameters[$key] = $routeParameter;
            }
        }

        return $this;
    }

    /**
     * Set route parameters.
     *
     * @param array|string $routeParameters
     *
     * @return self
     */
    public function setRouteParameters($routeParameters)
    {
        $this->routeParameters = (array) $routeParameters;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    // @todo: why is this accepting string? it seems pretty useless, isn't it?
    /**
     * Set route parameters mapping.
     *
     * @param array|string $routeParametersMapping
     *
     * @return self
     */
    public function setRouteParametersMapping($routeParametersMapping)
    {
        $this->routeParametersMapping = (array) $routeParametersMapping;

        return $this;
    }

    /**
     * Map the parameter.
     *
     * @param string $name parameter
     *
     * @return null|string
     */
    public function getRouteParametersMapping($name)
    {
        return isset($this->routeParametersMapping[$name]) ? $this->routeParametersMapping[$name] : null;
    }

    /**
     * Set attributes.
     *
     * @param array $attributes
     *
     * @return self
     */
    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Add attribute.
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function addAttribute($name, $value)
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * set role.
     *
     * @param string $role
     *
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set render callback.
     *
     * @deprecated This is deprecated and will be removed in 3.0; use addManipulateRender instead.
     *
     * @param \Closure $callback
     *
     * @return self
     */
    public function manipulateRender(\Closure $callback)
    {
        return $this->addManipulateRender($callback);
    }

    /**
     * Add a callback to render callback stack.
     *
     * @param \Closure $callback
     *
     * @return self
     */
    public function addManipulateRender($callback)
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Render action for row.
     *
     * @param Row $row
     *
     * @return RowAction|null
     */
    public function render($row)
    {
        foreach ($this->callbacks as $callback) {
            if (is_callable($callback)) {
                if (null === call_user_func($callback, $this, $row)) {
                    return;
                }
            }
        }

        return $this;
    }

    // @todo: should not this be "isEnabled"?
    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    // @todo: should not this be "enable" as default value is false?
    /**
     * Set the enabled state of this action.
     *
     * @param bool $enabled
     *
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }
}

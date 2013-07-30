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

class RowAction implements RowActionInterface
{
    protected $title;
    protected $route;
    protected $confirm;
    protected $confirmMessage;
    protected $target;
    protected $column = '__actions';
    protected $routeParameters = array();
    protected $routeParametersMapping = array();
    protected $attributes = array();
    protected $role;
    protected $callback;

    /**
     * Default RowAction constructor
     *
     * @param string $title Title of the row action
     * @param string $route Route to the row action
     * @param boolean $confirm Show confirm message if true
     * @param string $target Set the target of this action (_self,_blank,_parent,_top)
     * @param array $attributes Attributes of the anchor tag
     * @param string $role Security role
     *
     * @return \APY\DataGridBundle\Grid\Action\RowAction
     */
    public function __construct($title, $route, $confirm = false, $target = '_self', $attributes = array(), $role = null)
    {
        $this->title = $title;
        $this->route = $route;
        $this->confirm = $confirm;
        $this->confirmMessage = 'Do you want to '.strtolower($title).' this row?';
        $this->target = $target;
        $this->attributes = $attributes;
        $this->role = $role;
    }

    /**
     * Set action title
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
     * get action title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set action route
     *
     * @param  string $route
     *
     * @return self
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * get action route
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Set action confirm
     *
     * @param  $confirm
     *
     * @return self
     */
    public function setConfirm($confirm)
    {
        $this->confirm = $confirm;

        return $this;
    }

    /**
     * get action confirm
     *
     * @return boolean
     */
    public function getConfirm()
    {
        return $this->confirm;
    }

    /**
     * Set action confirmMessage
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
     * get action confirmMessage
     *
     * @return string
     */
    public function getConfirmMessage()
    {
        return $this->confirmMessage;
    }

    /**
     * Set action target
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
     * get action target
     *
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set action column
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
     * get action column
     *
     * @return \APY\DataGridBundle\Grid\Column\Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Add route parameter
     *
     * @param array|string $routeParameter
     *
     * @return self
     */
    public function addRouteParameters($routeParameters)
    {
        $routeParameters = (array) $routeParameters;

        foreach ($routeParameters as $key => $routeParameter) {
            if(is_int($key)) {
                $this->routeParameters[] = $routeParameter;
            } else {
                $this->routeParameters[$key] = $routeParameter;
            }
        }

        return $this;
    }

    /**
     * Set route parameters
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
     * get route parameters
     *
     * @return array
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Set route parameters mapping
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
     * Map the parameter
     *
     * @param string $name parameter
     * @return null|string
     */
    public function getRouteParametersMapping($name)
    {
        return (isset($this->routeParametersMapping[$name]) ? $this->routeParametersMapping[$name] : null);
    }

    /**
     * Set attributes
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
     * Add attribute
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
     * Get attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * set role
     *
     * @param mixed $role
     *
     * @return self
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set render callback
     *
     * @param  $callback
     * @return self
     */
    public function manipulateRender($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Render action for row
     *
     * @param \APY\DataGridBundle\Grid\Row $row
     * @return null|RowAction
     */
    public function render($row)
    {
        if (is_callable($this->callback)) {
            return call_user_func($this->callback, $this, $row);
        }
        
        return $this;
    }
}

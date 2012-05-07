<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Action;

class RowAction implements RowActionInterface
{
    private $title;
    private $route;
    private $confirm;
    private $confirmMessage;
    private $target;
    private $column = '__actions';
    private $routeParameters = array();
    private $attributes = array();

    /**
     * Default RowAction constructor
     *
     * @param string $title Title of the mass action
     * @param string $route Route to the row action
     * @param boolean $confirm Show confirm message if true
     * @param string $target Set the target of this action (_slef,_blank,_parent,_top)
     * @param array $attributes Attributes of the anchor tag
     *
     * @return \Sorien\DataGridBundle\Grid\Action\RowAction
     */
    public function __construct($title, $route = null, $confirm = false, $target = '_self', $attributes = array())
    {
        $this->title = $title;
        $this->route = $route;
        $this->confirm = $confirm;
        $this->confirmMessage = 'Do you want to '.strtolower($title).' this row?';
        $this->target = $target;
        $this->attributes = $attributes;
    }

    /**
     * Set action title
     *
     * @param string $title
     *
     * @return \Sorien\DataGridBundle\Grid\Action\MassAction
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
     * @param \Sorien\DataGridBundle\Grid\Column\Column $column
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
     * @return \Sorien\DataGridBundle\Grid\Column\Column
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     * Set route parameters
     *
     * @param array $routeParameters
     *
     * @return self
     */
    public function setRouteParameters(array $routeParameters)
    {
        $this->routeParameters = $routeParameters;

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
     * @param array $attribute
     *
     * @return self
     */
    public function addAttribute($attribute)
    {
        $this->attributes[] = $attribute;

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
}

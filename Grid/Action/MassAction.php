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

class MassAction implements MassActionInterface
{
    private $title;
    private $callback;
    private $confirm;
    
    /**
     * Default MassAction constructor
     *
     * @param string $title Title of the mass action
     * @param string $callback Callback of the mass action
     * @param boolean $confirm Show confirm message if true
     * @return \Sorien\DataGridBundle\Grid\Action\MassAction
     */
    public function __construct($title, $callback = null, $confirm = false)
    {
        $this->title = $title;
        $this->callback = $callback;
        $this->confirm = $confirm;
    }

    /**
     * Set action title
     *
     * @param $title
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
     * Set action callback
     *
     * @param  $callback
     * @return \Sorien\DataGridBundle\Grid\Action\MassAction
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * get action callback
     *
     * @return string
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * Set action confirm
     *
     * @param  $confirm
     * @return \Sorien\DataGridBundle\Grid\Action\MassAction
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
}

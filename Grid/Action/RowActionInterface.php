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

// @todo: implementation seems to be more specific than interface. It obviously be the case but I've noticed that
// only one method of this interface is used in our code. So I wonder if this interface is "updated" and is the mimimum
// API methods that should be provided as a contract or not.
interface RowActionInterface
{
    /**
     * get action title.
     *
     */
    public function getTitle(): string;

    /**
     * get action route.
     *
     */
    public function getRoute(): string;

    /**
     * get action confirm.
     *
     */
    public function getConfirm(): bool;

    /**
     * get action confirmMessage.
     *
     */
    public function getConfirmMessage(): string;

    /**
     * get action target.
     *
     */
    public function getTarget(): string;

    /**
     * get the action column id.
     *
     */
    public function getColumn(): ?string;

    /**
     * get route parameters.
     *
     */
    public function getRouteParameters(): array;

    /**
     * get attributes of the link.
     *
     */
    public function getAttributes(): array;

    /**
     * get action enabled.
     *
     */
    public function getEnabled(): bool;
}

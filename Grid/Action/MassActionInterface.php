<?php

namespace APY\DataGridBundle\Grid\Action;

interface MassActionInterface
{
    public function getTitle(): string;

    public function getCallback(): callable|string|null;

    public function getConfirm(): bool;

    public function getConfirmMessage(): string;

    public function getParameters(): array;
}

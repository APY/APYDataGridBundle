<?php

namespace APY\DataGridBundle\Grid\Action;

class MassAction implements MassActionInterface
{
    protected string $title;

    protected \Closure|string|null $callback;

    protected bool $confirm;

    protected string $confirmMessage;

    protected array $parameters = [];

    protected ?string $role;

    /**
     * @param string      $title      Title of the mass action
     * @param string|null $callback   Callback of the mass action
     * @param bool        $confirm    Show confirm message if true
     * @param array       $parameters Additional parameters
     * @param string|null $role       Security role
     */
    public function __construct(string $title, callable|string|null $callback = null, bool $confirm = false, array $parameters = [], string $role = null)
    {
        $this->title = $title;
        $this->callback = $callback;
        $this->confirm = $confirm;
        $this->confirmMessage = 'Do you want to '.\strtolower($title).' the selected rows?';
        $this->parameters = $parameters;
        $this->role = $role;
    }

    // @todo: has this setter sense? we passed the title from constructor
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setCallback(callable|string|null $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    public function getCallback(): callable|string|null
    {
        return $this->callback;
    }

    // @todo: we should change this to something like "enableConfirm" as "false" is the default value and has pretty much
    // nosense to use setConfirm with false parameter.
    public function setConfirm(bool $confirm): static
    {
        $this->confirm = $confirm;

        return $this;
    }

    // @todo: could we change this to neddConfirm?
    public function getConfirm(): bool
    {
        return $this->confirm;
    }

    public function setConfirmMessage(string $confirmMessage): static
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    public function getConfirmMessage(): string
    {
        return $this->confirmMessage;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }
}

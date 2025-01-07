<?php

namespace APY\DataGridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Row;

class RowAction implements RowActionInterface
{
    protected string $title;

    protected string $route;

    protected bool $confirm;

    protected string $confirmMessage;

    protected string $target;

    protected string $column = '__actions';

    protected array $routeParameters = [];

    protected array $routeParametersMapping = [];

    protected array $attributes = [];

    protected ?string $role;

    protected array $callbacks = [];

    protected bool $enabled = true;

    /**
     * @param string      $title      Title of the row action
     * @param string      $route      Route to the row action
     * @param bool        $confirm    Show confirm message if true
     * @param string      $target     Set the target of this action (_self,_blank,_parent,_top)
     * @param array       $attributes Attributes of the anchor tag
     * @param string|null $role       Security role
     */
    public function __construct(string $title, string $route, bool $confirm = false, string $target = '_self', array $attributes = [], ?string $role = null)
    {
        $this->title = $title;
        $this->route = $route;
        $this->confirm = $confirm;
        $this->confirmMessage = 'Do you want to '.\strtolower($title).' this row?';
        $this->target = $target;
        $this->attributes = $attributes;
        $this->role = $role;
    }

    // @todo: has this setter real sense? we passed this value from constructor
    /**
     * Set action title.
     *
     * @return self
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    // @todo: has this setter real sense? we passed this value from constructor
    /**
     * Set action route.
     *
     * @return self
     */
    public function setRoute(string $route): static
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute(): string
    {
        return $this->route;
    }

    // @todo: we should change this to something like "enableConfirm" as "false" is the default value and has pretty much
    // nosense to use setConfirm with false parameter.
    /**
     * Set action confirm.
     *
     * @return self
     */
    public function setConfirm(bool $confirm): static
    {
        $this->confirm = $confirm;

        return $this;
    }

    public function getConfirm(): bool
    {
        return $this->confirm;
    }

    /**
     * Set action confirmMessage.
     *
     * @return self
     */
    public function setConfirmMessage(string $confirmMessage): static
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    public function getConfirmMessage(): string
    {
        return $this->confirmMessage;
    }

    /**
     * Set action target.
     *
     * @return self
     */
    public function setTarget(string $target): static
    {
        $this->target = $target;

        return $this;
    }

    public function getTarget(): string
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
    public function setColumn(string $column): static
    {
        $this->column = $column;

        return $this;
    }

    public function getColumn(): ?string
    {
        return $this->column;
    }

    /**
     * Add route parameter.
     *
     * @return self
     */
    public function addRouteParameters(array|string $routeParameters): static
    {
        $routeParameters = (array) $routeParameters;

        foreach ($routeParameters as $key => $routeParameter) {
            if (\is_int($key)) {
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
     * @return self
     */
    public function setRouteParameters(array|string $routeParameters): static
    {
        $this->routeParameters = (array) $routeParameters;

        return $this;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    // @todo: why is this accepting string? it seems pretty useless, isn't it?
    /**
     * Set route parameters mapping.
     *
     * @return self
     */
    public function setRouteParametersMapping(array|string $routeParametersMapping): static
    {
        $this->routeParametersMapping = (array) $routeParametersMapping;

        return $this;
    }

    /**
     * Map the parameter.
     *
     * @param string $name parameter
     *
     */
    public function getRouteParametersMapping(string $name): ?string
    {
        return $this->routeParametersMapping[$name] ?? null;
    }

    /**
     * Set attributes.
     *
     * @return self
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Add attribute.
     *
     * @return self
     */
    public function addAttribute(string $name, string $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * set role.
     *
     * @return self
     */
    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function manipulateRender(\Closure $callback): static
    {
        return $this->addManipulateRender($callback);
    }

    public function addManipulateRender(\Closure $callback): static
    {
        $this->callbacks[] = $callback;

        return $this;
    }

    /**
     * Render action for row.
     */
    public function render(Row $row): ?static
    {
        foreach ($this->callbacks as $callback) {
            if (\is_callable($callback) && null === $callback($this, $row)) {
                return null;
            }
        }

        return $this;
    }

    // @todo: should not this be "isEnabled"?
    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    // @todo: should not this be "enable" as default value is false?
    /**
     * Set the enabled state of this action.
     */
    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }
}

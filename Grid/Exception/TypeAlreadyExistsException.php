<?php

namespace APY\DataGridBundle\Grid\Exception;

/**
 * Class TypeAlreadyExistsException.
 *
 * @author  Quentin Ferrer
 */
class TypeAlreadyExistsException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string $name The name of type
     */
    public function __construct($name)
    {
        parent::__construct(sprintf('The type of grid "%s" already exists.', $name));
    }
}

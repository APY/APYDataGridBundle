<?php

namespace APY\DataGridBundle\Grid\Exception;

/**
 * Class TypeNotFoundException.
 *
 * @author  Quentin Ferrer
 */
class TypeNotFoundException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string $name The name of type
     */
    public function __construct($name)
    {
        parent::__construct(sprintf('The type of grid "%s" not found', $name));
    }
}

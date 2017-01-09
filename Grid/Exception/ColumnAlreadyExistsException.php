<?php

namespace APY\DataGridBundle\Grid\Exception;

/**
 * Class ColumnAlreadyExistsException.
 *
 * @author  Quentin Ferrer
 */
class ColumnAlreadyExistsException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string $name The column name
     */
    public function __construct($name)
    {
        parent::__construct(sprintf('The type of column "%s" already exists.', $name));
    }
}

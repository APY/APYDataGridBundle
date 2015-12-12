<?php
namespace APY\DataGridBundle\Grid\Exception;

/**
 * Class ColumnNotFoundException
 *
 * @package APY\DataGridBundle\Grid\Exception
 * @author  Quentin Ferrer
 */
class ColumnNotFoundException extends \InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param string $name The column name not found
     */
    public function __construct($name)
    {
        parent::__construct(sprintf('The type of column "%s" not found', $name));
    }
}

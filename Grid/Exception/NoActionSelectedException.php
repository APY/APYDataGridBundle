<?php

namespace APY\DataGridBundle\Grid\Exception;

/**
 * Class NoActionSelectedException.
 *
 * @author Samuele Lilli (samuele.lilli@gmail.com)
 */
class NoActionSelectedException extends \InvalidArgumentException
{
    public function __construct($message = "Selezionare un'azione di massa", $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}

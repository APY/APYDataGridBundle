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

namespace APY\DataGridBundle\Grid\Helper;

use DomainException;

final class StringHelper
{
    /**
     * Converts a fully-qualified class name to a block prefix.
     *
     * @param string $fqcn The fully-qualified class name
     *
     * @return string The block prefix
     * @see: vendor/symfony/form/Util/StringUtil.php:50
     */
    public static function fqcnToBlockPrefix(string $fqcn)
    {
        // Non-greedy ("+?") to match "type" suffix, if present
        if (preg_match('~([^\\\\]+?)(type)?$~i', $fqcn, $matches)) {
            return strtolower(
                preg_replace(
                    ['/([A-Z]+)([A-Z][a-z])/', '/([a-z\d])([A-Z])/'],
                    ['\\1_\\2', '\\1_\\2'],
                    $matches[1]
                )
            );
        }

        throw new DomainException('Invalid fully qualified class name.');
    }
}

<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Abhoryo <abhoryo@free.fr>
 * (c) Stanislav Turza
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @todo check for column extensions
 */

namespace APY\DataGridBundle\Grid\Mapping\Metadata;

class DriverHeap extends \SplPriorityQueue
{
    /**
     * (non-PHPdoc)
     * @see SplPriorityQueue::compare()
     */
     public function compare($priority1, $priority2)
     {
         if ($priority1 === $priority2) {
             return 0;
         }

         return $priority1 > $priority2 ? -1 : 1;
     }
}

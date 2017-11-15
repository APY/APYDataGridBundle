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

class ColumnsIterator extends \FilterIterator
{
    /** @var bool */
    protected $showOnlySourceColumns;

    /**
     * @param \Iterator $iterator
     * @param $showOnlySourceColumns
     */
    public function __construct(\Iterator $iterator, $showOnlySourceColumns)
    {
        parent::__construct($iterator);

        $this->showOnlySourceColumns = $showOnlySourceColumns;
    }

    public function accept()
    {
        $current = $this->getInnerIterator()->current();

        return $this->showOnlySourceColumns ? $current->isVisibleForSource() : true;
    }
}

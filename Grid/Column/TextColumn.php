<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Column;

use Sorien\DataGridBundle\Grid\Filter;

class TextColumn extends Column
{
    public function renderFilter($gridHash)
    {
        return '<input type="text" style="width:100%" value="'.$this->data.'" name="'.$gridHash.'['.$this->getId().']" onkeypress="if (event.which == 13){this.form.submit();}"/>';
    }

    public function getFilters()
    {
        return array(new Filter(self::OPERATOR_REGEXP, '.*'.$this->data.'.*'));
    }

    public function setData($data)
    {
        if (is_string($data))
        {
            $this->data = $data;
        }

        return $this;
    }
    
    public function getName()
    {
        return 'text';
    }
}

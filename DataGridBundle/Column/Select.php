<?php

    /*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Column;

use Sorien\DataGridBundle\Column;

class Select extends Column
{
    private $values;

    public function __construct($id, $title, Array $values, $size = 0, $sortable = true, $visible = true)
    {
        parent::__construct($id, $title, $size, $sortable, !empty($values), $visible);
        $this->values = $values;
    }

    public function drawFilter($gridId)
    {
        $result = '<option value=""></option>';

        foreach ($this->values as $key => $value)
        {
            if ($this->getFilterData() == $key)
            {
                $result .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
            }
            else
            {
                $result .= '<option value="'.$key.'">'.$value.'</option>';
            }
        }

        return '<select name="'.$gridId.'['.$this->getId().'][filter]" onchange="this.form.submit();">'.$result.'</select>';
    }
}

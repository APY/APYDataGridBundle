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

namespace APY\DataGridBundle\Grid\Column;

class BooleanColumn extends Column
{
    public function __initialize(array $params)
    {
        $params['filter'] = 'select';
        $params['selectFrom'] = 'values';
        $params['operators'] = [self::OPERATOR_EQ];
        $params['defaultOperator'] = self::OPERATOR_EQ;
        $params['operatorsVisible'] = false;
        $params['selectMulti'] = false;

        parent::__initialize($params);

        $this->setAlign($this->getParam('align', 'center'));
        $this->setSize($this->getParam('size', '30'));
        $this->setValues($this->getParam('values', [1 => 'true', 0 => 'false']));
    }

    public function isQueryValid($query)
    {
        $query = (array) $query;
        if ($query[0] === true || $query[0] === false || $query[0] == 0 || $query[0] == 1) {
            return true;
        }

        return false;
    }

    public function renderCell($value, $row, $router)
    {
        $value = parent::renderCell($value, $row, $router);

        return $value ?: 'false';
    }

    public function getDisplayedValue($value)
    {
        return is_bool($value) ? ($value ? 1 : 0) : $value;
    }

    public function getType()
    {
        return 'boolean';
    }
}

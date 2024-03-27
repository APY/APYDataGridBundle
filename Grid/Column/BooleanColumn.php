<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Row;
use Symfony\Component\Routing\RouterInterface;

class BooleanColumn extends Column
{
    public function __initialize(array $params): void
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

    public function isQueryValid($query): bool
    {
        $query = (array) $query;
        if (true === $query[0] || false === $query[0] || 0 == $query[0] || 1 == $query[0]) {
            return true;
        }

        return false;
    }

    public function renderCell(mixed $value, Row $row, RouterInterface $router): mixed
    {
        $value = parent::renderCell($value, $row, $router);

        return $value ?: 'false';
    }

    public function getDisplayedValue($value): bool
    {
        return \is_bool($value) ? ($value ? 1 : 0) : $value;
    }

    public function getType(): string
    {
        return 'boolean';
    }
}

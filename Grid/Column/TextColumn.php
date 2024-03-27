<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Source\Source;

class TextColumn extends Column
{
    public function isQueryValid(mixed $query): bool
    {
        $result = \array_filter((array) $query, 'is_string');

        return !empty($result);
    }

    public function getFilters(Source|string $source): array
    {
        $parentFilters = parent::getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            switch ($filter->getOperator()) {
                case self::OPERATOR_ISNULL:
                    $filters[] = new Filter(self::OPERATOR_ISNULL);
                    $filters[] = new Filter(self::OPERATOR_EQ, '');
                    $this->setDataJunction(self::DATA_DISJUNCTION);
                    break;
                case self::OPERATOR_ISNOTNULL:
                    $filters[] = new Filter(self::OPERATOR_ISNOTNULL);
                    $filters[] = new Filter(self::OPERATOR_NEQ, '');
                    break;
                default:
                    $filters[] = $filter;
            }
        }

        return $filters;
    }

    public function getType(): string
    {
        return 'text';
    }
}

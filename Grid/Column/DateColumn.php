<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Source\Source;

class DateColumn extends DateTimeColumn
{
    protected int $timeFormat = \IntlDateFormatter::NONE;

    protected string $fallbackFormat = 'Y-m-d';

    public function getFilters(Source|string $source): array
    {
        $parentFilters = parent::getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            if (null !== $filter->getValue()) {
                $dateFrom = $filter->getValue();
                $dateFrom->setTime(0, 0, 0);

                $dateTo = clone $dateFrom;
                $dateTo->setTime(23, 59, 59);

                switch ($filter->getOperator()) {
                    case self::OPERATOR_EQ:
                        $filters[] = new Filter(self::OPERATOR_GTE, $dateFrom);
                        $filters[] = new Filter(self::OPERATOR_LTE, $dateTo);
                        break;
                    case self::OPERATOR_NEQ:
                        $filters[] = new Filter(self::OPERATOR_LT, $dateFrom);
                        $filters[] = new Filter(self::OPERATOR_GT, $dateTo);
                        $this->setDataJunction(self::DATA_DISJUNCTION);
                        break;
                    case self::OPERATOR_LT:
                    case self::OPERATOR_GTE:
                        $filters[] = new Filter($filter->getOperator(), $dateFrom);
                        break;
                    case self::OPERATOR_GT:
                    case self::OPERATOR_LTE:
                        $filters[] = new Filter($filter->getOperator(), $dateTo);
                        break;
                    default:
                        $filters[] = $filter;
                }
            } else {
                $filters[] = $filter;
            }
        }

        return $filters;
    }

    public function getType(): string
    {
        return 'date';
    }
}

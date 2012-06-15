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

use APY\DataGridBundle\Grid\Filter;

class DateColumn extends DateTimeColumn
{
    protected $timeFormat = \IntlDateFormatter::NONE;

    protected $fallbackFormat = 'Y-m-d';

    public function getFilters($source)
    {
        $parentFilters = parent::getFilters($source);

        $filters = array();
        foreach($parentFilters as $filter) {
            if ($filter->getValue() !== null) {
                $dateFrom = $filter->getValue();
                $dateFrom->setTime(0, 0, 0);

                $dateTo = clone $dateFrom;
                $dateTo->setTime(23, 59, 59);

                switch ($filter->getOperator()) {
                    case self::OPERATOR_EQ:
                        $filters[] =  new Filter(self::OPERATOR_GTE, $dateFrom);
                        $filters[] =  new Filter(self::OPERATOR_LTE, $dateTo);
                        break;
                    case self::OPERATOR_NEQ:
                        $filters[] =  new Filter(self::OPERATOR_LT, $dateFrom);
                        $filters[] =  new Filter(self::OPERATOR_GT, $dateTo);
                        $this->setDataJunction(self::DATA_DISJUNCTION);
                        break;
                    case self::OPERATOR_LT:
                    case self::OPERATOR_GTE:
                        $filters[] =  new Filter($filter->getOperator(), $dateFrom);
                        break;
                    case self::OPERATOR_GT:
                    case self::OPERATOR_LTE:
                        $filters[] =  new Filter($filter->getOperator(), $dateTo);
                        break;
                    default:
                        $filters[] = $filter;
                }
            }else {
                $filters[] = $filter;
            }
        }

        return $filters;
    }

    public function getType()
    {
        return 'date';
    }
}

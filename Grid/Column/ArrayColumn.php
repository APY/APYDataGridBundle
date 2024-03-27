<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Source\Source;
use Symfony\Component\Routing\RouterInterface;

class ArrayColumn extends Column
{
    public function __initialize(array $params): void
    {
        parent::__initialize($params);

        $this->setOperators($this->getParam('operators', [
            self::OPERATOR_LIKE,
            self::OPERATOR_NLIKE,
            self::OPERATOR_EQ,
            self::OPERATOR_NEQ,
            self::OPERATOR_ISNULL,
            self::OPERATOR_ISNOTNULL,
        ]));
    }

    public function getFilters(Source|string $source): array
    {
        $parentFilters = parent::getFilters($source);

        $filters = [];
        foreach ($parentFilters as $filter) {
            if ('document' === $source) {
                $filters[] = $filter;
            } else {
                switch ($filter->getOperator()) {
                    case self::OPERATOR_EQ:
                    case self::OPERATOR_NEQ:
                        $filterValues = (array) $filter->getValue();
                        $value = '';
                        $counter = 1;
                        foreach ($filterValues as $filterValue) {
                            $len = \strlen($filterValue);
                            $value .= 'i:'.$counter++.';s:'.$len.':"'.$filterValue.'";';
                        }

                        $filters[] = new Filter($filter->getOperator(), 'a:'.\count($filterValues).':{'.$value.'}');
                        break;
                    case self::OPERATOR_LIKE:
                    case self::OPERATOR_NLIKE:
                        $len = \strlen($filter->getValue());
                        $value = 's:'.$len.':"'.$filter->getValue().'";';
                        $filters[] = new Filter($filter->getOperator(), $value);
                        break;
                    case self::OPERATOR_ISNULL:
                        $filters[] = new Filter(self::OPERATOR_ISNULL);
                        $filters[] = new Filter(self::OPERATOR_EQ, 'a:0:{}');
                        $this->setDataJunction(self::DATA_DISJUNCTION);
                        break;
                    case self::OPERATOR_ISNOTNULL:
                        $filters[] = new Filter(self::OPERATOR_ISNOTNULL);
                        $filters[] = new Filter(self::OPERATOR_NEQ, 'a:0:{}');
                        break;
                    default:
                        $filters[] = $filter;
                }
            }
        }

        return $filters;
    }

    public function renderCell(mixed $value, Row $row, RouterInterface $router): mixed
    {
        if (\is_callable($this->callback)) {
            return \call_user_func($this->callback, $value, $row, $router);
        }

        return $value;
    }

    public function getType(): string
    {
        return 'array';
    }
}

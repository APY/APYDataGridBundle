<?php

namespace Sorien\DataGridBundle\Grid\Source;

interface DistinctFieldRepositoryInterface
{
    /**
     * get distinct items for the given field (eg. column in an rdbms)
     *
     * @param $field
     */
    public function findDistinctByField($field);
}
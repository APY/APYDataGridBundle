<?php

namespace APY\DataGridBundle\Grid\Helper;

use Doctrine\ORM\Query\AST\AggregateExpression;
use Doctrine\ORM\Query\AST\GroupByClause;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\AST\SelectExpression;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\TreeWalkerAdapter;

class ORMCountWalker extends TreeWalkerAdapter
{
    /**
     * Distinct mode hint name.
     */
    public const HINT_DISTINCT = 'doctrine_paginator.distinct';

    /**
     * Walks down a SelectStatement AST node, modifying it to retrieve a COUNT.
     *
     * @throws \RuntimeException
     */
    public function walkSelectStatement(SelectStatement $AST): void
    {
        $rootComponents = [];
        foreach ($this->getQueryComponents() as $dqlAlias => $qComp) {
            if (\array_key_exists('parent', $qComp) && null === $qComp['parent'] && 0 === $qComp['nestingLevel']) {
                $rootComponents[] = [$dqlAlias => $qComp];
            }
        }

        if (\count($rootComponents) > 1) {
            throw new \RuntimeException('Cannot count query which selects two FROM components, cannot make distinction');
        }

        $root = \reset($rootComponents);
        $parentName = \key($root);
        $parent = \current($root);

        $pathExpression = new PathExpression(
            PathExpression::TYPE_STATE_FIELD | PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION,
            $parentName,
            $parent['metadata']->getSingleIdentifierFieldName()
        );
        $pathExpression->type = PathExpression::TYPE_STATE_FIELD;

        // Remove the variables which are not used by other clauses
        foreach ($AST->selectClause->selectExpressions as $key => $selectExpression) {
            if (null === $selectExpression->fieldIdentificationVariable) {
                unset($AST->selectClause->selectExpressions[$key]);
            } elseif ($selectExpression->expression instanceof PathExpression) {
                $groupByClause[] = $selectExpression->expression;
            }
        }

        // Put the count expression in the first position
        $distinct = $this->_getQuery()->getHint(self::HINT_DISTINCT);
        \array_unshift(
            $AST->selectClause->selectExpressions,
            new SelectExpression(
                new AggregateExpression('count', $pathExpression, $distinct),
                null
            )
        );

        $groupByClause[] = $pathExpression;
        $AST->groupByClause = new GroupByClause($groupByClause);

        // ORDER BY is not needed, only increases query execution through unnecessary sorting.
        $AST->orderByClause = null;
    }
}

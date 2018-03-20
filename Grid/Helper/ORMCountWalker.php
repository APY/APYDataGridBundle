<?php

/**
 * Doctrine ORM.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@beberlei.de so I can send you a copy immediately.
 */

namespace APY\DataGridBundle\Grid\Helper;

use Doctrine\ORM\Query\AST\AggregateExpression;
use Doctrine\ORM\Query\AST\PathExpression;
use Doctrine\ORM\Query\AST\SelectExpression;
use Doctrine\ORM\Query\AST\SelectStatement;
use Doctrine\ORM\Query\TreeWalkerAdapter;

/**
 * Replaces the selectClause of the AST with a COUNT statement.
 *
 * @category    DoctrineExtensions
 *
 * @author      David Abdemoulaie <dave@hobodave.com>
 * @copyright   Copyright (c) 2010 David Abdemoulaie (http://hobodave.com/)
 * @license     http://hobodave.com/license.txt New BSD License
 */
class ORMCountWalker extends TreeWalkerAdapter
{
    /**
     * Distinct mode hint name.
     */
    const HINT_DISTINCT = 'doctrine_paginator.distinct';

    /**
     * Walks down a SelectStatement AST node, modifying it to retrieve a COUNT.
     *
     * @param SelectStatement $AST
     *
     * @throws \RuntimeException
     */
    public function walkSelectStatement(SelectStatement $AST)
    {
        $rootComponents = [];
        foreach ($this->_getQueryComponents() as $dqlAlias => $qComp) {
            if (array_key_exists('parent', $qComp) && $qComp['parent'] === null && $qComp['nestingLevel'] == 0) {
                $rootComponents[] = [$dqlAlias => $qComp];
            }
        }

        if (count($rootComponents) > 1) {
            throw new \RuntimeException('Cannot count query which selects two FROM components, cannot make distinction');
        }

        $root = reset($rootComponents);
        $parentName = key($root);
        $parent = current($root);

        $pathExpression = new PathExpression(
            PathExpression::TYPE_STATE_FIELD | PathExpression::TYPE_SINGLE_VALUED_ASSOCIATION, $parentName,
            $parent['metadata']->getSingleIdentifierFieldName()
        );
        $pathExpression->type = PathExpression::TYPE_STATE_FIELD;

        // Remove the variables which are not used by other clauses
        foreach ($AST->selectClause->selectExpressions as $key => $selectExpression) {
            if ($selectExpression->fieldIdentificationVariable === null) {
                unset($AST->selectClause->selectExpressions[$key]);
            } elseif ($selectExpression->expression instanceof PathExpression) {
                $groupByClause[] = $selectExpression->expression;
            }
        }

        // Put the count expression in the first position
        $distinct = $this->_getQuery()->getHint(self::HINT_DISTINCT);
        array_unshift($AST->selectClause->selectExpressions,
            new SelectExpression(
                new AggregateExpression('count', $pathExpression, $distinct), null
            )
        );

        $groupByClause[] = $pathExpression;
        $AST->groupByClause = new \Doctrine\ORM\Query\AST\GroupByClause($groupByClause);

        // ORDER BY is not needed, only increases query execution through unnecessary sorting.
        $AST->orderByClause = null;
    }
}

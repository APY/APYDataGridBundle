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

namespace APY\DataGridBundle\Grid\Mapping;

use Attribute;

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Column
{
    protected $metadata;
    protected array $groups;

    public function __construct(
        array $metadata = [],
        array $groups = ['default'],
        int|string $id = null,
        string $type = null,
        string $field = null,
        string $title = null,
        string $fetchFrom = null,
        string $nameField = null,
        bool $primary = null,
        bool $sortable = null,
        bool $visible = null,
        bool $filterable = null,
        bool $source = null,
        string $filter = null,
        bool $nullable = null,
        int $precision = null,
        bool $grouping = null,
        int $roundingMode = null,
        string $fieldName = null,
        string $style = null,
        string $format = null,
        string $money_getter = null,
        string $money_creator = null,
        string $force_currency = null,
        array $operators = null,
        string $defaultOperator = null,
        bool $operatorsVisible = null,
        bool $isManualField = null,
        bool $isAggregate = null,
        bool $usePrefixTitle = null,
        bool $selectMulti = null,
        bool $selectExpanded = null,
        bool $searchOnClick = null,
        string $safe = null,
        string $separator = null,
        int $size = null,
        string $align = null,
        string $inputType = null,
        string $role = null,
        string $order = null,
        array $columns = null,
        string $joinType = null,
        string $selectFrom = null,
        array $values = null,
        string $export = null,
        string $class = null,
        string $translation_domain = null,
    )
    {
        if ($id) {
            $metadata['id'] = $id;
        }
        if ($type) {
            $metadata['type'] = $type;
        }
        if ($field) {
            $metadata['field'] = $field;
        }
        if ($title) {
            $metadata['title'] = $title;
        }
        if ($fetchFrom) {
            $metadata['fetchFrom'] = $fetchFrom;
        }
        if ($nameField) {
            $metadata['nameField'] = $nameField;
        }
        if ($primary) {
            $metadata['primary'] = $primary;
        }
        if ($sortable) {
            $metadata['sortable'] = $sortable;
        }
        if ($visible) {
            $metadata['visible'] = $visible;
        }
        if ($filterable) {
            $metadata['filterable'] = $filterable;
        }
        if ($source) {
            $metadata['source'] = $source;
        }
        if ($filter) {
            $metadata['filter'] = $filter;
        }
        if ($nullable) {
            $metadata['nullable'] = $nullable;
        }
        if ($precision) {
            $metadata['precision'] = $precision;
        }
        if ($grouping) {
            $metadata['grouping'] = $grouping;
        }
        if ($roundingMode) {
            $metadata['roundingMode'] = $roundingMode;
        }
        if ($fieldName) {
            $metadata['fieldName'] = $fieldName;
        }
        if ($style) {
            $metadata['style'] = $style;
        }
        if ($format) {
            $metadata['format'] = $format;
        }
        if ($money_getter) {
            $metadata['money_getter'] = $money_getter;
        }
        if ($money_creator) {
            $metadata['money_creator'] = $money_creator;
        }
        if ($force_currency) {
            $metadata['force_currency'] = $force_currency;
        }
        if ($operators) {
            $metadata['operators'] = $operators;
        }
        if ($defaultOperator) {
            $metadata['defaultOperator'] = $defaultOperator;
        }
        if ($operatorsVisible) {
            $metadata['operatorsVisible'] = $operatorsVisible;
        }
        if ($isManualField) {
            $metadata['isManualField'] = $isManualField;
        }
        if ($isAggregate) {
            $metadata['isAggregate'] = $isAggregate;
        }
        if ($usePrefixTitle) {
            $metadata['usePrefixTitle'] = $usePrefixTitle;
        }
        if ($selectMulti) {
            $metadata['selectMulti'] = $selectMulti;
        }
        if ($selectExpanded) {
            $metadata['selectExpanded'] = $selectExpanded;
        }
        if ($searchOnClick) {
            $metadata['searchOnClick'] = $searchOnClick;
        }
        if ($safe) {
            $metadata['safe'] = $safe;
        }
        if ($separator) {
            $metadata['separator'] = $separator;
        }
        if ($size) {
            $metadata['size'] = $size;
        }
        if ($align) {
            $metadata['align'] = $align;
        }
        if ($inputType) {
            $metadata['inputType'] = $inputType;
        }
        if ($role) {
            $metadata['role'] = $role;
        }
        if ($order) {
            $metadata['order'] = $order;
        }
        if ($columns) {
            $metadata['columns'] = $columns;
        }
        if ($joinType) {
            $metadata['joinType'] = $joinType;
        }
        if ($selectFrom) {
            $metadata['selectFrom'] = $selectFrom;
        }
        if ($values) {
            $metadata['values'] = $values;
        }
        if ($export) {
            $metadata['export'] = $export;
        }
        if ($class) {
            $metadata['class'] = $class;
        }
        if ($translation_domain) {
            $metadata['translation_domain'] = $translation_domain;
        }

        $this->metadata = $metadata;
        $this->groups = isset($metadata['groups']) ? (array) $metadata['groups'] : $groups;
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getGroups(): array
    {
        return $this->groups;
    }
}

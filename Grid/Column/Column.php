<?php

namespace APY\DataGridBundle\Grid\Column;

use APY\DataGridBundle\Grid\Filter;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Source\Source;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

abstract class Column
{
    public const DEFAULT_VALUE = null;

    /**
     * Filter.
     */
    public const DATA_CONJUNCTION = 0;
    public const DATA_DISJUNCTION = 1;

    public const OPERATOR_EQ = 'eq';
    public const OPERATOR_NEQ = 'neq';
    public const OPERATOR_LT = 'lt';
    public const OPERATOR_LTE = 'lte';
    public const OPERATOR_GT = 'gt';
    public const OPERATOR_GTE = 'gte';
    public const OPERATOR_BTW = 'btw';
    public const OPERATOR_BTWE = 'btwe';
    public const OPERATOR_LIKE = 'like';
    public const OPERATOR_NLIKE = 'nlike';
    public const OPERATOR_RLIKE = 'rlike';
    public const OPERATOR_LLIKE = 'llike';
    public const OPERATOR_SLIKE = 'slike'; // simple/strict LIKE
    public const OPERATOR_NSLIKE = 'nslike';
    public const OPERATOR_RSLIKE = 'rslike';
    public const OPERATOR_LSLIKE = 'lslike';

    public const OPERATOR_ISNULL = 'isNull';
    public const OPERATOR_ISNOTNULL = 'isNotNull';

    protected static array $availableOperators = [
        self::OPERATOR_EQ,
        self::OPERATOR_NEQ,
        self::OPERATOR_LT,
        self::OPERATOR_LTE,
        self::OPERATOR_GT,
        self::OPERATOR_GTE,
        self::OPERATOR_BTW,
        self::OPERATOR_BTWE,
        self::OPERATOR_LIKE,
        self::OPERATOR_NLIKE,
        self::OPERATOR_RLIKE,
        self::OPERATOR_LLIKE,
        self::OPERATOR_SLIKE,
        self::OPERATOR_NSLIKE,
        self::OPERATOR_RSLIKE,
        self::OPERATOR_LSLIKE,
        self::OPERATOR_ISNULL,
        self::OPERATOR_ISNOTNULL,
    ];

    /**
     * Align.
     */
    public const ALIGN_LEFT = 'left';
    public const ALIGN_RIGHT = 'right';
    public const ALIGN_CENTER = 'center';

    protected static array $aligns = [
        self::ALIGN_LEFT,
        self::ALIGN_RIGHT,
        self::ALIGN_CENTER,
    ];

    /**
     * Internal parameters.
     */
    protected int|string|null $id = null;
    protected ?string $title = null;
    protected ?bool $sortable = null;
    protected ?bool $filterable = null;
    protected ?bool $visible = null;
    protected mixed $callback = null;
    protected ?string $order = null;
    protected ?int $size = null;
    protected ?bool $visibleForSource = null;
    protected ?bool $primary = null;
    protected ?string $align = null;
    protected ?string $inputType = null;
    protected ?string $field = null;
    protected ?string $role = null;
    protected ?string $filterType = null;
    protected ?array $params = null;
    protected bool $isSorted = false;
    protected ?AuthorizationCheckerInterface $authorizationChecker = null;
    protected ?array $data = null;
    protected ?bool $operatorsVisible = null;
    protected ?array $operators = null;
    protected ?string $defaultOperator = null;
    protected array $values = [];
    protected ?string $selectFrom = null;
    protected ?bool $selectMulti = null;
    protected ?bool $selectExpanded = null;
    protected bool $searchOnClick = false;
    protected string|bool|null $safe = null;
    protected ?string $separator = null;
    protected ?string $joinType = null;
    protected ?bool $export = null;
    protected ?string $class = null;
    protected ?bool $isManualField = null;
    protected ?bool $isAggregate = null;
    protected ?bool $usePrefixTitle = null;
    protected ?string $translationDomain = null;

    protected int $dataJunction = self::DATA_CONJUNCTION;

    public function __construct(?array $params = null)
    {
        $this->__initialize((array) $params);
    }

    public function __initialize(array $params): void
    {
        $this->params = $params;
        $this->setId($this->getParam('id'));
        $this->setTitle($this->getParam('title', $this->getParam('field')));
        $this->setSortable($this->getParam('sortable', true));
        $this->setVisible($this->getParam('visible', true));
        $this->setSize($this->getParam('size', -1));
        $this->setFilterable($this->getParam('filterable', true));
        $this->setVisibleForSource($this->getParam('source', false));
        $this->setPrimary($this->getParam('primary', false));
        $this->setAlign($this->getParam('align', self::ALIGN_LEFT));
        $this->setInputType($this->getParam('inputType', 'text'));
        $this->setField($this->getParam('field'));
        $this->setRole($this->getParam('role'));
        $this->setOrder($this->getParam('order'));
        $this->setJoinType($this->getParam('joinType'));
        $this->setFilterType($this->getParam('filter', 'input'));
        $this->setSelectFrom($this->getParam('selectFrom', 'query'));
        $this->setValues($this->getParam('values', []));
        $this->setOperatorsVisible($this->getParam('operatorsVisible', true));
        $this->setIsManualField($this->getParam('isManualField', false));
        $this->setIsAggregate($this->getParam('isAggregate', false));
        $this->setUsePrefixTitle($this->getParam('usePrefixTitle', true));

        // Order is important for the order display
        $this->setOperators($this->getParam('operators', [
            self::OPERATOR_EQ,
            self::OPERATOR_NEQ,
            self::OPERATOR_LT,
            self::OPERATOR_LTE,
            self::OPERATOR_GT,
            self::OPERATOR_GTE,
            self::OPERATOR_BTW,
            self::OPERATOR_BTWE,
            self::OPERATOR_LIKE,
            self::OPERATOR_NLIKE,
            self::OPERATOR_RLIKE,
            self::OPERATOR_LLIKE,
            self::OPERATOR_SLIKE,
            self::OPERATOR_NSLIKE,
            self::OPERATOR_RSLIKE,
            self::OPERATOR_LSLIKE,
            self::OPERATOR_ISNULL,
            self::OPERATOR_ISNOTNULL,
        ]));
        $this->setDefaultOperator($this->getParam('defaultOperator', self::OPERATOR_LIKE));
        $this->setSelectMulti($this->getParam('selectMulti', false));
        $this->setSelectExpanded($this->getParam('selectExpanded', false));
        $this->setSearchOnClick($this->getParam('searchOnClick', false));
        $this->setSafe($this->getParam('safe', 'html'));
        $this->setSeparator($this->getParam('separator', '<br />'));
        $this->setExport($this->getParam('export'));
        $this->setClass($this->getParam('class'));
        $this->setTranslationDomain($this->getParam('translation_domain'));
    }

    protected function getParam(string $id, mixed $default = null)
    {
        return $this->params[$id] ?? $default;
    }

    /**
     * Draw cell.
     */
    public function renderCell(mixed $value, Row $row, RouterInterface $router): mixed
    {
        if (\is_callable($this->callback)) {
            return \call_user_func($this->callback, $value, $row, $router);
        }

        $value = (string) (\is_bool($value) ? (int) $value : $value);
        if (\array_key_exists($value, $this->values)) {
            $value = $this->values[$value];
        }

        return $value;
    }

    /**
     * Set column callback.
     */
    public function manipulateRenderCell(callable $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * Set column identifier.
     */
    public function setId(int|string|null $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * get column identifier.
     */
    public function getId(): int|string|null
    {
        return $this->id;
    }

    /**
     * get column render block identifier.
     */
    public function getRenderBlockId(): string
    {
        // For Mapping fields and aggregate dql functions
        return \str_replace(['.', ':'], '_', $this->id);
    }

    /**
     * Set column title.
     */
    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get column title.
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set column visibility.
     */
    public function setVisible(bool $visible): static
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Return column visibility.
     *
     * @return bool return true when column is visible
     */
    public function isVisible(bool $isExported = false): bool
    {
        $visible = $isExported && null !== $this->export ? $this->export : $this->visible;

        if ($visible && null !== $this->authorizationChecker && null !== $this->getRole()) {
            return $this->authorizationChecker->isGranted($this->getRole());
        }

        return $visible;
    }

    /**
     * Return true if column is sorted.
     *
     * @return bool return true when column is sorted
     */
    public function isSorted(): bool
    {
        return $this->isSorted;
    }

    public function setSortable(bool $sortable): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * column ability to sort.
     *
     * @return bool return true when column can be sorted
     */
    public function isSortable(): ?bool
    {
        return $this->sortable;
    }

    /**
     * Return true if column is filtered.
     *
     * @return bool return true when column is filtered
     */
    public function isFiltered(): bool
    {
        if ($this->hasFromOperandFilter()) {
            return true;
        }

        if ($this->hasToOperandFilter()) {
            return true;
        }

        return $this->hasOperatorFilter();
    }

    private function hasFromOperandFilter(): bool
    {
        if (!isset($this->data['from'])) {
            return false;
        }

        if (!$this->isQueryValid($this->data['from'])) {
            return false;
        }

        return $this->data['from'] !== static::DEFAULT_VALUE;
    }

    private function hasToOperandFilter(): bool
    {
        if (!isset($this->data['to'])) {
            return false;
        }

        if (!$this->isQueryValid($this->data['to'])) {
            return false;
        }

        return $this->data['to'] !== static::DEFAULT_VALUE;
    }

    private function hasOperatorFilter(): bool
    {
        if (!isset($this->data['operator'])) {
            return false;
        }

        return self::OPERATOR_ISNULL === $this->data['operator'] || self::OPERATOR_ISNOTNULL === $this->data['operator'];
    }

    public function setFilterable(bool $filterable): static
    {
        $this->filterable = $filterable;

        return $this;
    }

    /**
     * column ability to filter.
     *
     * @return bool return true when column can be filtred
     */
    public function isFilterable(): ?bool
    {
        return $this->filterable;
    }

    /**
     * set column order.
     *
     * @param string $order asc|desc
     */
    public function setOrder(?string $order): static
    {
        if (null !== $order) {
            $this->order = $order;
            $this->isSorted = true;
        }

        return $this;
    }

    /**
     * get column order.
     *
     * @return string asc|desc
     */
    public function getOrder(): ?string
    {
        return $this->order;
    }

    /**
     * Set column width.
     */
    public function setSize(int $size): static
    {
        if ($size < -1) {
            throw new \InvalidArgumentException(\sprintf('Unsupported column size %s, use positive value or -1 for auto resize', $size));
        }

        $this->size = $size;

        return $this;
    }

    /**
     * get column width.
     *
     * @return int column width in pixels
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * set filter data from session | request.
     */
    public function setData(array $data): static
    {
        $this->data = ['operator' => $this->getDefaultOperator(), 'from' => static::DEFAULT_VALUE, 'to' => static::DEFAULT_VALUE];

        $hasValue = false;
        if (isset($data['from']) && $this->isQueryValid($data['from'])) {
            $this->data['from'] = $data['from'];
            $hasValue = true;
        }

        if (isset($data['to']) && $this->isQueryValid($data['to'])) {
            $this->data['to'] = $data['to'];
            $hasValue = true;
        }

        $isNullOperator = (isset($data['operator']) && (self::OPERATOR_ISNULL === $data['operator'] || self::OPERATOR_ISNOTNULL === $data['operator']));
        if (($hasValue || $isNullOperator) && isset($data['operator']) && $this->hasOperator($data['operator'])) {
            $this->data['operator'] = $data['operator'];
        }

        return $this;
    }

    /**
     * get filter data from session | request.
     */
    public function getData(): array
    {
        if (!\is_array($this->data)) {
            return [];
        }

        $result = [];

        $hasValue = false;
        if ($this->data['from'] !== $this::DEFAULT_VALUE) {
            $result['from'] = $this->data['from'];
            $hasValue = true;
        }

        if ($this->data['to'] !== $this::DEFAULT_VALUE) {
            $result['to'] = $this->data['to'];
            $hasValue = true;
        }

        $isNullOperator = (isset($this->data['operator']) && (self::OPERATOR_ISNULL === $this->data['operator'] || self::OPERATOR_ISNOTNULL === $this->data['operator']));
        if ($hasValue || $isNullOperator) {
            $result['operator'] = $this->data['operator'];
        }

        return $result;
    }

    /**
     * Return true if filter value is correct (has to be overridden in each Column class that can be filtered, in order to catch wrong values).
     */
    public function isQueryValid(mixed $query): bool
    {
        return true;
    }

    /**
     * Set column visibility for source class.
     */
    public function setVisibleForSource(bool $visibleForSource): static
    {
        $this->visibleForSource = $visibleForSource;

        return $this;
    }

    /**
     * Return true is column in visible for source class.
     */
    public function isVisibleForSource(): ?bool
    {
        return $this->visibleForSource;
    }

    /**
     * Set column as primary.
     */
    public function setPrimary(bool $primary): static
    {
        $this->primary = $primary;

        return $this;
    }

    /**
     * Return true is column in primary.
     */
    public function isPrimary(): ?bool
    {
        return $this->primary;
    }

    /**
     * Set column align.
     *
     * @param string $align left/right/center
     */
    public function setAlign(string $align): static
    {
        if (!\in_array($align, self::$aligns, true)) {
            throw new \InvalidArgumentException(\sprintf('Unsupported align %s, just left, right and center are supported', $align));
        }

        $this->align = $align;

        return $this;
    }

    /**
     * get column align.
     */
    public function getAlign(): ?string
    {
        return $this->align;
    }

    public function setInputType(string $inputType): static
    {
        $this->inputType = $inputType;

        return $this;
    }

    public function getInputType(): ?string
    {
        return $this->inputType;
    }

    public function setField(?string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setRole(?string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setFilterType(?string $filterType): static
    {
        $this->filterType = \strtolower($filterType ?? '');

        return $this;
    }

    public function getFilterType(): string
    {
        return $this->filterType;
    }

    public function getFilters(Source|string $source): array
    {
        if (!\is_array($this->data)) {
            return [];
        }

        $filters = [];

        if ($this->hasOperator($this->data['operator'])) {
            if ($this instanceof ArrayColumn && \in_array($this->data['operator'], [self::OPERATOR_EQ, self::OPERATOR_NEQ], true)) {
                $filters[] = new Filter($this->data['operator'], $this->data['from']);
            } else {
                switch ($this->data['operator']) {
                    case self::OPERATOR_BTW:
                        if ($this->data['from'] !== static::DEFAULT_VALUE) {
                            $filters[] = new Filter(self::OPERATOR_GT, $this->data['from']);
                        }
                        if ($this->data['to'] !== static::DEFAULT_VALUE) {
                            $filters[] = new Filter(self::OPERATOR_LT, $this->data['to']);
                        }
                        break;
                    case self::OPERATOR_BTWE:
                        if ($this->data['from'] !== static::DEFAULT_VALUE) {
                            $filters[] = new Filter(self::OPERATOR_GTE, $this->data['from']);
                        }
                        if ($this->data['to'] !== static::DEFAULT_VALUE) {
                            $filters[] = new Filter(self::OPERATOR_LTE, $this->data['to']);
                        }
                        break;
                    case self::OPERATOR_ISNULL:
                    case self::OPERATOR_ISNOTNULL:
                        $filters[] = new Filter($this->data['operator']);
                        break;
                    case self::OPERATOR_LIKE:
                    case self::OPERATOR_RLIKE:
                    case self::OPERATOR_LLIKE:
                    case self::OPERATOR_SLIKE:
                    case self::OPERATOR_RSLIKE:
                    case self::OPERATOR_LSLIKE:
                    case self::OPERATOR_EQ:
                        if ($this->getSelectMulti()) {
                            $this->setDataJunction(self::DATA_DISJUNCTION);
                        }
                        // no break
                    case self::OPERATOR_NEQ:
                    case self::OPERATOR_NLIKE:
                    case self::OPERATOR_NSLIKE:
                        foreach ((array) $this->data['from'] as $value) {
                            $filters[] = new Filter($this->data['operator'], $value);
                        }
                        break;
                    default:
                        $filters[] = new Filter($this->data['operator'], $this->data['from']);
                }
            }
        }

        return $filters;
    }

    public function setDataJunction(int $dataJunction): static
    {
        $this->dataJunction = $dataJunction;

        return $this;
    }

    /**
     * get data filter junction (how column filters are connected with column data).
     *
     * @return bool self::DATA_CONJUNCTION | self::DATA_DISJUNCTION
     */
    public function getDataJunction(): int
    {
        return $this->dataJunction;
    }

    public function setOperators(array $operators): static
    {
        $this->operators = $operators;

        return $this;
    }

    /**
     * Return column filter operators.
     */
    public function getOperators(): array
    {
        return $this->operators;
    }

    public function setDefaultOperator($defaultOperator): static
    {
        if (!$this->hasOperator($defaultOperator)) {
            throw new \InvalidArgumentException($defaultOperator.' operator not found in operators list.');
        }

        $this->defaultOperator = $defaultOperator;

        return $this;
    }

    public function getDefaultOperator(): ?string
    {
        return $this->defaultOperator;
    }

    /**
     * Return true if $operator is in $operators.
     */
    public function hasOperator(string $operator): bool
    {
        return \in_array($operator, $this->operators, true);
    }

    public function setOperatorsVisible(bool $operatorsVisible): static
    {
        $this->operatorsVisible = $operatorsVisible;

        return $this;
    }

    public function getOperatorsVisible(): ?bool
    {
        return $this->operatorsVisible;
    }

    public function setValues(array $values): static
    {
        $this->values = $values;

        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function setSelectFrom(string $selectFrom): static
    {
        $this->selectFrom = $selectFrom;

        return $this;
    }

    public function getSelectFrom(): ?string
    {
        return $this->selectFrom;
    }

    public function setSelectMulti(bool $selectMulti): static
    {
        $this->selectMulti = $selectMulti;

        return $this;
    }

    public function getSelectMulti(): ?bool
    {
        return $this->selectMulti;
    }

    public function setSelectExpanded(bool $selectExpanded): static
    {
        $this->selectExpanded = $selectExpanded;

        return $this;
    }

    public function getSelectExpanded(): ?bool
    {
        return $this->selectExpanded;
    }

    public function hasDQLFunction(&$matches = null): bool
    {
        $regex = '/(?P<all>(?P<field>\w+):(?P<function>\w+)(:)?(?P<parameters>\w*))$/';

        return (null === $matches) ? \preg_match($regex, $this->field) : \preg_match($regex, $this->field, $matches);
    }

    /**
     * Internal function.
     */
    public function setAuthorizationChecker(AuthorizationCheckerInterface $authorizationChecker): static
    {
        $this->authorizationChecker = $authorizationChecker;

        return $this;
    }

    public function getParentType(): string
    {
        return '';
    }

    public function getType(): string
    {
        return '';
    }

    /**
     * By default all filers include a JavaScript onchange=submit block.  This
     * does not make sense in some cases, such as with multi-select filters.
     *
     * @todo Eventaully make this configurable via annotations?
     */
    public function isFilterSubmitOnChange(): bool
    {
        return !$this->getSelectMulti();
    }

    public function setSearchOnClick(bool $searchOnClick): static
    {
        $this->searchOnClick = $searchOnClick;

        return $this;
    }

    public function getSearchOnClick(): ?bool
    {
        return $this->searchOnClick;
    }

    /**
     * Allows to set twig escaping parameter (html, js, css, url, html_attr)
     * or to display raw value if type is false.
     *
     * @param string|bool $safeOption can be one of false, html, js, css, url, html_attr
     */
    public function setSafe(string|bool $safeOption): static
    {
        $this->safe = $safeOption;

        return $this;
    }

    public function getSafe(): string|bool|null
    {
        return $this->safe;
    }

    public function setSeparator(string $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    public function getSeparator(): ?string
    {
        return $this->separator;
    }

    public function setJoinType(?string $type): static
    {
        $this->joinType = $type;

        return $this;
    }

    public function getJoinType(): ?string
    {
        return $this->joinType;
    }

    public function setExport(?bool $export): static
    {
        $this->export = $export;

        return $this;
    }

    public function getExport(): ?bool
    {
        return $this->export;
    }

    public function setClass(?string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setIsManualField(bool $isManualField): static
    {
        $this->isManualField = $isManualField;

        return $this;
    }

    public function getIsManualField(): ?bool
    {
        return $this->isManualField;
    }

    public function setIsAggregate(bool $isAggregate): static
    {
        $this->isAggregate = $isAggregate;

        return $this;
    }

    public function getIsAggregate(): ?bool
    {
        return $this->isAggregate;
    }

    public function setUsePrefixTitle(bool $usePrefixTitle): static
    {
        $this->usePrefixTitle = $usePrefixTitle;

        return $this;
    }

    public function getUsePrefixTitle(): ?bool
    {
        return $this->usePrefixTitle;
    }

    public function getTranslationDomain(): ?string
    {
        return $this->translationDomain;
    }

    public function setTranslationDomain(?string $translationDomain): static
    {
        $this->translationDomain = $translationDomain;

        return $this;
    }

    public static function getAvailableOperators(): array
    {
        return self::$availableOperators;
    }
}

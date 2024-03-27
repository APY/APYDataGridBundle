<?php

namespace APY\DataGridBundle\Grid\Source;

use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\JoinColumn;
use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Helper\ColumnsIterator;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Mapping\Metadata\Metadata;
use APY\DataGridBundle\Grid\Row;
use APY\DataGridBundle\Grid\Rows;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\DB2Platform;
use Doctrine\DBAL\Platforms\OraclePlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountOutputWalker;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class Entity extends Source
{
    public const DOT_DQL_ALIAS_PH = '__dot__';
    public const COLON_DQL_ALIAS_PH = '__col__';

    protected ?EntityManagerInterface $manager = null;

    protected ?QueryBuilder $query = null;

    protected ?QueryBuilder $querySelectfromSource = null;

    /**
     * @var string|null e.g App\Entity\Page
     */
    protected ?string $class = null;

    /**
     * @var string e.g Cms:Page
     */
    protected string $entityName;

    /**
     * @var string|null e.g mydatabase
     */
    protected ?string $managerName;

    protected ?Metadata $metadata = null;

    protected ?ClassMetadata $ormMetadata = null;

    protected ?array $joins = null;

    protected mixed $group;

    protected ?array $groupBy = null;

    protected ?array $hints = null;

    /**
     * The QueryBuilder that will be used to start generating query for the DataGrid
     * You can override this if the querybuilder is constructed in a business-specific way
     * by an external controller/service/repository and you wish to re-use it for the datagrid.
     * Typical use-case involves an external repository creating complex default restriction (i.e. multi-tenancy etc)
     * which then will be expanded on by the datagrid.
     */
    protected ?QueryBuilder $queryBuilder = null;

    /**
     * The table alias that will be used in the query to fetch actual data.
     */
    protected string $tableAlias;

    protected mixed $prepareCountQueryCallback = null;

    /**
     * Legacy way of accessing the default alias (before it became possible to change it)
     * Please use $entity->getTableAlias() now instead of $entity::TABLE_ALIAS.
     *
     * @deprecated
     */
    private const TABLE_ALIAS = '_a';

    /**
     * @param string $entityName  e.g Cms:Page
     * @param string $managerName e.g. mydatabase
     */
    public function __construct(string $entityName, string $group = 'default', string $managerName = null)
    {
        $this->entityName = $entityName;
        $this->managerName = $managerName;
        $this->joins = [];
        $this->group = $group;
        $this->hints = [];
        $this->setTableAlias(self::TABLE_ALIAS);
    }

    public function initialise(ManagerRegistry $doctrine, Manager $manager): void
    {
        $this->manager = $doctrine->getManager($this->managerName);
        $this->ormMetadata = $this->manager->getClassMetadata($this->entityName);

        $this->class = $this->ormMetadata->getReflectionClass()->name;

        // todo autoregister mapping drivers with tag
        $manager->addDriver($this, -1);
        $this->metadata = $manager->getMetadata($this->class, $this->group);

        $this->groupBy = $this->metadata->getGroupBy();
    }

    protected function getTranslationFieldNameWithParents(Column $column): string
    {
        $name = $column->getField();

        if ($column->getIsManualField()) {
            return $column->getField();
        }

        if (\str_contains($name, '.')) {
            $previousParent = '';

            $elements = \explode('.', $name);
            while ($element = \array_shift($elements)) {
                if (\count($elements) > 0) {
                    $previousParent .= '_'.$element;
                }
            }
        } elseif (\str_contains($name, ':')) {
            $previousParent = $this->getTableAlias();
        } else {
            return $this->getTableAlias().'.'.$name;
        }

        $matches = [];
        if ($column->hasDQLFunction($matches)) {
            return $previousParent.'.'.$matches['field'];
        }

        return $column->getField();
    }

    protected function getFieldName(Column $column, bool $withAlias = false): string
    {
        $name = $column->getField();

        if ($column->getIsManualField()) {
            return $column->getField();
        }

        if (\str_contains($name, '.')) {
            $previousParent = '';

            $elements = \explode('.', $name);
            while ($element = \array_shift($elements)) {
                if (\count($elements) > 0) {
                    $parent = ('' === $previousParent) ? $this->getTableAlias() : $previousParent;
                    $previousParent .= '_'.$element;
                    $this->joins[$previousParent] = ['field' => $parent.'.'.$element, 'type' => $column->getJoinType()];
                } else {
                    $name = $previousParent.'.'.$element;
                }
            }

            $alias = $this->fromColIdToAlias($column->getId());
        } elseif (\str_contains($name, ':')) {
            $previousParent = $this->getTableAlias();
            $alias = $name;
        } else {
            return $this->getTableAlias().'.'.$name;
        }

        // Aggregate dql functions
        $matches = [];
        if ($column->hasDQLFunction($matches)) {
            if ('distinct' === \strtolower($matches['parameters'])) {
                $functionWithParameters = $matches['function'].'(DISTINCT '.$previousParent.'.'.$matches['field'].')';
            } else {
                $parameters = '';
                if ('' !== $matches['parameters']) {
                    $parameters = ', '.(\is_numeric($matches['parameters']) ? $matches['parameters'] : "'".$matches['parameters']."'");
                }

                $functionWithParameters = $matches['function'].'('.$previousParent.'.'.$matches['field'].$parameters.')';
            }

            if ($withAlias) {
                // Group by the primary field of the previous entity
                $this->query->addGroupBy($previousParent);
                $this->querySelectfromSource->addGroupBy($previousParent);

                return "$functionWithParameters as $alias";
            }

            return $alias;
        }

        if ($withAlias) {
            return "$name as $alias";
        }

        return $name;
    }

    private function fromColIdToAlias(string $colId): string
    {
        return \str_replace(['.', ':'], [self::DOT_DQL_ALIAS_PH, self::COLON_DQL_ALIAS_PH], $colId);
    }

    protected function getGroupByFieldName(string $fieldName): string
    {
        if (\str_contains($fieldName, '.')) {
            $previousParent = '';

            $elements = \explode('.', $fieldName);
            while ($element = \array_shift($elements)) {
                if (\count($elements) > 0) {
                    $previousParent .= '_'.$element;
                } else {
                    $name = $previousParent.'.'.$element;
                }
            }
        } else {
            if (($pos = \strpos($fieldName, ':')) !== false) {
                $fieldName = \substr($fieldName, 0, $pos);
            }

            return $this->getTableAlias().'.'.$fieldName;
        }

        return $name;
    }

    public function getColumns(Columns $columns): void
    {
        foreach ($this->metadata->getColumnsFromMapping($columns) as $column) {
            $columns->addColumn($column);
        }
    }

    protected function normalizeOperator($operator): string
    {
        return match ($operator) {
            Column::OPERATOR_LIKE, Column::OPERATOR_LLIKE, Column::OPERATOR_RLIKE, Column::OPERATOR_NLIKE, Column::OPERATOR_SLIKE, Column::OPERATOR_LSLIKE, Column::OPERATOR_RSLIKE, Column::OPERATOR_NSLIKE => 'like',
            default => $operator,
        };
    }

    protected function normalizeValue($operator, $value): mixed
    {
        return match ($operator) {
            Column::OPERATOR_LIKE, Column::OPERATOR_NLIKE, Column::OPERATOR_SLIKE, Column::OPERATOR_NSLIKE => "%$value%",
            Column::OPERATOR_LLIKE, Column::OPERATOR_LSLIKE => "%$value",
            Column::OPERATOR_RLIKE, Column::OPERATOR_RSLIKE => "$value%",
            default => $value,
        };
    }

    /**
     * Sets the initial QueryBuilder for this DataGrid.
     */
    public function initQueryBuilder(QueryBuilder $queryBuilder): void
    {
        $this->queryBuilder = clone $queryBuilder;

        // Try to guess the new root alias and apply it to our queries+
        // as the external querybuilder almost certainly is not used our default alias
        $externalTableAliases = $this->queryBuilder->getRootAliases();
        if (\count($externalTableAliases)) {
            $this->setTableAlias($externalTableAliases[0]);
        }
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        // If a custom QB has been provided, use a copy of that one
        // Otherwise create our own basic one
        if ($this->queryBuilder instanceof QueryBuilder) {
            $qb = clone $this->queryBuilder;
        } else {
            $qb = $this->manager->createQueryBuilder();
            $qb->from($this->class, $this->getTableAlias());
        }

        return $qb;
    }

    public function execute(ColumnsIterator $columns, int $page = 0, ?int $limit = 0, $maxResults = null, int $gridDataJunction = Column::DATA_CONJUNCTION): Rows|array
    {
        $this->query = $this->getQueryBuilder();
        $this->querySelectfromSource = clone $this->query;

        $bindIndex = 123;
        $serializeColumns = [];
        $where = Column::DATA_CONJUNCTION === $gridDataJunction ? $this->query->expr()->andX() : $this->query->expr()->orX();

        $columnsById = [];
        foreach ($columns as $column) {
            $columnsById[$column->getId()] = $column;
        }

        foreach ($columns as $column) {
            // If a column is a manual field, ie a.col*b.col as myfield, it is added to select from user.
            if (false === $column->getIsManualField()) {
                $fieldName = $this->getFieldName($column, true);
                $this->query->addSelect($fieldName);
                $this->querySelectfromSource->addSelect($fieldName);
            }

            if ($column->isSorted()) {
                if ($column instanceof JoinColumn) {
                    $this->query->resetDQLPart('orderBy');
                    foreach ($column->getJoinColumns() as $columnName) {
                        $this->query->addOrderBy($this->getFieldName($columnsById[$columnName]), $column->getOrder());
                    }
                } else {
                    $this->query->orderBy($this->getFieldName($column), $column->getOrder());
                }
            }

            if ($column->isFiltered()) {
                // Some attributes of the column can be changed in this function
                $filters = $column->getFilters('entity');

                $isDisjunction = Column::DATA_DISJUNCTION === $column->getDataJunction();

                $dqlMatches = [];
                $hasHavingClause = $column->hasDQLFunction($dqlMatches) || $column->getIsAggregate();
                if (isset($dqlMatches['function']) && 'translation_agg' === $dqlMatches['function']) {
                    $hasHavingClause = false;
                }

                $sub = $isDisjunction ? $this->query->expr()->orX() : ($hasHavingClause ? $this->query->expr()->andX() : $where);

                foreach ($filters as $filter) {
                    $operator = $this->normalizeOperator($filter->getOperator());

                    $columnForFilter = (!$column instanceof JoinColumn) ? $column : $columnsById[$filter->getColumnName()];

                    $fieldName = $this->getFieldName($columnForFilter, false);
                    $bindIndexPlaceholder = "?$bindIndex";

                    if (\in_array(
                        $filter->getOperator(),
                        [Column::OPERATOR_LIKE, Column::OPERATOR_RLIKE, Column::OPERATOR_LLIKE, Column::OPERATOR_NLIKE],
                        true
                    )) {
                        if (isset($dqlMatches['function']) && 'translation_agg' === $dqlMatches['function']) {
                            $translationFieldName = $this->getTranslationFieldNameWithParents($columnForFilter);
                            $fieldName = 'LOWER('.$translationFieldName.')';
                        } elseif (isset($dqlMatches['function']) && 'role_agg' === $dqlMatches['function']) {
                            $translationFieldName = $this->getTranslationFieldNameWithParents($columnForFilter);
                            $fieldName = 'LOWER('.$translationFieldName.')';
                        } else {
                            $fieldName = "LOWER($fieldName)";
                        }
                        $bindIndexPlaceholder = "LOWER($bindIndexPlaceholder)";
                    }

                    $q = $this->query->expr()->$operator($fieldName, $bindIndexPlaceholder);

                    if (Column::OPERATOR_NLIKE === $filter->getOperator() || Column::OPERATOR_NSLIKE === $filter->getOperator()) {
                        $q = $this->query->expr()->not($q);
                    }

                    $sub->add($q);

                    if (null !== $filter->getValue()) {
                        $this->query->setParameter($bindIndex++, $this->normalizeValue($filter->getOperator(), $filter->getValue()));
                    }
                }

                if ($hasHavingClause) {
                    $this->query->andHaving($sub);
                } elseif ($isDisjunction) {
                    $where->add($sub);
                }
            }

            if ('array' === $column->getType()) {
                $serializeColumns[] = $column->getId();
            }
        }

        if ($where->count() > 0) {
            // Using ->andWhere here to make sure we preserve any other where clauses present in the query builder
            // the other where clauses may have come from an external builder
            $this->query->andWhere($where);
        }

        foreach ($this->joins as $alias => $field) {
            if (null !== $field['type'] && 'inner' === \strtolower($field['type'])) {
                $join = 'join';
            } else {
                $join = 'leftJoin';
            }

            $this->query->$join($field['field'], $alias);
            $this->querySelectfromSource->$join($field['field'], $alias);
        }

        if ($page > 0) {
            $this->query->setFirstResult($page * $limit);
        }

        if ($limit > 0) {
            if (null !== $maxResults && ($maxResults - $page * $limit < $limit)) {
                $limit = $maxResults - $page * $limit;
            }

            $this->query->setMaxResults($limit);
        } elseif (null !== $maxResults) {
            $this->query->setMaxResults($maxResults);
        }

        if (!empty($this->groupBy)) {
            $this->query->resetDQLPart('groupBy');
            $this->querySelectfromSource->resetDQLPart('groupBy');

            foreach ($this->groupBy as $field) {
                $this->query->addGroupBy($this->getGroupByFieldName($field));
                $this->querySelectfromSource->addGroupBy($this->getGroupByFieldName($field));
            }
        }

        // call overridden prepareQuery or associated closure
        $this->prepareQuery($this->query);
        $hasJoin = $this->checkIfQueryHasFetchJoin($this->query);

        $query = $this->query->getQuery();
        foreach ($this->hints as $hintKey => $hintValue) {
            $query->setHint($hintKey, $hintValue);
        }
        $items = new Paginator($query, $hasJoin);
        $items->setUseOutputWalkers(false);

        $repository = $this->manager->getRepository($this->entityName);

        // Force the primary field to get the entity in the manipulatorRow
        $primaryColumnId = null;
        foreach ($columns as $column) {
            if ($column->isPrimary()) {
                $primaryColumnId = $column->getId();

                break;
            }
        }

        // hydrate result
        $result = new Rows();

        foreach ($items as $item) {
            $row = new Row();

            foreach ($item as $key => $value) {
                $key = $this->fromAliasToColId($key);

                if (\in_array($key, $serializeColumns, true) && \is_string($value)) {
                    $value = \unserialize($value);
                }

                $row->setField($key, $value);
            }

            $row->setPrimaryField($primaryColumnId);

            // Setting the representative repository for entity retrieving
            $row->setRepository($repository);

            // call overridden prepareRow or associated closure
            if (($modifiedRow = $this->prepareRow($row)) !== null) {
                $result->addRow($modifiedRow);
            }
        }

        return $result;
    }

    private function fromAliasToColId(string $alias): string
    {
        return \str_replace([self::DOT_DQL_ALIAS_PH, self::COLON_DQL_ALIAS_PH], ['.', ':'], $alias);
    }

    public function getTotalCount(int $maxResults = null): int
    {
        // Doctrine Bug Workaround: http://www.doctrine-project.org/jira/browse/DDC-1927
        $countQueryBuilder = clone $this->query;

        $this->prepareCountQuery($countQueryBuilder);

        foreach ($countQueryBuilder->getRootAliases() as $alias) {
            $countQueryBuilder->addSelect($alias);
        }

        // From Doctrine\ORM\Tools\Pagination\Paginator::count()
        $countQuery = $countQueryBuilder->getQuery();

        // Add hints from main query, if developer wants to use additional hints (ex. gedmo translations):
        foreach ($this->hints as $hintName => $hintValue) {
            $countQuery->setHint($hintName, $hintValue);
        }

        if (!$countQuery->getHint(CountWalker::HINT_DISTINCT)) {
            $countQuery->setHint(CountWalker::HINT_DISTINCT, true);
        }

        if (false === $countQuery->getHint(Query::HINT_CUSTOM_OUTPUT_WALKER)) {
            $platform = $countQuery->getEntityManager()->getConnection()->getDatabasePlatform(); // law of demeter win

            $rsm = new ResultSetMapping();
            $rsm->addScalarResult($this->getSQLResultCasing($platform, 'dctrn_count'), 'count');

            $countQuery->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, CountOutputWalker::class);
            $countQuery->setResultSetMapping($rsm);
        } else {
            $hints = $countQuery->getHint(Query::HINT_CUSTOM_TREE_WALKERS);

            if (false === $hints) {
                $hints = [];
            }

            $hints[] = CountWalker::class;
            // $hints[] = 'APY\DataGridBundle\Grid\Helper\ORMCountWalker';
            $countQuery->setHint(Query::HINT_CUSTOM_TREE_WALKERS, $hints);
        }
        $countQuery->setFirstResult(0)->setMaxResults($maxResults);

        try {
            $data = $countQuery->getScalarResult();
            $data = \array_map('current', $data);
            $count = \array_sum($data);
        } catch (NoResultException) {
            $count = 0;
        }

        return $count;
    }

    public function getFieldsMetadata(string $class, string $group = 'default'): array
    {
        $result = [];
        foreach ($this->ormMetadata->getFieldNames() as $name) {
            $mapping = $this->ormMetadata->getFieldMapping($name);
            $values = ['title' => $name, 'source' => true];

            if (isset($mapping['fieldName'])) {
                $values['field'] = $mapping['fieldName'];
                $values['id'] = $mapping['fieldName'];
            }

            if (isset($mapping['id']) && $mapping['id']) {
                $values['primary'] = true;
            }

            switch ($mapping['type']) {
                case 'string':
                case 'text':
                    $values['type'] = 'text';
                    break;
                case 'integer':
                case 'smallint':
                case 'bigint':
                case 'float':
                case 'decimal':
                    $values['type'] = 'number';
                    break;
                case 'boolean':
                    $values['type'] = 'boolean';
                    break;
                case 'date':
                    $values['type'] = 'date';
                    break;
                case 'datetime':
                    $values['type'] = 'datetime';
                    break;
                case 'time':
                    $values['type'] = 'time';
                    break;
                case 'array':
                case 'object':
                    $values['type'] = 'array';
                    break;
            }

            $result[$name] = $values;
        }

        return $result;
    }

    public function populateSelectFilters(Columns|ColumnsIterator $columns, bool $loop = false): void
    {
        // @var $column Column
        foreach ($columns as $column) {
            $selectFrom = $column->getSelectFrom();

            if ('select' === $column->getFilterType() && ('source' === $selectFrom || 'query' === $selectFrom)) {
                // For negative operators, show all values
                if ('query' === $selectFrom) {
                    foreach ($column->getFilters('entity') as $filter) {
                        if (\in_array($filter->getOperator(), [Column::OPERATOR_NEQ, Column::OPERATOR_NLIKE, Column::OPERATOR_NSLIKE], true)) {
                            $selectFrom = 'source';
                            break;
                        }
                    }
                }

                // Dynamic from query or not ?
                $query = ('source' === $selectFrom) ? clone $this->querySelectfromSource : clone $this->query;

                $query = $query->select($this->getFieldName($column, true))
                    ->distinct()
                    ->orderBy($this->getFieldName($column), 'asc')
                    ->setFirstResult(0)
                    ->setMaxResults(null)
                    ->getQuery();
                if ('query' === $selectFrom) {
                    foreach ($this->hints as $hintKey => $hintValue) {
                        $query->setHint($hintKey, $hintValue);
                    }
                }
                $result = $query->getResult();

                $values = [];
                foreach ($result as $row) {
                    $alias = $this->fromColIdToAlias($column->getId());

                    $value = $row[$alias];

                    switch ($column->getType()) {
                        case 'array':
                            if (\is_string($value)) {
                                $value = \unserialize($value);
                            }
                            foreach ($value as $val) {
                                $values[$val] = $val;
                            }
                            break;
                        case 'simple_array':
                            if (\is_string($value)) {
                                $value = \explode(',', $value);
                            }
                            foreach ($value as $val) {
                                $values[$val] = $val;
                            }
                            break;
                        case 'number':
                            $values[$value] = $column->getDisplayedValue($value);
                            break;
                        case 'datetime':
                        case 'date':
                        case 'time':
                            $displayedValue = $column->getDisplayedValue($value);
                            $values[$displayedValue] = $displayedValue;
                            break;
                        default:
                            $values[$value] = $value;
                    }
                }

                // It avoids to have no result when the other columns are filtered
                if ('query' === $selectFrom && empty($values) && false === $loop) {
                    $column->setSelectFrom('source');
                    $this->populateSelectFilters($columns, true);
                } else {
                    if ('array' === $column->getType()) {
                        \natcasesort($values);
                    }

                    $values = $this->prepareColumnValues($column, $values);
                    $column->setValues($values);
                }
            }
        }
    }

    public function prepareCountQuery(QueryBuilder $countQueryBuilder): void
    {
        if (\is_callable($this->prepareCountQueryCallback)) {
            \call_user_func($this->prepareCountQueryCallback, $countQueryBuilder);
        }
    }

    public function manipulateCountQuery(callable $callback = null): static
    {
        $this->prepareCountQueryCallback = $callback;

        return $this;
    }

    public function delete(array $ids): void
    {
        $repository = $this->getRepository();

        foreach ($ids as $id) {
            $object = $repository->find($id);

            if (!$object) {
                throw new \RuntimeException(\sprintf('No %s found for id %s', $this->entityName, $id));
            }

            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function getRepository(): EntityRepository|ObjectRepository
    {
        return $this->manager->getRepository($this->entityName);
    }

    public function getHash(): string
    {
        return $this->entityName;
    }

    public function addHint(mixed $key, mixed $value): void
    {
        $this->hints[$key] = $value;
    }

    public function clearHints(): void
    {
        $this->hints = [];
    }

    public function setGroupBy(array $groupBy): void
    {
        $this->groupBy = $groupBy;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function setTableAlias(string $tableAlias): void
    {
        $this->tableAlias = $tableAlias;
    }

    public function getTableAlias(): string
    {
        return $this->tableAlias;
    }

    protected function checkIfQueryHasFetchJoin(QueryBuilder $qb): bool
    {
        $join = $qb->getDQLPart('join');
        if (empty($join)) {
            return false;
        }

        foreach ($join[$this->getTableAlias()] as $join) {
            if (Join::INNER_JOIN === $join->getJoinType() || Join::LEFT_JOIN === $join->getJoinType()) {
                return true;
            }
        }

        return false;
    }

    private function getSQLResultCasing(AbstractPlatform $platform, string $column): string
    {
        if ($platform instanceof DB2Platform || $platform instanceof OraclePlatform) {
            return \strtoupper($column);
        }

        if ($platform instanceof PostgreSQLPlatform) {
            return \strtolower($column);
        }

        if (\method_exists($platform, 'getSQLResultCasing')) {
            return $platform->getSQLResultCasing($column);
        }

        return $column;
    }
}

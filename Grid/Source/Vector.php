<?php

namespace APY\DataGridBundle\Grid\Source;

use APY\DataGridBundle\Grid\Column\ArrayColumn;
use APY\DataGridBundle\Grid\Column\BooleanColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\DateColumn;
use APY\DataGridBundle\Grid\Column\DateTimeColumn;
use APY\DataGridBundle\Grid\Column\NumberColumn;
use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\Column\UntypedColumn;
use APY\DataGridBundle\Grid\Columns;
use APY\DataGridBundle\Grid\Helper\ColumnsIterator;
use APY\DataGridBundle\Grid\Mapping\Metadata\Manager;
use APY\DataGridBundle\Grid\Rows;
use Doctrine\Persistence\ManagerRegistry;

class Vector extends Source
{
    protected array|object|null $data = [];

    /**
     * either a column name as a string
     *  or an array of names of columns.
     */
    protected mixed $id = null;

    /**
     * Array of columns.
     *
     * @var Column[]
     */
    protected array $columns;

    /**
     * Creates the Vector and sets its data.
     */
    public function __construct(array $data, array $columns = [])
    {
        if (!empty($data)) {
            $this->setData($data);
        }

        $this->setColumns($columns);
    }

    public function initialise(ManagerRegistry $doctrine, Manager $manager): void
    {
        if (!empty($this->data)) {
            $this->guessColumns();
        }
    }

    protected function guessColumns(): void
    {
        $guessedColumns = [];
        $dataColumnIds = \array_keys(\reset($this->data));

        foreach ($dataColumnIds as $id) {
            if (!$this->hasColumn($id)) {
                $params = [
                    'id' => $id,
                    'title' => $id,
                    'source' => true,
                    'filterable' => true,
                    'sortable' => true,
                    'visible' => true,
                    'field' => $id,
                ];
                $guessedColumns[] = new UntypedColumn($params);
            }
        }

        $this->setColumns(\array_merge($this->columns, $guessedColumns));

        // Guess on the first 10 rows only
        $iteration = \min(10, \count($this->data));

        foreach ($this->columns as $c) {
            if (!$c instanceof UntypedColumn) {
                continue;
            }

            $i = 0;
            $fieldTypes = [];

            foreach ($this->data as $row) {
                if (!isset($row[$c->getId()])) {
                    continue;
                }

                $fieldValue = $row[$c->getId()];

                if ('' !== $fieldValue && null !== $fieldValue) {
                    if (\is_array($fieldValue)) {
                        $fieldTypes['array'] = 1;
                    } elseif ($fieldValue instanceof \DateTime) {
                        if ('000000' === $fieldValue->format('His')) {
                            $fieldTypes['date'] = 1;
                        } else {
                            $fieldTypes['datetime'] = 1;
                        }
                    } elseif (\strlen($fieldValue) >= 3 && false !== \strtotime($fieldValue)) {
                        $dt = new \DateTime($fieldValue);
                        if ('000000' === $dt->format('His')) {
                            $fieldTypes['date'] = 1;
                        } else {
                            $fieldTypes['datetime'] = 1;
                        }
                    } elseif (true === $fieldValue || false === $fieldValue || 1 === $fieldValue || 0 === $fieldValue || '1' === $fieldValue || '0' === $fieldValue) {
                        $fieldTypes['boolean'] = 1;
                    } elseif (\is_numeric($fieldValue)) {
                        $fieldTypes['number'] = 1;
                    } else {
                        $fieldTypes['text'] = 1;
                    }
                }

                if (++$i >= $iteration) {
                    break;
                }
            }

            if (1 === \count($fieldTypes)) {
                $c->setType(\key($fieldTypes));
            } elseif (isset($fieldTypes['boolean'], $fieldTypes['number'])) {
                $c->setType('number');
            } elseif (isset($fieldTypes['date'], $fieldTypes['datetime'])) {
                $c->setType('datetime');
            } else {
                $c->setType('text');
            }
        }
    }

    public function getColumns(Columns $columns): void
    {
        $token = empty($this->id); // makes the first column primary by default

        foreach ($this->columns as $c) {
            if ($c instanceof UntypedColumn) {
                $column = match ($c->getType()) {
                    'date' => new DateColumn($c->getParams()),
                    'datetime' => new DateTimeColumn($c->getParams()),
                    'boolean' => new BooleanColumn($c->getParams()),
                    'number' => new NumberColumn($c->getParams()),
                    'array' => new ArrayColumn($c->getParams()),
                    default => new TextColumn($c->getParams()),
                };
            } else {
                $column = $c;
            }

            if (!$column->isPrimary()) {
                $column->setPrimary((\is_array($this->id) && \in_array($column->getId(), $this->id)) || $column->getId() === $this->id || $token);
            }

            $columns->addColumn($column);

            $token = false;
        }
    }

    public function execute(Columns|ColumnsIterator $columns, int $page = 0, ?int $limit = 0, int $maxResults = null, int $gridDataJunction = Column::DATA_CONJUNCTION): Rows|array
    {
        return $this->executeFromData($columns, $page, $limit, $maxResults);
    }

    public function populateSelectFilters($columns, $loop = false): void
    {
        $this->populateSelectFiltersFromData($columns, $loop);
    }

    public function getTotalCount(int $maxResults = null): int
    {
        return $this->getTotalCountFromData($maxResults);
    }

    public function getHash(): string
    {
        return __CLASS__.\md5(\implode('', \array_map(static function($c) { return $c->getId(); }, $this->columns)));
    }

    /**
     * @param mixed $id either a string or an array of strings
     */
    public function setId(mixed $id): void
    {
        $this->id = $id;
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    /**
     * Set a two-dimentional array.
     *
     * @throws \InvalidArgumentException
     */
    public function setData(object|array $data): static
    {
        $this->data = $data;

        if (!\is_array($this->data) || empty($this->data)) {
            throw new \InvalidArgumentException('Data should be an array with content');
        }

        // This seems to exclude ...
        if (\is_object(\reset($this->data))) {
            foreach ($this->data as $key => $object) {
                $this->data[$key] = (array) $object;
            }
        }

        // ... this other (or vice versa)
        $firstRaw = \reset($this->data);
        if (!\is_array($firstRaw) || empty($firstRaw)) {
            throw new \InvalidArgumentException('Data should be a two-dimentional array');
        }

        return $this;
    }

    public function delete(array $ids): void
    {
    }

    protected function setColumns($columns): void
    {
        $this->columns = $columns;
    }

    protected function hasColumn($id): bool
    {
        foreach ($this->columns as $c) {
            if ($id === $c->getId()) {
                return true;
            }
        }

        return false;
    }
}

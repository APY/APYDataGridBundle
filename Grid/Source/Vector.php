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

namespace APY\DataGridBundle\Grid\Source;

use APY\DataGridBundle\Grid\Column\ArrayColumn;
use APY\DataGridBundle\Grid\Column\BooleanColumn;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Column\DateColumn;
use APY\DataGridBundle\Grid\Column\DateTimeColumn;
use APY\DataGridBundle\Grid\Column\NumberColumn;
use APY\DataGridBundle\Grid\Column\TextColumn;
use APY\DataGridBundle\Grid\Column\UntypedColumn;
use APY\DataGridBundle\Grid\Rows;

/**
 * Vector is really an Array.
 *
 * @author dellamowica
 */
class Vector extends Source
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * either a column name as a string
     *  or an array of names of columns.
     *
     * @var mixed
     */
    protected $id = null;

    /**
     * Array of columns.
     *
     * @var Column[]
     */
    protected $columns;

    /**
     * Creates the Vector and sets its data.
     *
     * @param array $data
     * @param array $columns
     */
    public function __construct(array $data, array $columns = [])
    {
        if (!empty($data)) {
            $this->setData($data);
        }

        $this->setColumns($columns);
    }

    public function initialise($container)
    {
        if (!empty($this->data)) {
            $this->guessColumns();
        }
    }

    protected function guessColumns()
    {
        $guessedColumns = [];
        $dataColumnIds = array_keys(reset($this->data));

        foreach ($dataColumnIds as $id) {
            if (!$this->hasColumn($id)) {
                $params = [
                    'id'         => $id,
                    'title'      => $id,
                    'source'     => true,
                    'filterable' => true,
                    'sortable'   => true,
                    'visible'    => true,
                    'field'      => $id,
                ];
                $guessedColumns[] = new UntypedColumn($params);
            }
        }

        $this->setColumns(array_merge($this->columns, $guessedColumns));

        // Guess on the first 10 rows only
        $iteration = min(10, count($this->data));

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

                if ($fieldValue !== '' && $fieldValue !== null) {
                    if (is_array($fieldValue)) {
                        $fieldTypes['array'] = 1;
                    } elseif ($fieldValue instanceof \DateTime) {
                        if ($fieldValue->format('His') === '000000') {
                            $fieldTypes['date'] = 1;
                        } else {
                            $fieldTypes['datetime'] = 1;
                        }
                    } elseif (strlen($fieldValue) >= 3 && strtotime($fieldValue) !== false) {
                        $dt = new \DateTime($fieldValue);
                        if ($dt->format('His') === '000000') {
                            $fieldTypes['date'] = 1;
                        } else {
                            $fieldTypes['datetime'] = 1;
                        }
                    } elseif (true === $fieldValue || false === $fieldValue || 1 === $fieldValue || 0 === $fieldValue || '1' === $fieldValue || '0' === $fieldValue) {
                        $fieldTypes['boolean'] = 1;
                    } elseif (is_numeric($fieldValue)) {
                        $fieldTypes['number'] = 1;
                    } else {
                        $fieldTypes['text'] = 1;
                    }
                }

                if (++$i >= $iteration) {
                    break;
                }
            }

            if (count($fieldTypes) == 1) {
                $c->setType(key($fieldTypes));
            } elseif (isset($fieldTypes['boolean']) && isset($fieldTypes['number'])) {
                $c->setType('number');
            } elseif (isset($fieldTypes['date']) && isset($fieldTypes['datetime'])) {
                $c->setType('datetime');
            } else {
                $c->setType('text');
            }
        }
    }

    /**
     * @param \APY\DataGridBundle\Grid\Columns $columns
     */
    public function getColumns($columns)
    {
        $token = empty($this->id); //makes the first column primary by default

        foreach ($this->columns as $c) {
            if ($c instanceof UntypedColumn) {
                switch ($c->getType()) {
                    case 'date':
                        $column = new DateColumn($c->getParams());
                        break;
                    case 'datetime':
                        $column = new DateTimeColumn($c->getParams());
                        break;
                    case 'boolean':
                        $column = new BooleanColumn($c->getParams());
                        break;
                    case 'number':
                        $column = new NumberColumn($c->getParams());
                        break;
                    case 'array':
                        $column = new ArrayColumn($c->getParams());
                        break;
                    case 'text':
                    default:
                        $column = new TextColumn($c->getParams());
                        break;
                }
            } else {
                $column = $c;
            }

            if (!$column->isPrimary()) {
                $column->setPrimary((is_array($this->id) && in_array($column->getId(), $this->id)) || $column->getId() == $this->id || $token);
            }

            $columns->addColumn($column);

            $token = false;
        }
    }

    /**
     * @param \APY\DataGridBundle\Grid\Column\Column[] $columns
     * @param int                                      $page             Page Number
     * @param int                                      $limit            Rows Per Page
     * @param int                                      $maxResults       Max rows
     * @param int                                      $gridDataJunction Grid data junction
     *
     * @return Rows
     */
    public function execute($columns, $page = 0, $limit = 0, $maxResults = null, $gridDataJunction = Column::DATA_CONJUNCTION)
    {
        return $this->executeFromData($columns, $page, $limit, $maxResults);
    }

    public function populateSelectFilters($columns, $loop = false)
    {
        $this->populateSelectFiltersFromData($columns, $loop);
    }

    public function getTotalCount($maxResults = null)
    {
        return $this->getTotalCountFromData($maxResults);
    }

    public function getHash()
    {
        return __CLASS__ . md5(implode('', array_map(function ($c) { return $c->getId(); }, $this->columns)));
    }

    /**
     * sets the primary key.
     *
     * @param mixed $id either a string or an array of strings
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set a two-dimentional array.
     *
     * @param array $data
     *
     * @throws \InvalidArgumentException
     */
    public function setData($data)
    {
        $this->data = $data;

        if (!is_array($this->data) || empty($this->data)) {
            throw new \InvalidArgumentException('Data should be an array with content');
        }

        // This seems to exclude ...
        if (is_object(reset($this->data))) {
            foreach ($this->data as $key => $object) {
                $this->data[$key] = (array) $object;
            }
        }

        // ... this other (or vice versa)
        $firstRaw = reset($this->data);
        if (!is_array($firstRaw) || empty($firstRaw)) {
            throw new \InvalidArgumentException('Data should be a two-dimentional array');
        }
    }

    public function delete(array $ids)
    {
    }

    protected function setColumns($columns)
    {
        $this->columns = $columns;
    }

    protected function hasColumn($id)
    {
        foreach ($this->columns as $c) {
            if ($id === $c->getId()) {
                return true;
            }
        }

        return false;
    }
}

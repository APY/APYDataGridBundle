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

use APY\DataGridBundle\Grid\Column;

/**
 * Vector is really an Array
 * @author dellamowica
 */
class Vector extends Source
{
    /**
     * @var array
     */
    protected $fieldNames;

    /**
     * @var array
     */
    protected $fieldType;

    /**
     * @var array
     */
    protected $data;

    /**
     * either a column name as a string
     *  or an array of names of columns
     * @var mixed
     */
    protected $id = null;

    protected $items = array();

    /**
     * Creates the Vector and sets its data
     * @param array $array
     */
    public function __construct(array $array)
    {
        $this->setData($array);
    }

    public function initialise($container)
    {
        $this->fieldNames = array_keys(reset($this->data));
        $this->guessColumnsType();
    }

    protected function guessColumnsType()
    {
        // Guess on the first 10 rows only
        $iteration = min(10, count($this->data));

        foreach ($this->fieldNames as $fieldName) {
            $i = 0;
            $fieldTypes = array();

            foreach ($this->data as $row) {
                $fieldValue = $row[$fieldName];

                if ($fieldValue !== '' && $fieldValue !== null) {
                    if (is_array($fieldValue)) {
                        $fieldTypes['array'] = 1;
                    } elseif (strtotime($fieldValue) !== false) {
                        $dt = new \DateTime($fieldValue);
                        if ($dt->format('His') === '000000') {
                            $fieldTypes['date'] = 1;
                        } else {
                            $fieldTypes['datetime'] = 1;
                        }
                    } elseif ($fieldValue === true || $fieldValue === false || $fieldValue == 1 || $fieldValue == 0) {
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

            if(count($fieldTypes) == 1) {
                $this->fieldType[$fieldName] = key($fieldTypes);
            } elseif (count($fieldTypes) == 2) {
                if (isset($fieldTypes['boolean']) && isset($fieldTypes['number'])) {
                    $this->fieldType[$fieldName] = 'number';
                } elseif (isset($fieldTypes['date']) && isset($fieldTypes['datetime'])) {
                    $this->fieldType[$fieldName] = 'datetime';
                }
            } else {
                $this->fieldType[$fieldName] = 'text';
            }
        }
    }

    /**
     * @param \APY\DataGridBundle\Grid\Columns $columns
     * @return null
     */
    public function getColumns($columns)
    {
        $token = empty($this->id); //makes the first column primary by default
        foreach ($this->fieldNames as $fieldName) {
            $params = array(
                'id' => $fieldName,
                'title' => $fieldName,
                'primary' => (is_array($this->id) && in_array($fieldName, $this->id)) || $fieldName == $this->id || $token,
                'source' => true,
                'filterable' => true,
                'sortable' => true,
                'visible' => true,
                'field' => $fieldName,
            );

            switch ($this->fieldType[$fieldName]) {
                case 'text':
                    $column = new Column\TextColumn($params);
                    break;
                case 'date':
                    $column = new Column\DateColumn($params);
                    break;
                case 'datetime':
                    $column = new Column\DateTimeColumn($params);
                    break;
                case 'boolean':
                    $column = new Column\BooleanColumn($params);
                    break;
                case 'number':
                    $column = new Column\NumberColumn($params);
                    break;
                case 'array':
                    $column = new Column\ArrayColumn($params);
                    break;
            }

            $columns->addColumn($column);

            $token = false;
        }
    }

    /**
     * @param $columns \APY\DataGridBundle\Grid\Column\Column[]
     * @param $page int Page Number
     * @param $limit int Rows Per Page
     * @return \APY\DataGridBundle\Grid\Rows
     */
    public function execute($columns, $page = 0, $limit = 0, $maxResults = null)
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
        return __CLASS__.md5(implode('', $this->fieldNames));
    }

    /**
     * sets the primary key
     * @param mixed $id either a string or an array of strings
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Set a two-dimentional array
     * @param array $data
     * @throws \InvalidArgumentException
     */
    public function setData($data){
        $this->data = $data;

        if(!is_array($this->data) || empty($this->data)){
            throw new \InvalidArgumentException('Data should be an array with content');
        }

        if (is_object(reset($this->data))) {
            foreach ($this->data as $key => $object) {
                $this->data[$key] = (array) $object;
            }
        }

        $firstRaw = reset($this->data);
        if(!is_array($firstRaw) || empty($firstRaw)){
            throw new \InvalidArgumentException('Data should be a two-dimentional array');
        }
    }

    public function delete(array $ids){}
}
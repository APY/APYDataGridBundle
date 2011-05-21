<?php

namespace Sorien\DataGridBundle\Samples;

use Sorien\DataGridBundle\Source;
use Sorien\DataGridBundle\Grid;
use Sorien\DataGridBundle\Row;
use Sorien\DataGridBundle\Column\Text;
use Sorien\DataGridBundle\Column\Select;
use Sorien\DataGridBundle\Column\Range;

class Users extends Source
{
    private $grid;

    /**
     * Prepare columns
     *
     * @param  \Grid $grid
     * @return null
     */
    function prepare($columns)
    {
        $columns->addColumn(new Range('v.id', 'Id', 120));

        $textColumn = new Text('v.authors', 'Authors', 200, true, true);
        $textColumn->setCallback(function($value, $row, $router) { return '<a style="color:#F00;" href="'.$router->generate('logout').'">'.$value.'</a>'; });

        $columns->addColumn($textColumn);
        $columns->addColumn(new Select('v.mode', 'a', array('admin' => 'Admin', 'user' => 'User')));
        $columns->addColumn(new Text('v.admins', 'Admin', 200, true, true));
    }

    function execute($columns, $page)
    {
        //http://www.doctrine-project.org/docs/orm/2.0/en/reference/query-builder.html
        /*
        $query = $this->get('doctrine')->getEntityManager();
        $query->select('a');

        foreach ($this->getColumns() as $column)
        {
            if ($column->isSorted())
            {
                $query->orderBy($column->getId(), $column->getOrder());
            }

            if ($column->isFiltred())
            {
                $where = $column->filtersConnected() ? $query->expr()->xand() : $query->expr()->xor();
                foreach ($column->getFilters() as $filter)
                {
                    $where->add($column->getId().' '.$filter->getFilterOperator().''.$filter->getFilterValue());
                }

                $query->addWhere($where);
            }
        }

        $query->from('Article', 'a');*/
        //$query->setMaxResults(20);

        $data = new \SplObjectStorage();
        for ($i = 0;$i < 20; $i++)
        {
            $row = new Row();
            foreach ($columns as $column)
            {
                $row->addField($column->getId(), $column->getTitle().'-'.$i);
                if ($i == 10)
                {
                    $row->setColor('#ffd9d5');
                }
            }

            $data->attach($row);
        }

        $this->setCount(20);
        $this->setTotalCount(50);

        return $data;
    }
}
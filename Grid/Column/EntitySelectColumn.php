<?php

namespace Sorien\DataGridBundle\Grid\Column;

use Sorien\DataGridBundle\Grid\Filter;

class EntitySelectColumn extends SelectColumn
{
    private $class;

    private $em;

    public function __construct($em)
    {
        $this->em = $em;
        parent::__construct();
    }

    public function __initialize(array $params)
    {
        parent::__initialize($params);
        if (isset($params['class'])) {
            $this->setClass($params['class']);
        }

        if ($this->getField()) {
            $repository = $this->em->getRepository($this->getClass());
            $query = $repository->createQueryBuilder('a')
                ->select('DISTINCT a.'.$this->getField())
                ->getQuery();
            foreach ($query->getResult() as $result) {
                $value = $result[$this->getField()];
                $this->values[$value] = $value;
            }
        }
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getCssClass()
    {
        return 'chzn-select';
    }

    public function getType()
    {
        return 'entityselect';
    }

    public function getParentType()
    {
        return 'select';
    }
}

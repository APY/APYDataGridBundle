<?php

namespace Sorien\DataGridBundle\Grid\Column;

use Sorien\DataGridBundle\Grid\Filter;

class EntitySelectColumn extends SelectColumn
{
    private $em;

    public function __construct($em)
    {
        $this->em = $em;
        parent::__construct();
    }

    public function __initialize(array $params)
    {
        parent::__initialize($params);

        if ($this->getField()) {
            $repository = $this->em->getRepository($this->getContainer());
            // Check that the repository's entity is the same
            if ($repository->getClassName() != $this->getContainer()) {
                throw new \Exception('Repository not defined for ' . $this->getContainer());
            }
            if (! method_exists($repository, 'findDistinctByField')) {
                throw new \Exception('findDistinctByField() not defined in ' . get_class($repository));
            }
            $results = $repository->findDistinctByField($this->getField());
            foreach ($results as $result) {
                $value = $result[$this->getField()];
                $this->values[$value] = $value;
            }
        }
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

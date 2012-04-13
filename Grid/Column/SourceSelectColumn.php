<?php

namespace Sorien\DataGridBundle\Grid\Column;

use Sorien\DataGridBundle\Grid\Filter;
use Sorien\DataGridBundle\Grid\Source\DistinctFieldRepositoryInterface;

class SourceSelectColumn extends SelectColumn implements PopulatableColumnInterface
{
    /**
     * @param $source
     * @throws \Exception
     */
    public function populate($source)
    {
        if ($this->getField()) {
            $repository = $source->getRepository();
            if (! $repository instanceof DistinctFieldRepositoryInterface) {
                throw new \Exception(get_class($repository) . ' must implement DistinctFieldRepositoryInterface for SourceSelectColumn');
            }
            $results = $repository->findDistinctByField($this->getField());
            foreach ($results as $result) {
                $value = $result[$this->getField()];
                $this->values[$value] = $value;
            }
        }
    }

    public function getType()
    {
        return 'sourceselect';
    }

    public function getParentType()
    {
        return 'select';
    }
}

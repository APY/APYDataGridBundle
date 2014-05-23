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

namespace APY\DataGridBundle\Grid\Column;

class RankColumn extends BlankColumn
{
    protected $rank = 1;

    public function __initialize(array $params)
    {
        parent::__initialize($params);

        $this->setId($this->getParam('id', 'rank'));
        $this->setTitle($this->getParam('title', 'rank'));
        $this->setSize($this->getParam('size', '30'));
        $this->setAlign($this->getParam('align', 'center'));
    }

    public function renderCell($value, $row, $router)
    {
        return $this->rank++;
    }

    public function getType()
    {
        return 'rank';
    }
}

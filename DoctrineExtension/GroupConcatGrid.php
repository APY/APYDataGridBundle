<?php

namespace APY\DataGridBundle\DoctrineExtension;

use DoctrineExtensions\Query\Mysql\GroupConcat;

class GroupConcatGrid extends GroupConcat
{
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $this->separator = ', ';
        parent::parse($parser);
    }
}

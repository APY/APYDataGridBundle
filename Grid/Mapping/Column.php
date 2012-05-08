<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sorien\DataGridBundle\Grid\Mapping;

/**
 * @Annotation
 */
class Column
{
    protected $metadata;
    protected $groups;

    public function __construct($metadata)
    {
        $this->metadata = $metadata;
        $this->groups = isset($metadata['groups']) ? (array) $metadata['groups'] : array('default');
    }

    public function getMetadata()
    {
        return $this->metadata;
    }

    public function getGroups()
    {
        return $this->groups;
    }
}

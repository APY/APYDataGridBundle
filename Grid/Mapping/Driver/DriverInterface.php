<?php
namespace Sorien\DataGridBundle\Grid\Mapping\Driver;

interface DriverInterface
{
    public function getClassColumns($class);

    public function getFieldsMetadata($class);
}

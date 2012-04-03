<?php

namespace Sorien\DataGridBundle\Grid\Column;

interface PopulatableColumnInterface
{
    public function populate($source);
}
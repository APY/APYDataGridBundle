<?php

namespace Sorien\DataGridBundle;

use Sorien\DataGridBundle\Grid;

$grid = new Grid(new Users(), array());
$grid->bindRequest(array());

$grid->render();

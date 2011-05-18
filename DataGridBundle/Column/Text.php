<?php

namespace Sorien\DataGridBundle\Column;

use Sorien\DataGridBundle\Column;

class Text extends Column
{
	public function drawFilter($gridId)
	{
		return '<input type="text" style="width:100%" value="'.$this->getFilterData().'" name="'.$gridId.'['.$this->getId().'][filter]"/>';
	}
}

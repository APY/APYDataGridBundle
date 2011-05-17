<?php

namespace Sorien\DataGridBundle\Column;

use Sorien\DataGridBundle\Column;

class Text extends Column
{
	//set orders from session [grid_717127575fasdf1as7dfa1sf][a.author_id][filter]
	public function drawFilter($gridId)
	{
		return '<input type="text" onkeypress="" style="width:100%" value="'.$this->getFilterData().'" name="'.$gridId.'['.$this->getId().'][filter]"/>';
	}
}

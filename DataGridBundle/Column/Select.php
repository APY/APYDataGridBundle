<?php

namespace Sorien\DataGridBundle\Column;

use Sorien\DataGridBundle\Column;

class Select extends Column
{
	private $values;

	public function __construct($id, $title, Array $values, $size = null, $sortable = true, $visible = true)
	{
		parent::__construct($id, $title, $size, $sortable, !empty($values), $visible);
		$this->values = $values;
	}

	public function drawFilter($gridId)
	{
		$result = '<option value=""></option>';

		foreach ($this->values as $key => $value)
		{
			if ($this->getFilterData() == $key)
			{
				$result .= '<option value="'.$key.'" selected="selected">'.$value.'</option>';
			}
			else
			{
				$result .= '<option value="'.$key.'">'.$value.'</option>';
			}
		}

		return '<select name="'.$gridId.'['.$this->getId().'][filter]" onchange="this.form.submit();">'.$result.'</select>';
	}	
}

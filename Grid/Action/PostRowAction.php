<?php

namespace APY\DataGridBundle\Grid\Action;

use APY\DataGridBundle\Grid\Row;
use Symfony\Component\Form\FormBuilder;

class PostRowAction extends RowAction
{
    /**
     * @var FormBuilder
     */
    private $formBuilder;

    public function __construct($title, $route, $confirm = false, $target = '_self', array $attributes = [], $role = null)
    {
        parent::__construct($title, $route, $confirm, $target, $attributes, $role);
    }

    public function setFormBuilder(FormBuilder $formBuilder)
    {
        $this->formBuilder = $formBuilder;
    }

    public function getForm()
    {
        return $this
            ->formBuilder
            ->getForm()
            ->createView();
    }

    public function getId(Row $row)
    {
        return sprintf('%s_%s', $this->getRoute(), $row->getPrimaryFieldValue());
    }
}
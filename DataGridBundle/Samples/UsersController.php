<?php


class DefaultController extends Controller
{
    public function gridAction()
    {
        $grid = new Grid(new Users(), $this, 'filter');
        return $this->render('AdminBundle::default_index.html.twig', array('data' => $grid));
    }

    public function filterAction()
    {
        $grid = new Grid(new Users(), $this);

        $grid->addMassAction('Suspend', )

        return new RedirectResponse($this->generateUrl('grid'));
    }
}

README
======

What is DataGridBundle?
-----------------

goal is to create datagrid for Symfony2 highly inspired by Zfdatagrid and Magento Grid

 - sorting
 - magento like filters but highly customizable
 - customizable columns
 - mass actions
 - sources: doctrine(entities), xml, array ...
 - exports: xml, excell, pdf ...
 - theme support


it's just sketch, and far from final implementation

Usage
-----

    class DefaultController extends Controller
    {
        public function gridAction()
        {
            $grid = new Grid(new Users(), $this, 'filter');
            return $this->render('AdminBundle::default_index.html.twig', array('grid' => $grid->render()));
        }

        public function filterAction()
        {
            $grid = new Grid(new Users(), $this);
            return new RedirectResponse($this->generateUrl('grid'));
        }

    }
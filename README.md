What is DataGridBundle?
-----

datagrid for Symfony2 highly inspired by Zfdatagrid and Magento Grid

planed features:

 - sorting
 - magento like filters but highly customizable
 - customizable columns
 - mass actions
 - sources: doctrine(entities), xml, array ...
 - exports: xml, excel, pdf ...
 - theme support like Symfony\Bridge\Twig\Extension


it's just sketch, and far from final implementation, if you have any ideas let me know on irc, nick sorien

Compatibility
-----

Symfony2 beta 1


Usage - controller
-----

    class DefaultController extends Controller
    {
        public function gridAction()
        {
            $grid = new Grid(new Users(), $this, 'filter');
            return $this->render('YourBundle::default.html.twig', array('data' => $grid));
        }

        public function filterAction()
        {
            $grid = new Grid(new Users(), $this);
            return new RedirectResponse($this->generateUrl('grid'));
        }

    }

Usage - view
-----
template

    //second parameter is optional and defining template
    {{ grid(data, 'YourBundle::own_grid_theme_template.html.twig') }}

own grid theme template: you can override blocks - grid, grid_titles, grid_filters, grid_pager

    //file: YourBundle::own_grid_theme.html.twig
    {% extends 'DataGridBundle::datagrid.html.twig' %}
    {% block grid %}
        extended grid!
    {% endblock %}


Working preview:
<img src="http://vortex-portal.com/datagrid/grid1.png" alt="Screenshot" />

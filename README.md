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

Symfony2 beta 2


Usage - controller
-----

    class DefaultController extends Controller
    {
        public function gridAction()
        {
            /**
             * create simple grid based on your entity.
             * @hint to add custom columns or actions you need to extend Source\Doctrine class
             *
             * 1st param Source object inherited from Source class
             * 2nd param Controller
             * 3th param route to controller action which is handling grid actions (filtering, ordering ...)
             *           http://en.wikipedia.org/wiki/Post/Redirect/Get to prevent reposting until ajax support is ready
             */
            $grid = new Grid(new Doctrine('YourBundle:YourEntity'), $this, 'filter');
            return $this->render('AdminBundle::default_index.html.twig', array('data' => $grid->prepare()));
        }

        public function filterAction()
        {
            /**
             * process actions and redirect to gridAction to show changes
             */
            $grid = new Grid(new Doctrine('YourBundle:YourEntity'), $this);
            return new RedirectResponse($this->generateUrl('grid'));
        }

    }

Usage - view
-----
view:

    //second parameter is optional and defining template
    {{ grid(data, 'YourBundle::own_grid_theme_template.html.twig') }}

your own grid theme template: you can override blocks - grid, grid_titles, grid_filters, grid_rows, grid_pager, grid_massactions

    //file: YourBundle::own_grid_theme.html.twig
    {% extends 'DataGridBundle::datagrid.html.twig' %}
    {% block grid %}
        extended grid!
    {% endblock %}


Working preview:
<img src="http://vortex-portal.com/datagrid/grid1.png" alt="Screenshot" />

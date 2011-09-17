What is DataGridBundle?
-----

datagrid for Symfony2 highly inspired by Zfdatagrid and Magento Grid

last changes

 - annotations
 - ODM support

planed features:

 - sorting - done
 - paggination - done
 - customizable columns with filters - done
 - mass actions - almost done
 - sources: doctrine(entities) - done , xml, array ... - later
 - exports: xml, excel, pdf ... - later
 - theme support like Symfony\Bridge\Twig\Extension - done but wil be changed a bit
 - ajax support <- next step
 - entity anotations - done
 - ODM support - done


if you want to help or change any part according your needs im open to any idea just create PR or open issue ticket

Compatibility
-----

Symfony - 2.01

Usage - routes
-----
two routs goes to the same controller action

    grid:
        pattern:  /grid
        defaults: { _controller: YourBundle:Default:grid }

    filter:
        pattern:  /filter
        defaults: { _controller: YourBundle:Default:grid }

Usage - Grid with Doctrine ORM Entity or ODM Document as source
-----
    use Sorien\DataGridBundle\Grid;
    use Sorien\DataGridBundle\Source\Entity;
    use Sorien\DataGridBundle\Source\Document;

    class DefaultController extends Controller
    {
        public function gridAction()
        {
            /**
             * creates simple grid based on your entity.
             * @hint to add custom columns or actions you need to extend Source\Doctrine class
             *
             * 1st param Source object inherited from Source class (ORM in sample)
             * 2nd param Controller
             * 3th param route to controller action which is handling grid actions (filtering, ordering, pagination ...)
             *           until ajax support is ready
             */
            $grid = new Grid(new Entity('Bundle:Entity'), $this, 'filter');

            // or use Document source class for ODM

            $grid = new Grid(new Document('Bundle:Entity'), $this, 'filter'); // when repository extends Source\Entity

            if ($grid->isReadyForRedirect())
            {
                //data are stored, do redirect
                return new RedirectResponse($this->generateUrl('grid'));
            }
            else
            {
                //show grid
                return $this->render('YourBundle::default_index.html.twig', array('data' => $grid->prepare()));
            }
        }
    }

Usage - view
-----
view:

    //second parameter is optional and defining template
    {{ grid(data, 'YourBundle::own_grid_theme_template.html.twig') }}

your own grid theme template: you can override blocks - `grid`, `grid_titles`, `grid_filters`, `grid_rows`, `grid_pager`, `grid_actions`

    //file: YourBundle::own_grid_theme.html.twig

    {% extends 'DataGridBundle::datagrid.html.twig' %}
    {% block grid %}
        extended grid!
    {% endblock %}

cell rendering inside template

    {% block grid_column_yourcolumnname_cell %}
    {% if row.field('type') == 1 %}
    <span style="color:#f00">My row id is: {{ row.getPrimaryFieldValue() }}</span>
    {% endif %}
    {% endblock %}


Usage - Document or Entity annotations

    Entity and Document source uses doctrine annotations for type guessing, for better customization you can use own annotations

    use Sorien\DataGridBundle\Grid\Mapping as GRID;

    /**
     * Annotation Test Class
     *
     * @GRID\Entity(columns="id, ...")
     */
    class Test
    {
        /**
         * @var integer $id
         *
         * @ORM\Column(name="id", type="integer")
         * @GRID\Column(title="my own column name", size="120", type="text", visible="true", ... )
         */
        private $id;
    }

Available types for '@GRID\Column' notation

 - title [string] - own column name
 - size [int] - column width in pixels
 - type [string] - column type (Date, Range, Select, Text)
 - values [array] - options (only Select Column)
 - format [string] - format (only Date Column)
 - sortable [boolean]- turns on or off column sorting
 - filterable [boolean] - turns on or off visibility of column filter
 - source [boolean] - turns on or off column visibility for Source Class
 - visible [boolean] -  turns on or off column visibility

Available types for '@GRID\Column' notation

 - columns [array] order of columns in grid
 - filterable [bool] turns on or off visibility of all columns

Usage - Examples

    // Adding custom column to source - will be also possible by annotation in short time

    $source = new Entity('Test:Test');
    $source->setPrepareCallback(function ($columns){
        $columns->addColumn(new Column(array('id' => 'callbacks', 'size' => '54', 'sortable' => false, 'filterable' => false, 'source' => false)));
    });

    $grid = new Grid($source, $this, 'path');


Working preview
-----
<img src="http://vortex-portal.com/datagrid/grid2.png" alt="Screenshot" />

What is DataGridBundle?
-----

datagrid for Symfony2 highly inspired by Zfdatagrid and Magento Grid

todo:

 - exports: xml, excel, pdf ... - later
 - theme support like Symfony\Bridge\Twig\Extension - done but wil be changed a bit
 - ajax support

if you want to help or change any part according your needs im open to any idea, just create PR or open issue ticket

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

Usage - Grid with ORM /ODM as source
-----
    use Sorien\DataGridBundle\Grid\Source\Entity;
    use Sorien\DataGridBundle\Grid\Source\Document;

    class DefaultController extends Controller
    {
        public function gridAction()
        {
            // creates simple grid based on your entity (ORM)
            $grid = $this->get('grid')->setSource(new Entity('Bundle:Entity'))->setRoute('filter');

            // or use Document source class for ODM
            $grid = $this->get('grid')->setSource(new Document('Bundle:Entity'))->setRoute('filter');

            if ($grid->isReadyForRedirect())
            {
                //data are stored, do redirect
                return new RedirectResponse($this->generateUrl('grid'));
            }
            else
            {
                // to obtain data for template you need to call prepare function
                return $this->render('YourBundle::default_index.html.twig', array('data' => $grid->prepare()));
            }
        }
    }

Usage - view
-----
view:

    //2nd parameter is optional and defines template
    //3th parameter is optional and defines grid id, like calling $grid->setId() from controller
    {{ grid(data, 'YourBundle::own_grid_theme_template.html.twig', 'custom_grid_id') }}

your own grid theme template: you can override blocks - `grid`, `grid_titles`, `grid_filters`, `grid_rows`, `grid_pager`, `grid_actions`

    //file: YourBundle::own_grid_theme.html.twig

    {% extends 'DataGridBundle::blocks.html.twig' %}
    {% block grid %}
        extended grid!
    {% endblock %}

custom cell rendering inside template

    {% block grid_column_yourcolumnid_cell %}
    <span style="color:#f00">My row id is: {{ row.getPrimaryFieldValue() }}</span>
    {% endblock %}

custom filter rendering inside template

    {% block grid_column_yourcolumnname_filter %}
    <span style="color:#f00">My custom filter</span>
    {% endblock %}

Usage - Document or Entity annotations

    Entity and Document source uses doctrine annotations for type guessing, for better customization you can use own annotations

    use Sorien\DataGridBundle\Grid\Mapping as GRID;

    /**
     * Annotation Test Class
     *
     * @GRID\Source(columns="id, ...")
     * @GRID\Column(id="attached1", size="120", type="text") //add custom column to grid, id has to be specified
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

 - id [string] - column id - default is property name, Source overrides it to field name
 - title [string] - own column name
 - size [int] - column width in pixels
 - type [string] - column type (Date, Range, Select, Text)
 - values [array] - options (only Select Column)
 - format [string] - format (only Date Column)
 - sortable [boolean]- turns on or off column sorting
 - filterable [boolean] - turns on or off visibility of column filter
 - source [boolean] - turns on or off column visibility for Source class
 - visible [boolean] -  turns on or off column visibility
 - primary [boolean] - sets column as primary - default is primary key form Entity/Document

Available types for '@GRID\Source' notation

 - columns [string] order of columns in grid (columns are separated by ",")
 - filterable [bool] turns on or off visibility of all columns

Examples
-----
    // Adding custom column from controller
    $source = new Entity('Test:Test');
    $source->setCallback(Source::EVENT_PREPARE, function ($columns, $actions){
        $columns->addColumn(new Column(array('id' => 'callbacks', 'size' => '54', 'sortable' => false, 'filterable' => false, 'source' => false)));
        $actions->addAction('Delete', 'YourProject\YourBundle\Controller\YourControllerClass::yourDeleteMethod');
    });

    //modify returned items from source
    $entity->setCallback(Source::EVENT_PREPARE_QUERY, function ($query) {
            $query->setMaxResults(1);
    });

    $gridManager = $this->get('grid');
    
    // Configure limits and first page shown before the source
    $gridManager->setLimits(array('3' => '3', '6' => '6', '9' => '9'));
    
    $grid = $gridManager->setSource($source)->setRoute('filter');


    // Many to One association support with `.` notation (just ORM)

    /**
     * @ORM\ManyToOne(targetEntity="Vendors")
     * @ORM\JoinColumn(name="vendor", referencedColumnName="id")
     *
     * @GRID\Column(id="vendor.name")
     */
    private $vendor;


Working preview
-----
<img src="http://vortex-portal.com/datagrid/grid2.png" alt="Screenshot" />

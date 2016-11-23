DataGrid
========

An entity
---------
```php
class Product
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var DateTime
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $status;

    // Getters and setters ...
}
```
Creating the grid from the grid builder
---------------------------------------

Creating a grid requires relatively little code because the grid objects are built with a "grid builder". 
The grid builder's purpose is to allow you to write simple grid, and have it do all the heavy-lifting of actually building the grid.

```php
class ProductController extends Controller
{

    public function listAction(Request $request)
    {    
        // Creates the builder
        $gridBuilder = $this->createGridBuilder(new Entity('MyProjectBundle:Product'), [
            'persistence'  => true,
            'route'        => 'product_list',
            'filterable'   => false,
            'sortable'     => false,
            'max_per_page' => 20,
        ]);

        // Creates columns
        $grid = $gridBuilder
            ->add('id', 'number', [
                'title'   => '#',
                'primary' => 'true',
            ])
            ->add('name', 'text')
            ->add('created_at', 'datetime', [
                'field' => 'createdAt',
            ])
            ->add('status', 'text')
            ->getGrid();

        // Handles filters, sorts, exports, ...
        $grid->handleRequest($request);

        // Renders the grid
        return $this->render('MyProjectBundle:Product:list', ['grid' => $grid]);
    }

    /**
     * @return GridBuilder
     */
    public function createGridBuilder(Source $source = null, array $options = [])
    {
        return $this->container->get('apy_grid.factory')->createBuilder('grid', $source, $options);
    }
}
```

Creating a grid from a type
---------------------------

A better practice is to build the grid in a separate, standalone PHP class, which can then be reused anywhere in your application. 
Create a new class that will house the logic for building the product grid:

```php
class ProductListType extends GridType
{
    public function buildGrid(GridBuilder $builder, array $options = [])
    {
        parent::buildGrid($builder, $options);

        $builder
            ->add('id', 'number', [
                'title'   => '#',
                'primary' => 'true',
            ])
            ->add('name', 'text')
            ->add('created_at', 'datetime', [
                'field' => 'createdAt',
            ])
            ->add('status', 'text');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'source'       => new Entity('MyProjectBundle:Product'),
            'persistence'  => true,
            'route'        => 'product_list',
            'filterable'   => false,
            'sortable'     => false,
            'max_per_page' => 20,
        ]);
    }

    public function getName()
    {
        return 'product_list';
    }
}
```

It can be used to quickly build a grid object in the controller:

```php
class ProductController extends Controller
{

    public function listAction(Request $request)
    {
        // Creates the grid from the type
        $grid = $this->createGrid(new ProductListType());

        // Handles filters, sorts, exports, ...
        $grid->handleRequest($request);

        return $this->render('MyProjectBundle:Product:list', ['grid' => $grid]);
    }

    /**
     * @return Grid
     */
    public function createGrid($type, Source $source = null, array $options = [])
    {
        $this->container->get('apy_grid.factory')->create($type, $source, $options);
    }
}
```

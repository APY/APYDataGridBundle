Getting Started With DataGridBundle
===================================

Datagrid for Symfony2 inspired by Zfdatagrid and Magento Grid but not compatible.

**Compatibility**: Symfony 2.x

The following documents are available:

1. [Installation](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/installation.md)
2. [Grid Configuration](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/grid_configuration.md)
3. [Annotations](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/annotations.md)
4. [Multiple grids](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/multiple_grids.md)
5. [Overriding Templates](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/overriding_templates.md)

## Special thanks to all contributors

Abhoryo, golovanov, touchdesign, Spea, nurikabe, print, Gregory McLean, centove, lstrojny, Benedikt Wolters, Martin Parsiegla, evan and all bug reporters

## Simple grid with ORM or ODM as source

    // MyProject\MyBundle\DefaultController.php
    namespace MyProject\MyBundle\Controller;

    use Symfony\Component\HttpFoundation\RedirectResponse;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    use Sorien\DataGridBundle\Grid\Source\Entity;
    use Sorien\DataGridBundle\Grid\Source\Document;

    class DefaultController extends Controller
    {
        public function myGridAction()
        {
            // Creates simple grid based on your entity (ORM)
            $source = new Entity('MyProjectMyBundle:MyEntity');

            // or use Document source class for ODM
            $source = new Document('MyProjectMyBundle:MyDocument');

            $grid = $this->get('grid');

            // Mass actions, query and row manipulations are defined here

            $grid->setSource($source);

            // Columns, row actions are defined here

            if ($grid->isReadyForRedirect())
            {
                // Data are stored, do redirect to prevent multiple post requests
                return new RedirectResponse($grid->getRouteUrl());
            }
            else
            {
                // To obtain data for template simply pass in the grid instance
                return $this->render('MyProjectMyBundle::my_grid.html.twig', array('data' => $grid));
            }
        }
    }

## Rendering inside twig

    <!-- MyProject\MyBundle\Resources\views\my_grid.html.twig -->
    {{ grid(data) }}


Working preview with [assets](https://github.com/S0RIEN/DataGridBundle/wiki/Working-preview-assets)
-----
<img src="http://vortex-portal.com/datagrid/grid2.png" alt="Screenshot" />

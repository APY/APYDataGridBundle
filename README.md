Getting Started With DataGridBundle
===================================

Datagrid for Symfony2 highly inspired by Zfdatagrid and Magento Grid but not compatible.

**Compatibility**: Symfony 2.0+ and will follow stable releases

## Installation

### Step 1: Download DataGridBundle

Ultimately, the DataGridBundle files should be downloaded to the
`vendor/bundles/Sorien/DataGridBundle` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using the vendors script**

Add the following lines in your `deps` file:

```
[DataGridBundle]
    git=git://github.com/S0RIEN/DataGridBundle.git
    target=bundles/Sorien/DataGridBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, the run the following:

``` bash
$ git submodule add git://github.com/S0RIEN/DataGridBundle.git vendor/bundles/Sorien/DataGridBundle
$ git submodule update --init
```

### Step 2: Configure the Autoloader

Add the `Sorien` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'Sorien' => __DIR__.'/../vendor/bundles',
));
```

### Step 3: Enable the bundle

Finally, enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
		new Sorien\DataGridBundle\SorienDataGridBundle(),
    );
}
```

### Next Steps

Now that you have completed the basic installation and configuration of the
DataGridBundle, you are ready to learn about more advanced features and usages
of the bundle.

The following documents are available:

1. [Grid Configuration](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/grid_configuration.md)
2. [Annotations](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/annotations.md)
3. [Overriding Templates](https://github.com/S0RIEN/DataGridBundle/blob/master/Resources/doc/overriding_templates.md)

## Simple grid with ORM or ODM as source

```php
<?php
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
			// Data are stored, do redirect
			return new RedirectResponse($this->generateUrl($this->getRequest()->get('_route')));
		}
		else
		{
			// To obtain data for template you need to call prepare function
			return $this->render('MyProjectMyBundle::my_grid.html.twig', array('data' => $grid));
		}
	}
}
?>
```

```html
<!-- MyProject\MyBundle\Resources\views\my_grid.html.twig -->
{{ grid(data) }}
```

Working preview with [assets](https://github.com/S0RIEN/DataGridBundle/wiki/Working-preview-assets)
-----
<img src="http://vortex-portal.com/datagrid/grid2.png" alt="Screenshot" />

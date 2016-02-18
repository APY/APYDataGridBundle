# APY DataGrid Bundle

APYDataGridBundle is a Symfony bundle for create grids for list your entities. [APYDataGridBundle](https://github.com/APY/APYDataGridBundle) was initiated by **Stanislav Turza (Sorien)** and inspired by **Zfdatagrid and Magento Grid**.

> IMPORTANT NOTICE : this is a fork repository of [APYDataGridBundle](https://github.com/APY/APYDataGridBundle). But the current version of [APYDataGridBundle](https://github.com/APY/APYDataGridBundle) is not compatible with Symfony 3+ framework. So, I fork this repository for make a APYDataGrid bundle compatible with Symfony3+. If you want to use it for Symfony2, please use the original repository [APYDataGridBundle](https://github.com/APY/APYDataGridBundle).

> IMPORTANT NOTICE: This bundle is still under development. Any changes will be done without prior notice to consumers of this package. Of course this code will become stable at a certain point, but for now, use at your own risk.

## Prerequisites

This version of the bundle requires Symfony 3.0+.

### Translations

If you wish to use default texts provided in this bundle, you have to make sure you have translator enabled in your config.

```yaml
# app/config/config.yml
framework:
    translator: ~
```

For more inforamtion about translations, check [Symfony documentation](https://symfony.com/doc/current/book/translation.html).

## Installation

### Step 1 : Download APYDataGridBnudle using composer

For this forked version of APYDataGridBundle, update your project's composer.json file like the following :

```json
{
	"require": {
        "artscorestudio/APYDataGridBundle": "dev-master"
	},
	"repositories" : [{
        "type": "package",
        "package": {
            "name": "artscorestudio/APYDataGridBundle",
            "version": "dev-master",
            "dist" : {
				"url" : "https://github.com/artscorestudio/APYDataGridBundle/archive/master.zip",
				"type" : "zip"
			},
			"source" : {
				"url" : "https://github.com/artscorestudio/APYDataGridBundle.git",
				"type" : "git",
				"reference" : "dev-master"
			},
			"autoload": {
			    "psr-4": { "APY\\DataGridBundle\\": "" }
			}
        }
    }]
}
```

And run composer update command :

```bash
$ composer update
```

Composer will install the bundle to your project's *vendor/artscorestudio/APYDataGridBundle* directory.

### Step 2 : Enable the bundle

Enable the bundle in the kernel :

```php
// app/AppKernel.php

public function registerBundles()
{
	$bundles = array(
		// ...
		new APY\DataGridBundle\APYDataGridBundle(),
		// ...
	);
}
```

### Next Steps

Now you have completed the basic installation and configuration of the APYDataGridBundle, you are ready to learn about more advanced features and usages of the bundle.

The following documents are available :

* [APYDataGridBundle Configuration Reference](configuration.md)
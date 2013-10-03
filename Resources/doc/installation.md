Installation
============

### Step 1: Download DataGridBundle

Ultimately, the DataGridBundle files should be downloaded to the
`vendor/bundles/APY/DataGridBundle` directory.

This can be done in several ways, depending on your preference. The first
method is the standard Symfony2 method.

**Using Composer**

Add DataGridBundle in your composer.json:

```js
{
    "require": {
        "apy/datagrid-bundle": "dev-master"
    }
}
```

Now tell composer to download the bundle by running the command:

``` bash
$ php composer.phar update apy/datagrid-bundle
```

Composer will install the bundle to your project's `vendor/apy` directory.

**Using the vendors script**

Add the following lines in your `deps` file:

```
[DataGridBundle]
    git=git://github.com/Abhoryo/APYDataGridBundle.git
    target=bundles/APY/DataGridBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

**Using submodules**

If you prefer instead to use git submodules, the run the following:

``` bash
$ git submodule add git://github.com/Abhoryo/APYDataGridBundle.git vendor/bundles/APY/DataGridBundle
$ git submodule update --init
```

### Step 2: Configure the Autoloader (not needed for composer)

Add the `APY` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'APY' => __DIR__.'/../vendor/bundles',
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
        new APY\DataGridBundle\APYDataGridBundle(),
    );
}
```

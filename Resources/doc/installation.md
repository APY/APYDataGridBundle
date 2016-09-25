Installation
============

### Step 1: Download DataGridBundle using Composer

```bash
$ composer require apy/datagrid-bundle
```

### Step 2: Enable the bundle

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

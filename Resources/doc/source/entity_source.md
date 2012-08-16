Entity source
=============

Entity source supports ORM dbal.

## Usage

```php
<?php
namespace MyProject\MyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APY\DataGridBundle\Grid\Source\Entity;

class DefaultController extends Controller
{
    public function gridAction()
    {
        $source = new Entity($entity, $group, $managerName);

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        $grid->setSource($source);
        
        return $grid->getGridResponse('MyProjectMyBundle::my_grid.html.twig');
    }
}
```

## Entity::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|entity|string|_none_|Entity expression. _\<ProjectName\>\<BundleName\>:\<EntityName\>_|
|group|string|default|Group of annotations used. See [groups parameter in annotation](../columns_configuration/annotations/column_annotation_property.md#available-attributes)|
|managerName|string|null|Set this value if you want to use another manager|

## Example

```php
<?php
namespace MyProject\MyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APY\DataGridBundle\Grid\Source\Entity;

class DefaultController extends Controller
{
    public function gridAction()
    {
        $source = new Entity('MyProjectMyBundle:User');

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        $grid->setSource($source);
        
        return $grid->getGridResponse('MyProjectMyBundle::grid.html.twig');
    }
}
```

And the template:

```janjo
<!-- MyProjectMyBundle::grid.html.twig -->

{{ grid(grid) }}
```

## Unsupported features

* Entity Source doesn't support regex operator

## Known limitations

* When you use a DQL fonction on a field, \*LIKE, \*NULL and REGEX operators don't work. They are desactivated. See [Doctrine issue](http://www.doctrine-project.org/jira/browse/DDC-1858)
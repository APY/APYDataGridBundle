Document source
===============

Document source supports ODM dbal.

**Note**: Operators `Equals` and `Contains` support regular expression.

## Usage

```php
<?php
namespace MyProject\MyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APY\DataGridBundle\Grid\Source\Document;

class DefaultController extends Controller
{
    public function gridAction()
    {
        $source = new Document($entity, $group);

        /* @var $grid \APY\DataGridBundle\Grid\Grid */
        $grid = $this->get('grid');

        $grid->setSource($source);
        
        return $grid->getGridResponse('MyProjectMyBundle::my_grid.html.twig');
    }
}
```

## Document::__construct parameters

|parameter|Type|Default value|Description|
|:--:|:--|:--|:--|:--|
|entity|string|_none_|Entity expression. _\<ProjectName\>\<BundleName\>:\<DocumentName\>_|
|group|string|default|Group of annotations used. See [groups parameter in annotation](../columns_configuration/annotations/column_annotation_property.md#available-attributes)|

## Example

```php
<?php
namespace MyProject\MyBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use APY\DataGridBundle\Grid\Source\Document;

class DefaultController extends Controller
{
    public function gridAction()
    {
        $source = new Document('MyProjectMyBundle:User');

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

## Mapped fields (Referenced or embed) example

```php
/**
 * @MongoDB\Document(collection="room")
 * @GRID\Source(columns="id, gameParameters, gameParameters.maxPlayers")
 */
class Room
{
    /**
     * @var GameParameters
     * @MongoDB\EmbedOne(targetDocument=GameParameters::class)
     * @GRID\Column(field="gameParameters", visible=false)
     * @GRID\Column(field="gameParameters.maxPlayers", filterable=true, defaultOperator="eq", type="number")
     */
    private $gameParameters;
}
```


## Missing features

* GroupBy attributes and aggregate DQL functions (If someone is skilled with the mapReduce feature, contact us)
* Filter doesn't work with a ODM timestamp but it is show as a date and it can be sort

## Unsupported features

* The primary column is not filterable. (We can create a special column to manage this filter but why do you want to filter an Id ?)
* With ascending sort, null values are displayed first. Workaround, put a high number or `zz` and bind the value with the values attributes array('zz' => '', '9999999999' => '')

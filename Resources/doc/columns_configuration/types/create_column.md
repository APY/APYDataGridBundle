Create a column
===============

If the default types of column don't satisfy your application, you can create another column type or extend a existing one.

First of all you have to extends the abstract Column class and give a name to your new column.

## Example informations

We'll try to create a column to show the video of the preview in a column.

The video path is store in the $preview property

## Extend the Column class

```php
<?php

namespace APY\DataGridBundle\Grid\Column;

class VideoColumn extends Column
{
    public function getType()
    {
        return 'video';
    }
}
```

## Register your column type

In a config file, registrer your column type as a service with the tag `grid.column.extension`

```html
<services>
    ...
    <service id="grid.column.video" class="MyProject\MyBundle\Grid\Column\VideoColumn" public="false">
        <tag name="grid.column.extension" />
    </service>
    ...
</services>
```

## Test your new column

Clear your cache.  
Now you can use your new column type in annotation.

```php
<?php
...
use APY\DataGridBundle\Grid\Mapping as GRID;
...
class Movie
{
    /**
     * @ORM\Column(type="file")
     *
     * @GRID\Column(type="video")
     */
    protected $preview;     
}
```

You will only see the preview path in the column.

## Add an attribute to the annotation and disable filters and order of the column

You can add a new attribute to define the type of the player used to play the preview.

```php
<?php

namespace APY\DataGridBundle\Grid\Column;

class VideoColumn extends Column
{
    protected $playerFormat;

    public function __initialize(array $params)
    {
        parent::__initialize($params);

        $this->setPlayerFormat($this->getParam('playerFormat'));
        
        // Disable the filter of the column
        $this->setFilterable(false);
        $this->setOrder(false);
    }
    
    public function setPlayerFormat($playerFormat)
    {
        $this->playerFormat = $playerFormat;

        return $this;
    }

    public function getPlayerFormat()
    {
        return $this->playerFormat;
    }

    public function getType()
    {
        return 'video';
    }
}
```

## Display the player

In your twig template:

```janjo
<!-- MyProjectMyBundle::my_grid_template.html.twig -->
{% extends 'APYDataGridBundle::blocks.html.twig' %}

{% block grid_column_type_video_cell %}
    {# Show your player with the file path store in the variable {{ value }} #}
{% endblock grid_column_type_video_cell %}
```

## Advanced column

See the code of the default column types.
JqGrid integration
===================================

### Step 1: Jquery and Jqueryui

You've got to enable jquery and jqueryui in your template.


It can be done by including this in your main template:


``` php
{% javascripts
    '@YourBundle/Resources/public/js/jquery-1.7.1.min.js'
    '@YourBundle/Resources/public/js/jquery-ui-1.8.17.custom.min.js'
%}
<script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}


{% stylesheets 'bundles/yourbundle/css/ui-lightness/jquery-ui-1.8.17.custom.css' filter='cssrewrite' %}
            <link rel="stylesheet" type="text/css"  href="{{ asset_url }}" />
{% endstylesheets %}
```

### Step 2: JqGrid

JqGrid is included in this bundle but you have to enable it in your template:

``` php
{% javascripts
    ...
    '@SorienDataGridBundle/Resources/public/js/jqgrid/js/i18n/grid.locale-fr.js'
    '@SorienDataGridBundle/Resources/public/js/jqgrid/js/jquery.jqGrid.min.js'
%}
<script type="text/javascript" src="{{ asset_url }}"></script>
{% endjavascripts %}
```

You can change the language in the first include according to your configuration.

### Step 3: configuration

To enable jqgrid, you have to modify your configuration, ie config.yml (alpha version of configuration):

``` php
parameters:
    grid.base.class: Sorien\DataGridBundle\Grid\GridJq
    grid.twig_extension.template: SorienDataGridBundle::blocks_jqgrid.html.twig
```

### Step 4: Controller response

Check that you use this kind of response to display the grid in your controller:

``` php
 return $grid->gridResponse(array('data' => $grid));
```

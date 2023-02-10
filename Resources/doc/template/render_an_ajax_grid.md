Render an ajax grid
===================

You can load the grid with ajax interactions.  
Simply call or extend the template `@APYDataGrid/blocks_js.jquery.html.twig` instead of `@APYDataGrid/blocks.html.twig`.  
This template only works with the jQuery Javascript Framework but you can change it to manage this feature with your own Javascript Framework.


## Usage

Before : `{{ grid(data, '@APYDataGrid/blocks.html.twig') }}`  
After: `{{ grid(data, '@APYDataGrid/blocks_js.jquery.html.twig') }}` 

**Note**: The grid_search twig function doesn't need to extend this same template because its script are already included in the grid template.

#### Example

```django
{{ grid_search(data, '@APYDataGrid/blocks.html.twig') }}

{{ grid(data, '@APYDataGrid/blocks_js.jquery.html.twig') }}
```
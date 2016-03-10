# APYDataGrid Configuration Reference

All available configuration options are listed below with their default values.

```yaml
apy_data_grid:
    limits: [20, 50, 100]
    persistence: false
    theme: 'APYDataGridBundle::blocks.html.twig'
    no_data_message: "No data"
    no_result_message: "No result"
    actions_columns_size: -1
    actions_columns_title: "Actions"
    actions_columns_separator: "<br />"
    pagerfanta:
        enable: false
        view_class: "Pagerfanta\View\DefaultView"
        options: ["prev_message" => "«", "next_message" => "»"]
```
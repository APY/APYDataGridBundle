YML Configuration
================================

## YML File location

By convention, files are located in ``MyBundle/Resouces/config/grid/`` folder.

The file name must be *MyEntity*.*gridGroup*.grid.yml

* *MyEntity* : the name of your entity
* *gridGroup* : the name of the group

## Generating YML file

In order to generate you YML file, you can use a command provided by APYDataGrid

```sh 
php app/console apydatagrid:generate:grid MyBunddle:MyEntity gridGroup
```  

## Writting an YML file


### YML File content
All parameters described in the Annotation section can be used in YML file. You just have to respect indentation and Source / Colums sections.

### Example of an YML file: 

If you are familiar with doctrine YML configuration you shouldn't have problems to create YML files for APYDataGrid.

```yaml
# //src/ACSEO/Bundle/CalendrierBundle/Resources/config/grid/Article.default.grid.yml

# This is your Entity
ACSEO\Bundle\CalendrierBundle\Entity\Article:
	# Insert here source informations 
    Source:
        columns:
            id: ~
            title: ~
            content: ~
            publishedDate: ~
        groupBy: ~
        filterable: ~
    # Insert here columns informations
	Columns:
        id:
            filterable: true
            visible: true
            id: id
            title: id
        title:
            filterable: true
            visible: true
            id: title
            title: title
        content:
            filterable: true
            visible: true
            id: content
            title: content
        publishedDate:
            filterable: true
            visible: true
            id: publishedDate
            title: publishedDate
```

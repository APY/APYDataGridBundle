Create an export
================

This bundle proposes native exports such as a CSV or a JSON export and library-dependent exports such as Excel and PDF exports but everything is made that it is really easy to create your own export.  
When some exports are defined, a selector is displayed with an export button.

**Note**: An export don't export mass action and row actions columns.

## Tutorial

We'll try to create a CSV export.

1. **Create a class wherever you want and extend it with the Export class of the bundle.**

    ```php
    <?php
    namespace MyProject\MyBundle\Export;

    use APY\DataGridBundle\Grid\Export\Export;

    class CSVExport extends Export
    {
        public function computeData($grid)
        {
        
        }
    }
    ```

    **Note**: `computeData` is an abstract method of the Export class. This is the core of an export.

    #### Export::__construct parameters

    |parameter|Type|Default value|Description|
    |:--:|:--|:--|:--|:--|
    |title|string||Title of the export in the selector.|
    |fileName|string|export|Name of the export file without the extension.|
    |params|array|array()|Additionnal parameters.|
    |charset|string|UTF-8|Charset to convert the ouput of the export.|

2. **Add this export to the grid in your controller**

    ```php
    <?php
    namespace MyProject\MyBundle\Controller;

    use MyProject\MyBundle\Export\CSVExport;

    class DefaultController extends Controller
    {
        public function gridAction()
        {
            $source = new Entity('MyProjectMyBundle:User');

            $grid = $this->get('grid');

            $grid->setSource($source);
            
            $grid->addExport(new CSVExport('CSV Export', 'export'));
            
            return $grid->getGridResponse('MyProjectMyBundle::grid.html.twig');
        }
    }
    ```

    And the template:

    ```janjo
    <!-- MyProjectMyBundle::grid.html.twig -->

    {{ grid(grid) }}
    ```

    That's all. You can test your export.  
    Go to your controller page.  
    Select `CSV Export` in the export selector and click the export button.  
    A download window appears and... yes, you have a file with the name `export` with no extension and no content.

3. **Define the extension and the mime type of your export.**

    ```php
    <?php
    namespace MyProject\MyBundle\Export;

    use APY\DataGridBundle\Grid\Export\Export;

    class CSVExport extends Export
    {
        protected $fileExtension = 'csv';

        protected $mimeType = 'text/comma-separated-values';

        public function computeData($grid)
        {
        
        }
    }
    ```

4. **Define the content of the export.**

    The computeData method is the front door of an export. The grid calls this method with itself as argument.  
    The purpose of this method is to fill the content of the export.

    Try this exemple:
    ```php
    <?php
    namespace MyProject\MyBundle\Export;

    use APY\DataGridBundle\Grid\Export\Export;

    class CSVExport extends Export
    {
        protected $fileExtension = 'csv';

        protected $mimeType = 'text/comma-separated-values';

        public function computeData($grid)
        {
            $this->content = 'Hello world!';
        }
    }
    ```

    Now you have a export with a filename `export.csv` and the content `Hello world!`

5. **Get the grid data to fill the content of the export**

    The export class have some helper functions to get the data of the grid.

    #### Export::getGridData and Export::getRawGridData

    These functions return an array of titles and an array of rows.
    
    `getGridData` returns the same values displayed in your page because it retrieves data from the render of the grid. Sometimes you have some modifications of the display performed in the grid template.
    
    `getRawGridData` retrieves data from the grid object directly without calling the template of the grid but the titles are translated. 

    Exemple:
    ```php
    <?php
    array(
        'titles' => array(
            'column_id_1' => 'column_title_1',
            'column_id_2' => 'column_title_2'
        ),
        'rows' =>array(
            array(
                'column_id_1' => 'cell_value_1_1',
                'column_id_2' => 'cell_value_1_2'
            ),
            array(
                'column_id_1' => 'cell_value_2_1',
                'column_id_2' => 'cell_value_2_2'
            )
        )
    )
    ```

    **Note**: These functions return the array of titles only if titles are visible.

    #### Export::getFlatGridData and Export::getRawFlatGridData

    These functions return an flat array of rows. If titles are visible the first index of the array is the array of titles.

    Exemple:
    ```php
    <?php
    array(
        '0' => array(
            'column_id_1' => 'column_title_1',
            'column_id_2' => 'column_title_2'
        ),
        '1' => array(
             'column_id_1' => 'cell_value_1_1',
             'column_id_2' => 'cell_value_1_2'
        ),
        '2' => array(
             'column_id_1' => 'cell_value_2_1',
             'column_id_2' => 'cell_value_2_2'
        )
    )
    ```
    
    Available methods: Export::getGridTitles, Export::getRawGridTitles, Export::getGridRows and Export::getRawGridRows
    
6.  **Fill the content of the export**

    ```php
    <?php
    namespace MyProject\MyBundle\Export;

    use APY\DataGridBundle\Grid\Export\Export;

    class CSVExport extends Export
    {
        protected $fileExtension = 'csv';

        protected $mimeType = 'text/comma-separated-values';

        public function computeData($grid)
        {
            $data = $this->getFlatGridData($grid);

            // Array to dsv
            $outstream = fopen("php://temp", 'r+');

            foreach ($data as $line) {
                fputcsv($outstream, $line, ',', '"');
            }

            rewind($outstream);

            $content = '';
            while (($buffer = fgets($outstream)) !== false) {
                $content .= $buffer;
            }

            fclose($outstream);

            $this->content = $content;
        }
    }
    ```
    
    Voilà, you can export your grid in a csv file.

7. **Additional parameters**

    In French - for instance - Microsoft Excel accepts only CSV with the delimiter semi-colon `;`. You can add an additional parameter to resolve this difference.

    ```php
    <?php
    namespace MyProject\MyBundle\Export;

    use APY\DataGridBundle\Grid\Export\Export;

    class CSVExport extends Export
    {
        protected $fileExtension = 'csv';

        protected $mimeType = 'text/comma-separated-values';
        
        public function __construct($tilte, $fileName = 'export', $params = array(), $charset = 'UTF-8')
        {
            $this->parameters['delimiter'] = (isset($params['delimiter'])) $params['delimiter'] : ',';

            parent::__construct($tilte, $fileName, $params, $charset);
        }

        public function computeData($grid)
        {
            $data = $this->getFlatGridData($grid);

            // Array to dsv
            $outstream = fopen("php://temp", 'r+');

            foreach ($data as $line) {
                fputcsv($outstream, $line, $this->parameters['delimiter'], '"');
            }

            rewind($outstream);

            $content = '';
            while (($buffer = fgets($outstream)) !== false) {
                $content .= $buffer;
            }

            fclose($outstream);

            $this->content = $content;
        }
    }
    ```
    
    And call your export class with the parameter `delimiter`:
    
    ```php
    <?php
    ...
    $grid->setSource($source);

    $grid->addExport(new CSVExport('CSV Export in French', 'export', array('delimiter' => ';')));
    ...
    ```
    
8. **Define the charset of the export file**

    In French - for instance again - Microsoft Excel doesn't displayed correctly special characters. To resolve this problem, you have to define the charset of your export file.
    
    ```php
    <?php
    ...
    $grid->setSource($source);

    $grid->addExport(new CSVExport('CSV Export in French', 'export', array('delimiter' => ';'), 'Windows-1252'));
    ...
    ```
    
    **Note**: PHP extension `mb_strlen` is required.

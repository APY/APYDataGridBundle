<?php

/*
 * This file is part of the DataGridBundle.
 *
 * (c) Stanislav Turza <sorien@mail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Sorien\DataGridBundle\Grid;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sorien\DataGridBundle\Grid\Columns;
use Sorien\DataGridBundle\Grid\Rows;
use Sorien\DataGridBundle\Grid\Action\MassActionInterface;
use Sorien\DataGridBundle\Grid\Action\RowActionInterface;
use Sorien\DataGridBundle\Grid\Column\Column;
use Sorien\DataGridBundle\Grid\Column\MassActionColumn;
use Sorien\DataGridBundle\Grid\Column\ActionsColumn;
use Sorien\DataGridBundle\Grid\Source\Source;

class GridJq extends Grid {

    private $ajax;

    /**
     * @param \Source\Source $source Data Source
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param string $id set if you are using more then one grid inside controller
     */
    public function __construct($container, $source = null) {
        if (!is_null($source) && !($source instanceof Source)) {
            throw new \InvalidArgumentException(sprintf('Supplied Source have to extend Source class and not %s', get_class($source)));
        }

        parent::__construct($container, $source);

        if ($this->request->isXmlHttpRequest()) {
            //ajax call
            $this->ajax = true;
        }
    }

    /**
     * Retrieve Column Data from Session and Request
     *
     * @param string $column
     * @param bool $fromRequest
     * @param bool $fromSession
     * @return null|string
     */
    protected function getData($column, $fromRequest = true, $fromSession = true) {
        $result = null;

        if ($fromSession && is_array($data = $this->session->get($this->getHash()))) {
            if (isset($data[$column])) {
                $result = $data[$column];
            }
        }

        if ($fromRequest && is_array($data = $this->request->get($this->getHash()))) {
            if (isset($data[$column])) {
                $result = $data[$column];
            }
        }

        return $result;
    }

    /**
     * Set and Store Columns data
     *
     * @return void
     */
    protected function fetchAndSaveColumnData() {
        $storage = $this->session->get($this->getHash());

        foreach ($this->columns as $column) {
//            var_dump($this->getData($column->getId()));
            $column->setData($this->getData($column->getId()));

            if (($data = $column->getData()) !== null) {
                $storage[$column->getId()] = $data;
            } else {
                unset($storage[$column->getId()]);
            }
        }

        if (!empty($storage)) {
            $this->session->set($this->getHash(), $storage);
        }
    }

    /**
     * Set and Store Initial Grid data
     *
     * @return void
     */
    protected function fetchAndSaveGridData() {

        if ($this->ajax) {

            $this->limit = $this->request->get('rows');
            $this->setPage($this->request->get('page'));

            if ($this->request->get('sidx')) {
                $myorder = array($this->request->get('sidx'), $this->request->get('sord'));
                list($columnId, $columnOrder) = $myorder;

                $column = $this->columns->getColumnById($columnId);
                if (!is_null($column)) {
                    $column->setOrder($columnOrder);
                }
            }

            if ($this->request->get('_search') == 'true') {
                $searchField = $this->request->get('searchField');
                $searchString = $this->request->get('searchString');
                $searchOper = $this->request->get('searchOper');
            }
        } else {
            parent::fetchAndSaveGridData();
        }
    }

    /**
     * Prepare Grid for Drawing
     *
     * @return Grid
     */
    public function prepare() {
        $this->rows = $this->source->execute($this->columns->getIterator(true), $this->page - 1, $this->limit);

        if (!$this->rows instanceof Rows) {
            throw new \Exception('Source have to return Rows object.');
        }

        //add row actions column
        if (count($this->rowActions) > 0) {
            foreach ($this->rowActions as $column => $rowActions) {
                if ($rowAction = $this->columns->hasColumnById($column, true)) {
                    $rowAction->setRowActions($rowActions);
                } else {
                    $this->columns->addColumn(new ActionsColumn($column, 'Actions', $rowActions));
                }
            }
        }

        //add mass actions column
        if (count($this->massActions) > 0) {
            $this->columns->addColumn(new MassActionColumn($this->getHash()), 1);
        }

        $primaryColumnId = $this->columns->getPrimaryColumn()->getId();

        foreach ($this->rows as $row) {
            foreach ($this->columns as $column) {
                $row->setPrimaryField($primaryColumnId);
            }
        }

        //@todo refactor autohide titles when no title is set
        if (!$this->showTitles) {
            $this->showTitles = false;
            foreach ($this->columns as $column) {
                if (!$this->showTitles)
                    break;

                if ($column->getTitle() != '') {
                    $this->showTitles = true;
                    break;
                }
            }
        }

        //get size
        if (!is_int($this->totalCount = $this->source->getTotalCount($this->columns))) {
            throw new \Exception(sprintf('Source function getTotalCount need to return integer result, returned: %s', gettype($this->totalCount)));
        }

        return $this;
    }

    public function isReadyForRedirect() {
        $data = $this->request->get($this->getHash());
        return!empty($data) && !$this->getAjax();
    }

    public function getAjax() {
        return $this->ajax;
    }

    /**
     * Renders a view.
     *
     * @param array    $parameters An array of parameters to pass to the view
     * @param string   $view The view name
     * @param Response $response A response instance
     *
     * @return Response A Response instance
     */
    public function gridResponse(array $parameters = array(), $view = null, Response $response = null) {

        if ($this->ajax) {
            $response = new Response($this->toJson(), 200, array('Content-Type' => 'application/json'));
            return $response;
        } else {
            if ($this->isReadyForRedirect()) {
                return new RedirectResponse($this->getRouteUrl());
            } else {
                $this->ajax = true;
                if (is_null($view)) {
                    return $parameters;
                } else {

                    return $this->container->get('templating')->renderResponse($view, $parameters, $response);
                }
            }
        }
    }

    public function setSource($source) {
        if (!$this->ajax) {
            if (!is_null($this->source)) {
                throw new \InvalidArgumentException('Source can be set just once.');
            }

            if (!($source instanceof Source)) {
                throw new \InvalidArgumentException('Supplied Source have to extend Source class.');
            }

            $this->source = $source;

            $this->source->initialise($this->container);

            //get cols from source
            $this->source->getColumns($this->columns);

            //generate hash
            $this->createHash();

            return $this;
        } else {
            return parent::setSource($source);
        }
    }

    public function toJson() {
        $this->prepare();
        $responce['page'] = $this->getPage();
        $responce['total'] = $this->getPageCount();
        $responce['records'] = $this->getTotalCount();

        $i = 0;
        foreach ($this->getRows() as $row) {
            $primaryColumn = $this->getColumns()->getPrimaryColumn();
            $responce['rows'][$i]['id'] = $row->getField($primaryColumn->getId());
            $valField = array();

            foreach ($this->getColumns() as $column) {
                $valField[] = $row->getField($column->getId());
            }
            $responce['rows'][$i]['cell'] = $valField;
            $i++;
        }

        return json_encode($responce);
    }

    public function getJS() {
        $str = <<<EOF
<script type="text/javascript">
jQuery("%s").jqGrid({ 
	url:'%s', 
	datatype: "json", 
	colNames:[%s], 
	colModel:[
	 %s
	], 
	rowNum:%d, 
	rowList:[%s], 
	pager: '%s', 
	sortname: 'id', 
	viewrecords: true, 
	sortorder: "asc", 
	caption:"Grid title" 
}); 
jQuery("%s").jqGrid('navGrid','%s',{search:false,edit:false,add:false,del:false});
</script>
EOF;

        $tabCols = array();
        $ColsModel = array();
        foreach ($this->getColumns() as $column) {
            $tabCols[] = '\'' . $column->getId() . '\'';

            if ($column->getSize() > 1) {
                $width = ', width:' . $column->getSize();
            } else {
                $width = '';
            }

            if ($column->getAlign() != 'left') {
                $align = ', align:\'' . $column->getAlign() . '\'';
            } else {
                $align = '';
            }

            //sortable:false

            $ColsModel[] = '{name:\'' . $column->getTitle() . '\', index:\'' . $column->getTitle() . '\'' . $width . $align . '}';
        }

        $lstCols = implode(',', $tabCols);
        $lstModel = implode(',', $ColsModel);

        $str = sprintf($str, '#grid_' . $this->getHash(), $this->getRouteUrl() . '?q=2', $lstCols, $lstModel, $this->getCurrentLimit(), implode(',', $this->getLimits()), '#pager_' . $this->getHash(), '#grid_' . $this->getHash(), '#pager_' . $this->getHash());


        return $str;
    }

}


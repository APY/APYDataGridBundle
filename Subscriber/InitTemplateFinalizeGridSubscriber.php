<?php

namespace APY\DataGridBundle\Subscriber;

use APY\DataGridBundle\Grid\Action\RowAction;
use APY\DataGridBundle\Grid\Column\Column;
use APY\DataGridBundle\Grid\Grid;
use APY\DataGridBundle\Grid\Row;
use Madisoft\UiBundle\Event\InitTemplateFinalizeEvent;
use Madisoft\UiBundle\Templating\Event\PreRenderTemplateEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Subscriber per grid (generica): ascolta su eventi (ajaxAction) sollevati dal PreRenderListener.
 */
class InitTemplateFinalizeGridSubscriber implements  EventSubscriberInterface
{
    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var Router
     */
    private $router;

    const GRID_ACTION = 'grid';
    const GRID_EDIT_ACTION = 'grid_edit';
    const FILTER_GRID_ACTION = 'filterGrid';

    public static function getSubscribedEvents()
    {
        return [
            InitTemplateFinalizeEvent::EVENT_NAME . '.' . self::GRID_ACTION        => 'finalizeInitTemplateGrid',
            InitTemplateFinalizeEvent::EVENT_NAME . '.' . self::GRID_EDIT_ACTION   => 'finalizeInitTemplateGridEdit',
            InitTemplateFinalizeEvent::EVENT_NAME . '.' . self::FILTER_GRID_ACTION => 'finalizeInitTemplateFilterGrid',
        ];
    }

    public function __construct(EngineInterface $templating, Router $router)
    {
        $this->templating = $templating;
        $this->router = $router;
    }

    public function finalizeInitTemplateGrid(InitTemplateFinalizeEvent $initTemplateFinalizeEvent)
    {
        /** @var PreRenderTemplateEvent $preRenderTemplateEvent */
        $preRenderTemplateEvent = $initTemplateFinalizeEvent->getPreRenderTemplateEvent();
        /** @var $request Request */
        $request = $preRenderTemplateEvent->getRequest();
        $controllerResult = $initTemplateFinalizeEvent->getControllerResult();

        // @TODO in questo caso abbiamo una POST con errore quindi vogliamo renderizzare ancora la form,
        // ma visto che la ajaxAction è 'grid' si entra in questo metodo e non in 'finalizeInitTemplateGridEdit'.
        // E' necessario ripassare invece in 'finalizeInitTemplateGridEdit' perchè dobbiamo sostituire nelle queryString
        // i parametri per continuare a far funzionare l'ajax
        // REFACTORING: forse meglio ragionare su GET e POST e sullo stato di un eventuale oggetto form (valido o no)
        if (!array_key_exists('grid', $controllerResult)) {
            $this->finalizeInitTemplateGridEdit($initTemplateFinalizeEvent);
        }

        if (array_key_exists('grid', $controllerResult)) {
            /** @var Grid $grid */
            $grid = $controllerResult['grid'];

            $property = empty($request->query->get('property')) ? '__toString' : $request->query->get('property');
            $role = $request->query->get('role');
            switch ($role) {
                case 'AREA_ALUNNI_ALUNNI':
                case 'AREA_ALUNNI_DOCENTI':
                case 'AREA_ALUNNI_TUTORI':
                case 'AREA_ALUNNI_ATA':
                case 'AREA_PROTOCOLLO_ANAGRAFICHE':
                    $idRendered = 'getAnagraficaId';
                    break;
                case 'AREA_ALUNNI_ANAGRAFICHE_GRUPPI':
                    $idRendered = $request->query->get('idrendered') == 'undefined' ? 'id' : $request->query->get('idrendered');
                    break;
                default:
                    $idRendered = $request->query->get('idrendered') == 'undefined' ? 'id' : $request->query->get('idrendered');
                    break;
            }
            $routeName = $request->query->get('routeName');

            $grid->setRouteParameter('property', $property);
            $grid->setRouteParameter('idrendered', $idRendered);
            $grid->setRouteParameter('ajaxAction', self::GRID_ACTION);
            $grid->setRouteParameter('routeName', $routeName);

            if ($grid->hasColumn('__action')) {
                $grid->getColumn('__action')->setVisible(false);
            }
            if ($grid->hasColumn('azioni')) {
                $this->manipolaColonnaAzioni($grid, $property, $idRendered, $routeName);
            }

            //Cambio il template (è quello che mostra la manina per l'associazione)
            $controllerResult = array_merge($controllerResult, [
                'gridTemplate' => 'APYDataGridBundle::gridDialog.html.twig',
                'property'     => $property,
                'idRendered'   => $idRendered,
            ]);

            $template = $request->get('_template');
            if ($template instanceof Template) {
                $template = $template->getTemplate();
            }

            if ($template instanceof TemplateReferenceInterface) {
                $templateName = $template->getLogicalName();
            } else {
                $templateName = $template;
            }

            $preRenderTemplateEvent->setResponse($this->templating->renderResponse(
                $templateName,
                $controllerResult
            ));
        }
    }

    public function finalizeInitTemplateGridEdit(InitTemplateFinalizeEvent $initTemplateFinalizeEvent)
    {
        /** @var PreRenderTemplateEvent $preRenderTemplateEvent */
        $preRenderTemplateEvent = $initTemplateFinalizeEvent->getPreRenderTemplateEvent();

        /** @var $request Request */
        $request = $preRenderTemplateEvent->getRequest();
        /* @var FormView $formView */
        $controllerResult = $initTemplateFinalizeEvent->getControllerResult();
        $formView = $controllerResult['form'];
        $routeName = $request->query->get('routeName');
        if ($request->getMethod() == Request::METHOD_GET || !$formView->vars['valid']) {
            /*
             * in caso di edit non voglio seguire le redirect (in linea generale). Abbiamo scelto
             * di porre queste variabili qui per evitare di doverle ripetere in ogni template (cosa che è comunque
             * dovrebbe possibile in caso di customizzazione di una deteriminata action sulla grid)
             */
            $controllerResult = array_merge($controllerResult, [
                'form_submit_path'    => $request->get('_route'),
                'form_is_custom_path' => true,
                'form_submit_params'  => [
                    'property'       => $request->query->get('property'),
                    'idrendered'     => $request->query->get('idrendered'),
                    'ajaxAction'     => self::GRID_ACTION,
                    'routeName'      => $routeName,
                    'followRedirect' => 0,
                ],
            ]);

            $template = $request->get('_template');
            if ($template instanceof Template) {
                $template = $template->getTemplate();
            }

            if ($template instanceof TemplateReferenceInterface) {
                $templateName = $template->getLogicalName();
            } else {
                $templateName = $template;
            }

            $preRenderTemplateEvent->setResponse($this->templating->renderResponse(
                $templateName,
                $controllerResult
            ));
        }
    }

    public function finalizeInitTemplateFilterGrid(InitTemplateFinalizeEvent $initTemplateFinalizeEvent)
    {
        /** @var PreRenderTemplateEvent $preRenderTemplateEvent */
        $preRenderTemplateEvent = $initTemplateFinalizeEvent->getPreRenderTemplateEvent();

        /** @var $request Request */
        $request = $preRenderTemplateEvent->getRequest();
        /* @var FormView $formView */
        $controllerResult = $initTemplateFinalizeEvent->getControllerResult();
        $formView = $controllerResult['form'];
        $routeName = $request->query->get('routeName');
        if ($request->getMethod() == Request::METHOD_GET || !$formView->vars['valid']) {
            $controllerResult = array_merge($controllerResult, [
                'form_submit_params' => [
                    'property'       => $request->query->get('property'),
                    'idrendered'     => $request->query->get('idrendered'),
                    'ajaxAction'     => self::GRID_ACTION,
                    'routeName'      => $routeName,
                    'followRedirect' => 0,
                ],
            ]);

            $template = $request->get('_template');
            if ($template instanceof Template) {
                $template = $template->getTemplate();
            }

            if ($template instanceof TemplateReferenceInterface) {
                $templateName = $template->getLogicalName();
            } else {
                $templateName = $template;
            }

            $preRenderTemplateEvent->setResponse($this->templating->renderResponse(
                $templateName,
                $controllerResult
            ));
        }
    }

    /**
     * Se in grid, faccio sparire alcune azioni.
     *
     * @param $grid
     * @param $property
     * @param $idRendered
     * @param $routeName
     */
    protected function manipolaColonnaAzioni(Grid $grid, $property, $idRendered, $routeName)
    {
        /** @var Column $azioniColumn */
        $azioniColumn = $grid->getColumn('azioni');
        /** @var RowAction $action */
        foreach ($azioniColumn->getRowActions() as $action) {
            $action->manipulateRender(function (RowAction $action, Row $row) use ($property, $idRendered, $routeName) {
                $attributi = $action->getAttributes();
                if (stripos($attributi['class'], 'modifica') === false) {
                    $action->setAttributes(['class' => 'hidden']);
                } else {
                    $attributi['class'] = $attributi['class'] . ' modifica_ajax';
                    $action->setAttributes($attributi);
                    $action->addRouteParameters([
                        'property'              => $property,
                        'idrendered'            => $idRendered,
                        'ajaxAction'            => self::GRID_EDIT_ACTION,
                        'routeName'             => $routeName,
                        $row->getPrimaryField() => $row->getPrimaryFieldValue(),
                    ]);
                }

                return $action;
            });
        }
    }
}

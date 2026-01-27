<?php

/**
 * ContentBuilder Edit view.
 *
 * Edit view of the site interface
 *
 * @package     ContentBuilder
 * @subpackage  Site.View
 * @author      Xavier DANO
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */


namespace CB\Component\Contentbuilder\Site\View\Edit;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public $theme_css = '';
    public $theme_js = '';
    public $show_page_heading = false;
    public $page_title = '';
    public $event;
    public $record_id = 0;
    public $edit_by_type = false;
    public $latest = false;
    public $back_button = false;
    public $created = null;
    public $created_by = null;
    public $modified = null;
    public $modified_by = null;
    public $create_articles = false;
    public $apply_button_title = '';
    public $save_button_title = '';
    public $id = 0;
    public $article_options = null;
    public $article_settings = null;
    public $limited_options = false;
    public $toc = null;
    public $tpl = null;

    protected $state;
    protected $item;
    protected $form;

    public function display($tpl = null): void
    {
        $model = $this->getModel();
        if ($model) {
            $this->state = method_exists($model, 'getState') ? $model->getState() : null;
            $this->item  = method_exists($model, 'getItem') ? $model->getItem() : null;
            $this->form  = method_exists($model, 'getForm') ? $model->getForm() : null;
            $this->event = (object) [
                'afterDisplayTitle' => '',
                'beforeDisplayContent' => '',
                'afterDisplayContent' => '',
            ];

            if (is_object($this->item)) {
                $props = [
                    'theme_css', 'theme_js', 'show_page_heading', 'page_title', 'record_id',
                    'edit_by_type', 'latest', 'back_button', 'created', 'created_by',
                    'modified', 'modified_by', 'create_articles', 'apply_button_title',
                    'save_button_title', 'id', 'article_options', 'article_settings',
                    'limited_options', 'toc', 'tpl',
                ];

                foreach ($props as $prop) {
                    if (property_exists($this->item, $prop)) {
                        $this->$prop = $this->item->$prop;
                    }
                }

                // Model exposes the rendered markup as $item->template; accept null/empty here.
                if (($this->tpl === null || $this->tpl === '') && property_exists($this->item, 'template')) {
                    $this->tpl = $this->item->template;
                }

                if ($this->id === 0 && property_exists($this->item, 'form_id')) {
                    $this->id = (int) $this->item->form_id;
                }

                if ($this->record_id === 0 && property_exists($this->item, 'record_id')) {
                    $this->record_id = (int) $this->item->record_id;
                }

                if ($this->theme_css === '' && $this->theme_js === '' && property_exists($this->item, 'theme_plugin')) {
                    PluginHelper::importPlugin('contentbuilder_themes', $this->item->theme_plugin);
                    $dispatcher = Factory::getApplication()->getDispatcher();

                    $eventObj = new \Joomla\Event\Event('onContentTemplateCss', []);
                    $dispatcher->dispatch('onContentTemplateCss', $eventObj);
                    $results = $eventObj->getArgument('result') ?: [];
                    $this->theme_css = implode('', $results);

                    $eventObj = new \Joomla\Event\Event('onContentTemplateJavascript', []);
                    $dispatcher->dispatch('onContentTemplateJavascript', $eventObj);
                    $results = $eventObj->getArgument('result') ?: [];
                    $this->theme_js = implode('', $results);
                }
            }
        } else {
            $this->state = null;
            $this->item  = null;
            $this->form  = null;
            $this->event = (object) [
                'afterDisplayTitle' => '',
                'beforeDisplayContent' => '',
                'afterDisplayContent' => '',
            ];
            Factory::getApplication()->enqueueMessage(
                'ContentBuilder: Edit model not found for this request.',
                'warning'
            );
        }

        parent::display($tpl);
    }
}

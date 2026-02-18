<?php

/**
 * ContentBuilder NG Edit view.
 *
 * Edit view of the site interface
 *
 * @package     ContentBuilder NG
 * @subpackage  Site.View
 * @author      Xavier DANO
 * @copyright   Copyright (C) 2011–2026 by XDA+GIL
 * @license     GNU/GPL v2 or later
 * @link        https://breezingforms.vcmb.fr
 * @since       6.0.0  Joomla 6 compatibility rewrite.
 */


namespace CB\Component\Contentbuilder_ng\Site\View\Edit;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
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

    private function getFallbackEditThemeCss(): string
    {
        return <<<'CSS'
.cbEditableWrapper{
    max-width:1120px;
    margin:.7rem auto 1.4rem;
    padding:.85rem .95rem .95rem;
    border:1px solid rgba(36,61,86,.12);
    border-radius:.85rem;
    background:radial-gradient(circle at top right,rgba(13,110,253,.08),transparent 38%),linear-gradient(180deg,#fff 0,#f8fbff 100%);
    box-shadow:0 .55rem 1.2rem rgba(16,32,56,.08)
}
.cbEditableWrapper .cbToolBar{padding:.38rem .46rem;border:1px solid rgba(45,73,104,.14);border-radius:.72rem;background:rgba(255,255,255,.85)}
.cbEditableWrapper .cbToolBar.mb-5{margin-bottom:.85rem!important}
.cbEditableWrapper .cbToolBar .cbButton.btn{border-radius:999px;font-weight:600;font-size:.85rem;padding:.34rem .78rem}
.cbEditableWrapper fieldset.border.rounded.p-3.mb-3{padding:.68rem!important;margin-bottom:.58rem!important;border-radius:.72rem!important}
.cbEditableWrapper .mb-3{margin-bottom:.58rem!important}
.cbEditableWrapper .form-label,.cbEditableWrapper label{font-size:.86rem;margin-bottom:.22rem}
.cbEditableWrapper :is(input[type="text"],input[type="email"],input[type="number"],input[type="date"],input[type="datetime-local"],input[type="time"],input[type="url"],input[type="password"],textarea,select){min-height:2.05rem;padding:.34rem .52rem}
.cbEditableWrapper .form-select.form-select-sm,.cbEditableWrapper .form-control.form-control-sm{min-height:1.92rem;font-size:.88rem;padding-top:.24rem;padding-bottom:.24rem}
@media (max-width:767.98px){
    .cbEditableWrapper{margin-top:.45rem;padding:.72rem .64rem .78rem;border-radius:.72rem}
    .cbEditableWrapper .cbToolBar{padding:.32rem}
    .cbEditableWrapper .cbToolBar .cbButton.btn{width:100%;justify-content:center}
}
CSS;
    }

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
                    $themePlugin = (string) ($this->item->theme_plugin ?? '');
                    if ($themePlugin === '' || !PluginHelper::importPlugin('contentbuilder_ng_themes', $themePlugin)) {
                        PluginHelper::importPlugin('contentbuilder_ng_themes', 'joomla6');
                    }
                    $dispatcher = Factory::getApplication()->getDispatcher();

                    $eventObj = new \Joomla\Event\Event('onEditableTemplateCss', []);
                    $dispatcher->dispatch('onEditableTemplateCss', $eventObj);
                    $results = $eventObj->getArgument('result') ?: [];
                    $this->theme_css = trim(implode('', $results));
                    if ($this->theme_css === '') {
                        $this->theme_css = $this->getFallbackEditThemeCss();
                    }

                    $eventObj = new \Joomla\Event\Event('onEditableTemplateJavascript', []);
                    $dispatcher->dispatch('onEditableTemplateJavascript', $eventObj);
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
                Text::_('COM_CONTENTBUILDER_NG') .' : Edit model not found for this request.',
                'warning'
            );
        }

        parent::display($tpl);
    }
}

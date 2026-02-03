<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder_ng\Site\View\Details;

// No direct access
\defined('_JEXEC') or die('Restricted access');


use Joomla\CMS\Factory;
use Joomla\CMS\Event\Content\ContentPrepareEvent; 
use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use CB\Component\Contentbuilder_ng\Administrator\View\Contentbuilder_ng\HtmlView as BaseHtmlView;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderLegacyHelper;

class HtmlView extends BaseHtmlView
{
    private bool $frontend = false;
    protected $state;
    protected $item;
    protected $form;

	function display($tpl = null)
	{
		// Get data from the model
        $this->frontend = Factory::getApplication()->isClient('site');
		$subject = $this->get('Data');

		if (!$this->frontend) {
            // 1️⃣ Récupération du WebAssetManager
            $document = $this->getDocument();
            $wa = $document->getWebAssetManager();
            $wa->addInlineStyle(
                '.icon-logo_left{
                    background-image:url(' . Uri::root(true) . '/media/com_contentbuilder_ng/images/logo_left.png);
                    background-size:contain;
                    background-repeat:no-repeat;
                    background-position:center;
                    display:inline-block;
                    width:48px;
                    height:48px;
                }'
            );

            ToolbarHelper::title($subject->page_title, 'logo_left');
        }

		$event = new \stdClass();

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$db->setQuery("Select articles.`article_id` From #__contentbuilder_ng_articles As articles, #__content As content Where content.id = articles.article_id And (content.state = 1 Or content.state = 0) And articles.form_id = " . intval($subject->form_id) . " And articles.record_id = " . $db->Quote($subject->record_id));
		$article = $db->loadResult();

		$table = Table::getInstance('content');

		// required for pagebreak plugin
		Factory::getApplication()->input->set('view', 'article');

		$isNew = true;
		if ($article > 0) {
			$table->load($article);
			$isNew = false;
		}

		$table->cbrecord = $subject;
		$table->text = $table->cbrecord->template;

		$alias = $table->alias ? ContentbuilderLegacyHelper::stringURLUnicodeSlug($table->alias) : ContentbuilderLegacyHelper::stringURLUnicodeSlug($subject->page_title);
		if (trim(str_replace('-', '', $alias)) == '') {
			$datenow = Factory::getDate();
			$alias = $datenow->format("%Y-%m-%d-%H-%M-%S");
		}

		// we pass the slug with a flag in the end, and see in the end if the slug has been used in the output
		$table->slug = ($article > 0 ? $article : 0) . ':' . $alias . ':contentbuilder_ng_slug_used';

		$registry = new Registry;
		$registry->loadString($table->attribs ?? '{}', 'json');
		PluginHelper::importPlugin('content');

		// seems to be a joomla bug. if sef urls is enabled, "start" is used for paging in articles, else "limitstart" will be used
		//$limitstart = Factory::getApplication()->input->getInt('limitstart', 0);
		//$start      = Factory::getApplication()->input->getInt('start', 0);

		$limitstart = 0;

		$table->text = "<!-- class=\"system-pagebreak\"  -->\n" . $table->text;

		$dispatcher = Factory::getApplication()->getDispatcher();
		$dispatcher->dispatch(
			'onContentPrepare',
			new ContentPrepareEvent('onContentPrepare', ['com_content.article', &$table, &$registry, $limitstart])
		);

		// After title
		$eventObj = new AfterTitleEvent(
			'onContentAfterTitle',
			[
				'context' => 'com_content.article',
				'subject' => $table,
				'params'  => $registry,
				'page'    => $limitstart,
			]
		);
		$dispatcher->dispatch('onContentAfterTitle', $eventObj);
		$results = $eventObj->getArgument('result') ?: [];
		$event->afterDisplayTitle = trim(implode("\n", $results));

		// Before display
		$eventObj = new BeforeDisplayEvent(
			'onContentBeforeDisplay',
			[
				'context' => 'com_content.article',
				'subject' => $table,
				'params'  => $registry,
				'page'    => $limitstart,
			]
		);
		$dispatcher->dispatch('onContentBeforeDisplay', $eventObj);
		$results = $eventObj->getArgument('result') ?: [];
		$event->beforeDisplayContent = trim(implode("\n", $results));

		// After display
		$eventObj = new AfterDisplayEvent(
			'onContentAfterDisplay',
			[
				'context' => 'com_content.article',
				'subject' => $table,
				'params'  => $registry,
				'page'    => $limitstart,
			]
		);
		$dispatcher->dispatch('onContentAfterDisplay', $eventObj);
		$results = $eventObj->getArgument('result') ?: [];
		$event->afterDisplayContent = trim(implode("\n", $results));

		// if the slug has been used, we would like to stay in com_contentbuilder, so we re-arrange the resulting url a little
		if (strstr($subject->template, 'contentbuilder_ng_slug_used') !== false) {

			$matches = array(array(), array());
			preg_match_all("/\\\"([^\"]*contentbuilder_ng_slug_used[^\"]*)\\\"/i", $subject->template, $matches);

			foreach ($matches[1] as $match) {
				$sub = '';
				$parameters = explode('?', $match);
				if (count($parameters) == 2) {
					$parameters[1] = str_replace('&amp;', '&', $parameters[1]);
					$parameter = explode('&', $parameters[1]);
					foreach ($parameter as $par) {
						$keyval = explode('=', $par);
						if ($keyval[0] != '' && $keyval[0] != 'option' && $keyval[0] != 'id' && $keyval[0] != 'record_id' && $keyval[0] != 'view' && $keyval[0] != 'catid' && $keyval[0] != 'Itemid' && $keyval[0] != 'lang') {
							$sub .= '&' . $keyval[0] . '=' . (isset($keyval[1]) ? $keyval[1] : '');
						}
					}
				}
				$subject->template = str_replace($match, Route::_('index.php?option=com_contentbuilder&task=details.display&id=' . Factory::getApplication()->input->getInt('id') . '&record_id=' . Factory::getApplication()->input->getCmd('record_id', '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . $sub), $subject->template);
			}
		}

		// the same for the case a toc has been created
		if (isset($table->toc) && strstr($table->toc, 'contentbuilder_ng_slug_used') !== false) {

			preg_match_all("/\\\"([^\"]*contentbuilder_ng_slug_used[^\"]*)\\\"/i", $table->toc, $matches);

			foreach ($matches[1] as $match) {
				$sub = '';
				$parameters = explode('?', $match);
				if (count($parameters) == 2) {
					$parameters[1] = str_replace('&amp;', '&', $parameters[1]);
					$parameter = explode('&', $parameters[1]);
					foreach ($parameter as $par) {
						$keyval = explode('=', $par);
						if ($keyval[0] != '' && $keyval[0] != 'option' && $keyval[0] != 'id' && $keyval[0] != 'record_id' && $keyval[0] != 'view' && $keyval[0] != 'catid' && $keyval[0] != 'Itemid' && $keyval[0] != 'lang') {
							$sub .= '&' . $keyval[0] . '=' . (isset($keyval[1]) ? $keyval[1] : '');
						}
					}
				}
				$table->toc = str_replace($match, Route::_('index.php?option=com_contentbuilder&task=details.display&id=' . Factory::getApplication()->input->getInt('id') . '&record_id=' . Factory::getApplication()->input->getCmd('record_id', '') . '&Itemid=' . Factory::getApplication()->input->getInt('Itemid', 0) . $sub), $table->toc);
			}
		}

		if (!isset($table->toc)) {
			$table->toc = '';
		}

		$pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$subject->template = preg_replace($pattern, '', $subject->template);

		PluginHelper::importPlugin('contentbuilder_ng_themes', $subject->theme_plugin);

		$eventObj = new \Joomla\Event\Event('onContentTemplateCss', []);
		$dispatcher->dispatch('onContentTemplateCss', $eventObj);
		$results = $eventObj->getArgument('result') ?: [];
		$this->theme_css = implode('', $results);

		$eventObj = new \Joomla\Event\Event('onContentTemplateJavascript', []);
		$dispatcher->dispatch('onContentTemplateJavascript', $eventObj);
		$results = $eventObj->getArgument('result') ?: [];
		$this->theme_js = implode('', $results);

		$this->toc = $table->toc;
		$this->event = $event;

		$this->show_page_heading = $subject->show_page_heading;
		$this->tpl = $subject->template;
		$this->page_title = $subject->page_title;
		$this->created = $subject->created;
		$this->created_by = $subject->created_by;
		$this->modified = $subject->modified;
		$this->modified_by = $subject->modified_by;

		$this->metadesc = $subject->metadesc;
		$this->metakey = $subject->metakey;
		$this->author = $subject->author;
		$this->rights = $subject->rights;
		$this->robots = $subject->robots;
		$this->xreference = $subject->xreference;

		$this->print_button = $subject->print_button;
		$this->show_back_button = $subject->show_back_button;

		parent::display($tpl);
	}
}

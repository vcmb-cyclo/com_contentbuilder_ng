<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// no direct access

use Joomla\CMS\Factory;

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');

CBCompat::requireView();

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderViewEdit extends CBView
{
    function display($tpl = null)
    {
        //JHTML::_('behavior.tooltip');
        
        // Get data from the model
        $subject = $this->get('Data');
        
        $event = new stdClass();
        $event->afterDisplayTitle = '';
        $event->beforeDisplayContent = '';
        $event->afterDisplayContent = '';
                
        $table2 = new stdClass();
        $table2->toc = '';
        
        if($subject->edit_by_type){
        
            CBFactory::getDbo()->setQuery("Select articles.`article_id` From #__contentbuilder_articles As articles, #__content As content Where content.id = articles.article_id And (content.state = 1 Or content.state = 0) And articles.form_id = " . intval($subject->form_id) . " And articles.record_id = " . CBFactory::getDbo()->Quote($subject->record_id));
            $article = CBFactory::getDbo()->loadResult();

            $table = JTable::getInstance('content');

            // required for pagebreak plugin
            CBRequest::setVar('view', 'article');

	        $isNew = true;
	        if ($article > 0) {
		        $table->load($article);
		        $isNew = false;
	        }

	        $table->cbrecord = $subject;
	        $table->text = $table->cbrecord->template;

	        $alias = $table->alias ? contentbuilder::stringURLUnicodeSlug($table->alias) : contentbuilder::stringURLUnicodeSlug($subject->page_title);
	        if(trim(str_replace('-','',$alias)) == '') {
		        $datenow = JFactory::getDate();
		        $alias = $datenow->format("%Y-%m-%d-%H-%M-%S");
	        }

	        // we pass the slug with a flag in the end, and see in the end if the slug has been used in the output
	        $table->slug = ($article > 0 ? $article : 0) . ':' . $alias . ':contentbuilder_slug_used';

	        $registry = new JRegistry;
	        $registry->loadString($table->attribs);

	        JPluginHelper::importPlugin('content', 'breezingforms');

	        // seems to be a joomla bug. if sef urls is enabled, "start" is used for paging in articles, else "limitstart" will be used
	        $limitstart = CBRequest::getVar('limitstart', 0, '', 'int');
	        $start      = CBRequest::getVar('start', 0, '', 'int');

	        Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
	        $subject->template = $table->text;

	        $results = Factory::getApplication()->triggerEvent('onContentAfterTitle', array('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
	        $event->afterDisplayTitle = trim(implode("\n", $results));

	        $results = Factory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
	        $event->beforeDisplayContent = trim(implode("\n", $results));

	        $results = Factory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_content.article', &$table, &$registry, $limitstart ? $limitstart : $start));
	        $event->afterDisplayContent = trim(implode("\n", $results));

	        // if the slug has been used, we would like to stay in com_contentbuilder, so we re-arrange the resulting url a little
	        if(strstr($subject->template, 'contentbuilder_slug_used') !== false ){

		        $matches = array(array(),array());
		        preg_match_all("/\\\"([^\"]*contentbuilder_slug_used[^\"]*)\\\"/i", $subject->template, $matches);

		        foreach($matches[1] As $match){
			        $sub = '';
			        $parameters = explode('?', $match);
			        if(count($parameters) == 2){
				        $parameters[1] = str_replace('&amp;','&',$parameters[1]);
				        $parameter = explode('&', $parameters[1]);
				        foreach($parameter As $par){
					        $keyval = explode('=',$par);
					        if($keyval[0] != '' && $keyval[0] != 'option' && $keyval[0] != 'id' && $keyval[0] != 'record_id' && $keyval[0] != 'view' && $keyval[0] != 'catid' && $keyval[0] != 'Itemid' && $keyval[0] != 'lang'){
						        $sub .= '&'.$keyval[0].'='.(isset($keyval[1]) ? $keyval[1] : '');
					        }
				        }
			        }
			        $subject->template = str_replace($match, JRoute::_('index.php?option=com_contentbuilder&controller=details&id='.CBRequest::getInt('id').'&record_id='.CBRequest::getCmd('record_id','').'&Itemid='.CBRequest::getInt('Itemid', 0) . $sub ), $subject->template);
		        }
	        }

	        // the same for the case a toc has been created
	        if(isset($table->toc) && strstr($table->toc, 'contentbuilder_slug_used') !== false ){

		        preg_match_all("/\\\"([^\"]*contentbuilder_slug_used[^\"]*)\\\"/i", $table->toc, $matches);

		        foreach($matches[1] As $match){
			        $sub = '';
			        $parameters = explode('?', $match);
			        if(count($parameters) == 2){
				        $parameters[1] = str_replace('&amp;','&',$parameters[1]);
				        $parameter = explode('&', $parameters[1]);
				        foreach($parameter As $par){
					        $keyval = explode('=',$par);
					        if($keyval[0] != '' && $keyval[0] != 'option' && $keyval[0] != 'id' && $keyval[0] != 'record_id' && $keyval[0] != 'view' && $keyval[0] != 'catid' && $keyval[0] != 'Itemid'  && $keyval[0] != 'lang'){
						        $sub .= '&'.$keyval[0].'='.(isset($keyval[1]) ? $keyval[1] : '');
					        }
				        }
			        }
			        $table->toc = str_replace($match, JRoute::_('index.php?option=com_contentbuilder&controller=details&id='.CBRequest::getInt('id').'&record_id='.CBRequest::getCmd('record_id','').'&Itemid='.CBRequest::getInt('Itemid', 0) . $sub ), $table->toc);
		        }
	        }
            
            if(!isset($table->toc)){
                $table2->toc = '';
            }
        
            $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
            $subject->template = preg_replace($pattern, '', $subject->template);
        }
        
        if(!class_exists('cbFeMarker')){

            JToolBarHelper::title( '<span style="display:inline-block; vertical-align:middle">' . $subject->page_title . '</span>', 'logo_left.png' );
        }
        
        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $results = Factory::getApplication()->triggerEvent('onEditableTemplateCss', array());
        $theme_css = implode('', $results);
        $this->theme_css = $theme_css;
        
        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $results = Factory::getApplication()->triggerEvent('onEditableTemplateJavascript', array());
        $theme_js = implode('', $results);
        $this->theme_js = $theme_js;
        
        $this->toc = $table2->toc;
        $this->event = $event;
        $this->show_page_heading = $subject->show_page_heading;
        $this->back_button = $subject->back_button;
        $this->latest = $subject->latest;
        
        $this->limited_options = $subject->limited_options;
        $this->edit_by_type = $subject->edit_by_type;
        $this->frontend = $subject->frontend;
        $this->sectioncategories = $subject->sectioncategories;
        
        $this->ais15 = $subject->is15;
        $this->lists = $subject->lists; // special for 1.5
        $this->row = $subject->row; // special for 1.5
        $this->article_settings = $subject->article_settings;
        $this->article_options = $subject->article_options;
        $this->create_articles = $subject->create_articles;
        $this->record_id = $subject->record_id;
        $this->id = $subject->id;
        $this->tpl = $subject->template;
        $this->page_title = $subject->page_title;
        $this->created = $subject->created;
        $this->created_by = $subject->created_by;
        $this->modified = $subject->modified;
        $this->modified_by = $subject->modified_by;
        
        $this->save_button_title = $subject->save_button_title;
        $this->apply_button_title = $subject->apply_button_title;
        
        parent::display($tpl);
    }
}

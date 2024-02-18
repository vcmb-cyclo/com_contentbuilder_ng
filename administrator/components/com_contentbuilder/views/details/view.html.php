<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// no direct access

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');

CBCompat::requireView();

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderViewDetails extends CBView
{
    function display($tpl = null)
    {
        // Get data from the model
        $subject = $this->get('Data');
        
        if(!class_exists('cbFeMarker')){
            echo '
            <style type="text/css">
            .icon-48-logo_left { background-image: url(../administrator/components/com_contentbuilder/views/logo_left.png); }
            </style>
            ';
	        JToolBarHelper::title(   $subject->page_title . '</span>', 'logo_left.png' );
        }
        
        $event = new stdClass();
        
		$db = Factory::getContainer()->get(DatabaseInterface::class);
        $db->setQuery("Select articles.`article_id` From #__contentbuilder_articles As articles, #__content As content Where content.id = articles.article_id And (content.state = 1 Or content.state = 0) And articles.form_id = " . intval($subject->form_id) . " And articles.record_id = " . $db->Quote($subject->record_id));
        $article = $db->loadResult();

        $table = JTable::getInstance('content');

        jimport('joomla.version');
        $version = new JVersion();
        
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
	    JPluginHelper::importPlugin('content');

	    // seems to be a joomla bug. if sef urls is enabled, "start" is used for paging in articles, else "limitstart" will be used
	    //$limitstart = CBRequest::getVar('limitstart', 0, '', 'int');
	    //$start      = CBRequest::getVar('start', 0, '', 'int');

	    $limitstart = 0;

	    $table->text = "<!-- class=\"system-pagebreak\"  -->\n" . $table->text;
	    Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_content.article', &$table, &$registry, $limitstart));
	    $subject->template = $table->text;

	    $results = Factory::getApplication()->triggerEvent('onContentAfterTitle', array('com_content.article', &$table, &$registry, $limitstart));
	    $event->afterDisplayTitle = trim(implode("\n", $results));

	    $results = Factory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_content.article', &$table, &$registry, $limitstart));
	    $event->beforeDisplayContent = trim(implode("\n", $results));

	    $results = Factory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_content.article', &$table, &$registry, $limitstart));
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
            $table->toc = '';
        }
        
        $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
		$subject->template = preg_replace($pattern, '', $subject->template);
        
        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $results = Factory::getApplication()->triggerEvent('onContentTemplateCss', array());
        $this->theme_css = implode('', $results);
        
        JPluginHelper::importPlugin('contentbuilder_themes', $subject->theme_plugin);
        $results = Factory::getApplication()->triggerEvent('onContentTemplateJavascript', array());
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

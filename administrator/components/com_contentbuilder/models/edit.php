<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// No direct access


defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder_helpers.php');


require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper.php');
require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'plugin_helper4.php');

$pluginHelper4 = new \Joomla\CMS\Plugin\PluginHelper4();

class ContentbuilderModelEdit extends CBModel
{
    private $_record_id = 0;

    private $frontend = false;
    
    private $is15 = true;
    
    private $is16 = false;
    
    private $is30 = false;
    
    private $_menu_item = false;
    
    private $_show_back_button = true;
    
    private $_show_page_heading = true;
    
    private $_menu_filter = array();
    
    private $_menu_filter_order = array();
    
    private $_latest = false;
    
    private $_page_title = '';
    
    private $_page_heading = '';
    
    function createPathByTokens($path, array $names){
        
        if( strpos( $path, '|' ) === false ){
            return $path;
        }
        
        $path = str_replace('|', DS, $path);
        
        foreach($names As $id => $name){
            $is_array = 'STRING';
            if(is_array(CBRequest::getVar( 'cb_' . $id, ''))){
                $is_array = 'ARRAY';
            }
            $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, CBREQUEST_ALLOWRAW );
            if($is_array == 'ARRAY' && count($value)){
                $arrvals = array();
                foreach($value As $val){
                    if($val != 'cbGroupMark'){
                       $arrvals[] = $val;
                    }
                }
                $value = implode(DS, $arrvals);
            }
            if(trim($value) == ''){
                $value = '_empty_';
            }
            $path = str_replace('{'.strtolower($name).':value}', $value, $path);
        }
        
        $path = str_replace('{userid}', Factory::getApplication()->getIdentity()->get('id', 0), $path);
        $path = str_replace('{username}', Factory::getApplication()->getIdentity()->get('username', 'anonymous') . '_' . Factory::getApplication()->getIdentity()->get('id', 0), $path);
        $path = str_replace('{name}', Factory::getApplication()->getIdentity()->get('name', 'Anonymous') . '_' . Factory::getApplication()->getIdentity()->get('id', 0), $path);
        
        $_now = Factory::getDate();
        
        $path = str_replace('{date}', $_now->toSql(), $path);
        $path = str_replace('{time}', $_now->format('H:i:s'), $path);
        $path = str_replace('{date}', $_now->toSql(), $path);
        $path = str_replace('{datetime}', $_now->format('Y-m-d H:i:s'), $path);
        
        $endpath = contentbuilder::makeSafeFolder($path);
        $parts = explode(DS, $endpath);
        $inner_path = '';
        foreach( $parts As $part ){
            if( !is_dir( $inner_path.$part ) ) {
                $inner_path .= DS;
            }
            JFolder::create($inner_path.$part);
            $inner_path .= $part;    
        }
        return $endpath;
    }
    
    function  __construct($config)
    {

        parent::__construct($config);

        $this->is15 = false;
        $this->is16 = false;
        $this->is30 = true;
        
        CBRequest::setVar('cb_category_id',null);
        
        $this->frontend = Factory::getApplication()->isClient('site');
        
        if($this->frontend && CBRequest::getInt('Itemid',0)){
            $this->_menu_item = true;
            
            // try menu item
	        $menu = Factory::getApplication()->getMenu();
	        $item = $menu->getActive();

	        if (is_object($item)) {
		        CBRequest::setVar('cb_category_id', $item->getParams()->get('cb_category_id', null));

		        if(CBRequest::getVar('cb_controller') == 'edit'){
			        $this->_show_back_button = $item->getParams()->get('show_back_button', null);
		        }

		        if($item->getParams()->get('cb_latest', null) !== null){
			        $this->_latest = $item->getParams()->get('cb_latest', null);
		        }

		        if($item->getParams()->get('show_page_heading', null) !== null){
			        $this->_show_page_heading = $item->getParams()->get('show_page_heading', null);
		        }

		        if($item->getParams()->get('page_title', null) !== null){
			        $this->_page_title = $item->getParams()->get('page_title', null);
		        }

		        if($item->getParams()->get('page_heading', null) !== null){
			        $this->_page_heading = $item->getParams()->get('page_heading', null);
		        }
	        }
        }
        
        $menu_filter = CBRequest::getVar('cb_list_filterhidden', null);
        
        if($menu_filter !== null){
            $lines  = explode("\n", $menu_filter);
            foreach($lines As $line){
                $keyval = explode("\t", $line);
                if(count($keyval) == 2){
                    $keyval[1] = str_replace( array("\n","\r"), "", $keyval[1] );
                    $keyval[1] = contentbuilder::execPhpValue($keyval[1]);
                    if($keyval[1] != ''){
                        $this->_menu_filter[$keyval[0]] = explode('|',$keyval[1]);
                    }
                }
            }
        }
        
        $menu_filter_order = CBRequest::getVar('cb_list_orderhidden', null);
        
        if($menu_filter_order !== null){
            $lines  = explode("\n", $menu_filter_order);
            foreach($lines As $line){
                $keyval = explode("\t", $line);
                if(count($keyval) == 2){
                    $keyval[1] = str_replace( array("\n","\r"), "", $keyval[1] );
                    if($keyval[1] != ''){
                        $this->_menu_filter_order[$keyval[0]] = intval($keyval[1]);
                    }
                }
            }
        }
        
        @natsort($this->_menu_filter_order);
        
        $this->setIds(CBRequest::getInt('id',  0), CBRequest::getCmd('record_id',  0));
        
        if(!$this->frontend){
            Factory::getLanguage()->load('com_content');
        }else{
            Factory::getLanguage()->load('com_content', JPATH_SITE . DS . 'administrator');
            Factory::getLanguage()->load('joomla', JPATH_SITE . DS . 'administrator');
        }
    }

    /*
     * MAIN DETAILS AREA
     */

    /**
     *
     * @param int $id
     */
    function setIds($id, $record_id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_record_id = $record_id;
        $this->_data = null;
    }

    private function _buildQuery(){
        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms Where id = '.intval($this->_id).' And published = 1';
    }

    /**
    * Gets the currencies
    * @return array List of currencies
    */
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, 0, 1);

            if(!count($this->_data)){
	            throw new Exception(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 404);
            }

            foreach($this->_data As $data){
                
                if(!$this->frontend && $data->display_in == 0){
					throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
                }else if($this->frontend && $data->display_in == 1){
	                throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
                }
                
                $data->show_page_heading = $this->_show_page_heading;
                $data->limited_options = $this->frontend ? $data->limited_article_options_fe : $data->limited_article_options;
                $data->form_id = $this->_id;
                $data->record_id = $this->_record_id;
                if($data->type && $data->reference_id){
                    
                    // article options
                    $this->_db->setQuery("Select content.id, content.modified_by, content.version, content.hits, content.catid From #__contentbuilder_articles As articles, #__content As content Where (content.state = 1 Or content.state = 0) And content.id = articles.article_id And articles.form_id = " . $this->_id . " And articles.record_id = " . $this->_db->Quote($this->_record_id));
                    $article = $this->_db->loadAssoc();

                    if($data->create_articles){

	                    jimport('joomla.form.form');
	                    JForm::addFormPath(JPATH_SITE . '/administrator/components/com_contentbuilder/models/forms');
	                    JForm::addFieldPath(JPATH_SITE . '/administrator/components/com_content/models/fields');
	                    $form = JForm::getInstance('com_content.article', 'article', array('control' => 'jform', 'load_data' => true));

	                    if(is_array($article)){

		                    $table = JTable::getInstance('content');
		                    $loaded = $table->load($article['id']);
		                    if($loaded){
			                    // Convert to the JObject before adding other data.
			                    $properties = $table->getProperties(1);
			                    $item = ArrayHelper::toObject($properties, 'JObject');

			                    if (property_exists($item, 'params')) {
				                    $registry = new Registry;
				                    $registry->loadString($item->params);
				                    $item->params = $registry->toArray();
			                    }

			                    // Convert the params field to an array.
			                    $registry = new Registry;
			                    $registry->loadString($item->attribs);
			                    $item->attribs = $registry->toArray();

			                    // Convert the params field to an array.
			                    $registry = new Registry;
			                    $registry->loadString($item->metadata);
			                    $item->metadata = $registry->toArray();
			                    $item->articletext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;

			                    // Import the approriate plugin group.
			                    JPluginHelper::importPlugin('content');

			                    // Trigger the form preparation event.
			                    $results = Factory::getApplication()->triggerEvent('onContentPrepareForm', array($form, $item));

			                    // Check for errors encountered while preparing the form.
			                    /*
			                    if (count($results) && in_array(false, $results, true)) {
				                    // Get the last error.
				                    $error = $dispatcher->getError();

				                    // Convert to a JException if necessary.
				                    if (!JError::isError($error)) {
					                    throw new Exception($error);
				                    }
			                    }*/

			                    $form->bind($item);

			                    $data->sectioncategories = array();
			                    $data->row = $item;
			                    $data->lists = array();
		                    } else {
			                    $data->sectioncategories = array();
			                    $data->row = new stdClass(); $data->row->title = ''; $data->row->alias = ''; // special for 1.5
			                    $data->lists = array('state' => '', 'frontpage' => '', 'sectionid' => '', 'catid' => ''); // special for 1.5
		                    }

		                    $data->article_settings = new stdClass();
		                    $data->article_settings->modified_by = $article['modified_by'];
		                    $data->article_settings->version = $article['version'];
		                    $data->article_settings->hits = $article['hits'];
		                    $data->article_settings->catid = $article['catid'];

	                    }else{
		                    $data->article_settings = new stdClass();
		                    $data->article_settings->modified_by = 0;
		                    $data->article_settings->version = 0;
		                    $data->article_settings->hits = 0;
		                    $data->article_settings->catid = 0;
	                    }

	                    $data->article_options = $form;
                    }
                    
                    $data->back_button = CBRequest::getBool('latest', 0) && !CBRequest::getCmd('record_id',0) ? false : $this->_show_back_button;
                    $data->latest = $this->_latest;
                    $data->is15 = $this->is15;
                    $data->frontend = $this->frontend;
                    $data->form = contentbuilder::getForm($data->type, $data->reference_id);
                    if(!$data->form->exists){
                        throw new Exception(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 404);
                    }
                    $data->page_title = '';
                    if(CBRequest::getInt('cb_prefix_in_title', 1)){
                        if(!$this->_menu_item){
                            $data->page_title = $data->use_view_name_as_title ? $data->name : $data->form->getPageTitle();
                        }else{
                            $data->page_title = $data->use_view_name_as_title ? $data->name : Factory::getDocument()->getTitle();
                        }
                    }
                    
                    $data->labels = $data->form->getElementLabels();
                    $ids = array();
                    foreach($data->labels As $reference_id => $label){
                        $ids[] = $this->_db->Quote($reference_id);
                    }
                    
                    if(count($ids)){
                        $this->_db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval($this->_id) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                        $rows = $this->_db->loadAssocList();
                        $ids = array();
                        foreach($rows As $row){
                           $ids[] = $row['reference_id'];
                        }
                    }
                    
                    $data->items = $data->form->getRecord($this->_record_id, $data->published_only, $this->frontend ? ( $data->own_only_fe ? Factory::getApplication()->getIdentity()->get('id', 0) : -1 ) : ( $data->own_only ? Factory::getApplication()->getIdentity()->get('id', 0) : -1 ), $this->frontend ? $data->show_all_languages_fe : true );
                    
                    if(count($data->items)){
                        
                        $user = null;
                        
                        if($data->act_as_registration){
                            $meta = $data->form->getRecordMetadata($this->_record_id);
                            $this->_db->setQuery("Select * From #__users Where id = " . $meta->created_id);
                            $user = $this->_db->loadObject();
                        }
                        
                        $label = '';
                        foreach($data->items As $rec){
                            
                            if($rec->recElementId == $data->title_field){
                                
                                if($data->act_as_registration && $user !== null){
                                    
                                    if($data->registration_name_field == $rec->recElementId){
                                         $rec->recValue = $user->name;
                                    }
                                    else 
                                    if($data->registration_username_field == $rec->recElementId){
                                        $item->recValue = $user->username;
                                    }
                                    else 
                                    if($data->registration_email_field == $item->recElementId){
                                        $rec->recValue = $user->email;
                                    }
                                    else 
                                    if($data->registration_email_repeat_field == $rec->recElementId){
                                        $rec->recValue = $user->email;
                                    }
                                }
                                $label = cbinternal($rec->recValue);
                                break;
                            }
                        }
                        
                        // trying first element if no title field given
                        if(!$label){
                            $label = cbinternal($data->items[0]->recValue);
                        }
                        
                        // "buddy quaid hack", should be an option in future versions

	                    if($this->_show_page_heading && $this->_page_title != '' && $this->_page_heading != '' && $this->_page_title == $this->_page_heading){
		                    $data->page_title = $this->_page_title;
	                    }
	                    else{
		                    $data->page_title .= $label ? (!$data->page_title ? '' : ': ') . $label : '';
	                    }
                        
                        if($this->frontend){
                            $document = Factory::getDocument();
                            $document->setTitle(html_entity_decode ($data->page_title, ENT_QUOTES, 'UTF-8'));
                        }

                    }
                    
                    //if(!$data->edit_by_type){
            
                        $i = 0;
                        $api_items = '';
                        $api_names = $data->form->getElementNames();
                        $cntItems = count($api_names);
                        foreach($api_names As $reference_id => $api_name){
                            $api_items .= '"'.addslashes($api_name).'": "'.addslashes($reference_id).'"'.($i + 1 < $cntItems ? ',' : '');
                            $i++;
                        }
                        $items = $api_items;

                        Factory::getDocument()->addScriptDeclaration(
'
<!--
var contentbuilder = new function(){

   this.items = {'.$items.'};
   var items = this.items;

   this._ = function(name){
     var els = document.getElementsByName("cb_"+items[name]);
     if(els.length == 0){
        els = document.getElementsByName("cb_"+items[name]+"[]");
     }
     return els.length == 1 ? els[0] : els;
   };
   
   var _ = this._;

   this.urldecode = function (str) {
       return decodeURIComponent((str+\'\').replace(/\+/g, \'%20\'));
   };

   this.getQuery = function ( name ){
       name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");  
       var regexS = "[\\?&]"+name+"=([^&#]*)";  
       var regex = new RegExp( regexS );
       var results = regex.exec( window.location.href ); 
       if( results == null ){
           return null;
       } else {
           return this.urldecode(results[1]);
       }
   };

   this.onClick = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onclick = func;
        }
   };
   this.onFocus = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onfocus = func;
        }
   };
   this.onBlur = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onblur = func;
        }
   };
   this.onChange = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onchange = func;
        }
   };
   this.onSelect = function(name, func){
        if(typeof func != "function") return;
        var els = document.getElementsByName("cb_"+items[name]);
        if(els.length == 0){
            els = document.getElementsByName("cb_"+items[name]+"[]");
        }
        for(var i = 0; i < els.length; i++){
            els[i].onselect = func;
        }
   };
   
   this.submitReady = function(){ return true; };
   var _submitReady = this.submitReady;
   this.onSubmit = function(){ if(arguments.length > 0 && typeof arguments[0] == "function") { _submitReady = arguments[0]; return; } if(typeof _submitReady == "function" && _submitReady()) { document.forms.adminForm.submit(); } };
}
//-->
'        
                        );
                    //}
                    
                    $data->template = contentbuilder::getEditableTemplate($this->_id, $this->_record_id, $data->items, $ids, !$data->edit_by_type);

                    if(Factory::getApplication()->isClient('administrator')
                        && strpos($data->template, '[[hide-admin-title]]') !== false){

                        $data->page_title = '';
                    }

                    $metadata = $data->form->getRecordMetadata($this->_record_id);
                    
                    if($metadata instanceof stdClass && $data->metadata){
                        $data->created = $metadata->created ? $metadata->created : '';
                        $data->created_by = $metadata->created_by ? $metadata->created_by : '';
                        $data->modified = $metadata->modified ? $metadata->modified : '';
                        $data->modified_by = $metadata->modified_by ? $metadata->modified_by : '';
                    }else{
                        $data->created = '';
                        $data->created_by = '';
                        $data->modified = '';
                        $data->modified_by = '';
                    }
                }
                return $data;
            }
        }
        return null;
    }
    
    public static function customValidate($code, $field, $fields, $record_id, $form, $value){
        $msg = '';
        eval($code);
        return $msg;
    }
    
    public static function customAction($code, $record_id, $article_id, $form, $field, $fields, array $values){
        $msg = '';
        eval($code);
        return $msg;
    }
    
    function store(){

        CBRequest::checkToken('default') or jexit(Text::_('JInvalid_Token'));
        
        JPluginHelper::importPlugin('contentbuilder_submit');
        Factory::getSession()->clear('cb_failed_values', 'com_contentbuilder.'.$this->_id);
        CBRequest::setVar('cb_submission_failed', 0);
        
        $query = $this->_buildQuery();
        $this->_data = $this->_getList($query, 0, 1);

        if (!count($this->_data)) {
			throw new Exception(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 404);
        }

        foreach ($this->_data As $data) {

            if (!$this->frontend && $data->display_in == 0) {
	            throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
            } else if ($this->frontend && $data->display_in == 1) {
	            throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
            }
            
            $data->form_id = $this->_id;

            if ($data->type && $data->reference_id) {

                $values = array();
                $data->form = contentbuilder::getForm($data->type, $data->reference_id);
                $meta = $data->form->getRecordMetadata($this->_record_id);
                if(!$data->edit_by_type){

                    $noneditable_fields = contentbuilder::getListNonEditableElements($this->_id);
                    $names = $data->form->getElementNames();

                    $this->_db->setQuery("Select * From #__contentbuilder_elements Where form_id = " . $this->_id . " And published = 1 And editable = 1");
                    $fields = $this->_db->loadAssocList();

                    $the_fields        = array();
                    $the_name_field = null;
                    $the_username_field = null;
                    $the_password_field = null;
                    $the_password_repeat_field = null;
                    $the_email_field = null;
                    $the_email_repeat_field = null;
                    $the_html_fields   = array();
                    $the_upload_fields = array();
                    $the_captcha_field = null;
                    $the_failed_registration_fields = array();
                    
                    jimport('joomla.filesystem.file');
                    jimport('joomla.filesystem.folder');

                    foreach($fields As $special_field){
                        switch($special_field['type']){
                            case 'text':
                            case 'upload':
                            case 'captcha':
                            case 'textarea': 
                                if( $special_field['type'] == 'upload' ){
                                    $options = unserialize(cb_b64dec($special_field['options']));
                                    $special_field['options'] = $options;
                                    $the_upload_fields[$special_field['reference_id']] = $special_field;
                                }
                                else if( $special_field['type'] == 'captcha' ){
                                    $options = unserialize(cb_b64dec($special_field['options']));
                                    $special_field['options'] = $options;
                                    $the_captcha_field = $special_field;
                                }
                                else if( $special_field['type'] == 'textarea' ){
                                    $options = unserialize(cb_b64dec($special_field['options']));
                                    $special_field['options'] = $options;
                                    if(isset($special_field['options']->allow_html) && $special_field['options']->allow_html){
                                        $the_html_fields[$special_field['reference_id']] = $special_field;
                                    }else{
                                        $the_fields[$special_field['reference_id']] = $special_field;
                                    }
                                }
                                else if( $special_field['type'] == 'text' ){
                                    $options = unserialize(cb_b64dec($special_field['options']));
                                    $special_field['options'] = $options;
                                    if($data->act_as_registration && $data->registration_username_field == $special_field['reference_id'] ){
                                        $the_username_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_name_field == $special_field['reference_id'] ){
                                        $the_name_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_password_field == $special_field['reference_id'] ){
                                        $the_password_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_password_repeat_field == $special_field['reference_id'] ){
                                        $the_password_repeat_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_email_field == $special_field['reference_id'] ){
                                        $the_email_field = $special_field;
                                    } else if($data->act_as_registration && $data->registration_email_repeat_field == $special_field['reference_id'] ){
                                        $the_email_repeat_field = $special_field;
                                    } else {
                                        $the_fields[$special_field['reference_id']] = $special_field;
                                    }
                                }
                           break;
                           default:
                                $options = unserialize(cb_b64dec($special_field['options']));
                                $special_field['options'] = $options;
                                $the_fields[$special_field['reference_id']] = $special_field;
                        }
                    }

                    // we have defined a captcha, so let's test it
                    if($the_captcha_field !== null && !in_array($the_captcha_field['reference_id'], $noneditable_fields)){

                        if(!class_exists('Securimage')){
                           require_once(JPATH_SITE . DS . 'components' . DS . 'com_contentbuilder' . DS . 'images' . DS . 'securimage' . DS . 'securimage.php');
                        }

                        $securimage = new Securimage();
                        $cap_value = CBRequest::getVar( 'cb_' . $the_captcha_field['reference_id'], null, 'POST' );
                        if ($securimage->check($cap_value) == false) {
                            CBRequest::setVar('cb_submission_failed', 1);
                            Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_CAPTCHA_FAILED'), 'error');
                        }
                        $values[$the_captcha_field['reference_id']] = $cap_value;
                        $noneditable_fields[] = $the_captcha_field['reference_id'];
                    }

                    // now let us see if we have a registration
                    // make sure to wait for previous errors
                    if( $data->act_as_registration && $the_name_field !== null && $the_email_field !== null && $the_email_repeat_field !== null && $the_password_field !== null && $the_password_repeat_field !== null && $the_username_field !== null ){

                        $pw1 = CBRequest::getVar( 'cb_' . $the_password_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW );
                        $pw2 = CBRequest::getVar( 'cb_' . $the_password_repeat_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW );
                        $email = CBRequest::getVar( 'cb_' . $the_email_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW );
                        $email2 = CBRequest::getVar( 'cb_' . $the_email_repeat_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW );
                        $name = CBRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW );
                        $username = CBRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW );

                        if( !CBRequest::getVar('cb_submission_failed', 0) ){

                            if( !trim($name) )
                            {
                                CBRequest::setVar('cb_submission_failed', 1);
                                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_NAME_EMPTY'), 'error');
                            }
                            
                            if( !trim($username) )
                            {
                                CBRequest::setVar('cb_submission_failed', 1);
                                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_USERNAME_EMPTY'), 'error');
                            }
                            else if( preg_match( "#[<>\"'%;()&]#i", $username) || strlen(utf8_decode($username )) < 2 )
                            {
                                CBRequest::setVar('cb_submission_failed', 1);
                                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_USERNAME_INVALID'), 'error');
                            }
                            
                            if( !trim($email) )
                            {
                                CBRequest::setVar('cb_submission_failed', 1);
                                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_EMAIL_EMPTY'), 'error');
                            }
                            else if( !contentbuilder_is_email($email) )
                            {
                                CBRequest::setVar('cb_submission_failed', 1);
                                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_EMAIL_INVALID'), 'error');
                            } 
                            else if( $email != $email2 )
                            {
                                CBRequest::setVar('cb_submission_failed', 1);
                                Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_EMAIL_MISMATCH'), 'error');
                            }
                            
                            if( !$meta->created_id && !Factory::getApplication()->getIdentity()->get('id', 0) ){

                                $this->_db->setQuery("Select count(id) From #__users Where `username` = " . $this->_db->Quote($username));
                                if($this->_db->loadResult()){
                                    CBRequest::setVar('cb_submission_failed', 1);
                                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_USERNAME_NOT_AVAILABLE'), 'error');
                                }

                                $this->_db->setQuery("Select count(id) From #__users Where `email` = " . $this->_db->Quote($email));
                                if($this->_db->loadResult()){
                                    CBRequest::setVar('cb_submission_failed', 1);
                                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_EMAIL_NOT_AVAILABLE'), 'error');
                                }
                                
                                if( $pw1 != $pw2 ){
                                    CBRequest::setVar('cb_submission_failed', 1);
                                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_PASSWORD_MISMATCH'), 'error');
                                
                                    CBRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                    CBRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                        
                                } 
                                else if( !trim($pw1) )
                                {
                                    CBRequest::setVar('cb_submission_failed', 1);
                                    Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_PASSWORD_EMPTY'), 'error');
                                
                                    CBRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                    CBRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                                }
                            }
                            else
                            {
                                if($meta->created_id && $meta->created_id != Factory::getApplication()->getIdentity()->get('id', 0)){
                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote($meta->created_id)." And `username` = " . $this->_db->Quote($username));
                                    if($this->_db->loadResult()){
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_USERNAME_NOT_AVAILABLE'), 'error');
                                    }

                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote($meta->created_id)." And `email` = " . $this->_db->Quote($email));
                                    if($this->_db->loadResult()){
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_EMAIL_NOT_AVAILABLE'), 'error');
                                    }
                                }
                                else
                                {
                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote(Factory::getApplication()->getIdentity()->get('id', 0))." And `username` = " . $this->_db->Quote($username));
                                    if($this->_db->loadResult()){
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_USERNAME_NOT_AVAILABLE'), 'error');
                                    }

                                    $this->_db->setQuery("Select count(id) From #__users Where id <> ".$this->_db->Quote(Factory::getApplication()->getIdentity()->get('id', 0))." And `email` = " . $this->_db->Quote($email));
                                    if($this->_db->loadResult()){
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_EMAIL_NOT_AVAILABLE'), 'error');
                                    }
                                }
                                
                                if(trim($pw1) != '' || trim($pw2) != ''){
                                    
                                    if( $pw1 != $pw2 ){
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_PASSWORD_MISMATCH'), 'error');
                                    
                                        CBRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                        CBRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                                    } 
                                    else if( !trim($pw1) )
                                    {
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_PASSWORD_EMPTY'), 'error');
                                    
                                        CBRequest::setVar( 'cb_' . $the_password_field['reference_id'], '' );
                                        CBRequest::setVar( 'cb_' . $the_password_repeat_field['reference_id'], '' );
                                    }
                                }
                            }
                            
                            if(!CBRequest::getVar('cb_submission_failed', 0)){
                                
                                //$noneditable_fields[] = $the_name_field['reference_id'];
                                $noneditable_fields[] = $the_password_field['reference_id'];
                                $noneditable_fields[] = $the_password_repeat_field['reference_id'];
                                //$noneditable_fields[] = $the_email_field['reference_id'];
                                $noneditable_fields[] = $the_email_repeat_field['reference_id'];
                                //$noneditable_fields[] = $the_username_field['reference_id'];

                            }else{
                                
                                $the_failed_registration_fields[$the_name_field['reference_id']] = $the_name_field;
                                //$the_failed_registration_fields[$the_password_field['reference_id']] = $the_password_field;
                                //$the_failed_registration_fields[$the_password_repeat_field['reference_id']] = $the_password_repeat_field;
                                $the_failed_registration_fields[$the_email_field['reference_id']] = $the_email_field;
                                $the_failed_registration_fields[$the_email_repeat_field['reference_id']] = $the_email_repeat_field;
                                $the_failed_registration_fields[$the_username_field['reference_id']] = $the_username_field;
                            }
                        }
                        else
                        {
                            $the_failed_registration_fields[$the_name_field['reference_id']] = $the_name_field;
                            //$the_failed_registration_fields[$the_password_field['reference_id']] = $the_password_field;
                            //$the_failed_registration_fields[$the_password_repeat_field['reference_id']] = $the_password_repeat_field;
                            $the_failed_registration_fields[$the_email_field['reference_id']] = $the_email_field;
                            $the_failed_registration_fields[$the_email_repeat_field['reference_id']] = $the_email_repeat_field;
                            $the_failed_registration_fields[$the_username_field['reference_id']] = $the_username_field;
                        }
                    }

                    $form_elements_objects = array();
                    
                    $_items = $data->form->getRecord($this->_record_id, $data->published_only, $this->frontend ? ( $data->own_only_fe ? Factory::getApplication()->getIdentity()->get('id', 0) : -1 ) : ( $data->own_only ? Factory::getApplication()->getIdentity()->get('id', 0) : -1 ), $this->frontend ? $data->show_all_languages_fe : true );
                    
                    // asigning the proper names first
                    foreach($names As $id => $name){

                        if($noneditable_fields == null || !in_array($id, $noneditable_fields)){
                            $value = '';
                            $is_array = 'STRING';
                            if(is_array(CBRequest::getVar( 'cb_' . $id, ''))){
                                $is_array = 'ARRAY';
                            }
                            if( isset($the_fields[$id]['options']->allow_raw) && $the_fields[$id]['options']->allow_raw ){
                                $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, CBREQUEST_ALLOWRAW );
                            }
                            else if( isset($the_fields[$id]['options']->allow_html) && $the_fields[$id]['options']->allow_html ){
                                $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, CBREQUEST_ALLOWHTML );
                            }
                            else{
                                $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', $is_array );
                            }
                            if(isset($the_fields[$id]['options']->transfer_format)){
                                $value = contentbuilder_convert_date($value, $the_fields[$id]['options']->format, $the_fields[$id]['options']->transfer_format);
                            }

                            if(isset($the_html_fields[$id]))
                            {
                                $the_html_fields[$id]['name'] = $name;
                                $the_html_fields[$id]['value'] = $value;
                            } 
                            else if(isset($the_failed_registration_fields[$id]))
                            {
                                $the_failed_registration_fields[$id]['name'] = $name;
                                $the_failed_registration_fields[$id]['value'] = $value;
                            }
                            else if(isset($the_upload_fields[$id]))
                            {
                                $the_upload_fields[$id]['name'] = $name;
                                $the_upload_fields[$id]['value'] = '';
                                $the_upload_fields[$id]['orig_value'] = '';
                                
                                if($id == $the_upload_fields[$id]['reference_id']){

                                    // delete if triggered
                                    if( CBRequest::getInt('cb_delete_'.$id, 0) == 1 && isset($the_upload_fields[$id]['validations']) && $the_upload_fields[$id]['validations'] == '' ){
                                        if(count($_items)){
                                            foreach($_items As $_item){
                                                if($_item->recElementId == $the_upload_fields[$id]['reference_id']){
                                                    $_value = $_item->recValue;
                                                    $_files = explode("\n", str_replace("\r",'',$_value));
                                                    foreach($_files As $_file){
                                                        if(strpos(strtolower($_file), '{cbsite}') === 0){
                                                            $_file = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_file);
                                                        }
                                                        if(JFile::exists($_file)){
                                                            JFile::delete($_file);
                                                        }
                                                        $values[$id] = '';
                                                    }
                                                }
                                            }
                                        }
                                    }

                                    $file = CBRequest::getVar('cb_' . $id, null, 'files', 'array');

                                    if( trim(JFile::makeSafe($file['name'])) != '' && $file['size'] > 0){

                                        $filename = trim( JFile::makeSafe($file['name']) );
                                        $infile = $filename;

                                        $src = $file['tmp_name'];
                                        $dest = '';
                                        $tmp_dest = '';
                                        $tmp_upload_field_dir = '';
                                        $tmp_upload_dir = '';

                                        if(isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->upload_directory) && $the_upload_fields[$id]['options']->upload_directory != ''){
                                            $tmp_upload_field_dir = $the_upload_fields[$id]['options']->upload_directory;
                                            $tmp_dest = $tmp_upload_field_dir;
                                        } else if($data->upload_directory != ''){
                                            $tmp_upload_dir = $data->upload_directory;
                                            $tmp_dest = $tmp_upload_dir;
                                        }

                                        if(isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->upload_directory) && $the_upload_fields[$id]['options']->upload_directory != ''){

                                            $dest = str_replace(array('{CBSite}','{cbsite}'), JPATH_SITE, $the_upload_fields[$id]['options']->upload_directory);

                                        } else if($data->upload_directory != ''){

                                            $dest = str_replace(array('{CBSite}','{cbsite}'), JPATH_SITE, $data->upload_directory);
                                        }

                                        // create dest path by tokens
                                        $dest = $this->createPathByTokens($dest, $names);
                                        
                                        $msg = '';
                                        $uploaded = false;

                                        // FILE SIZE TEST

                                        if($dest != '' && isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->max_filesize) && $the_upload_fields[$id]['options']->max_filesize > 0){

                                            $val = $the_upload_fields[$id]['options']->max_filesize;
                                            $val = trim($val);
                                            $last = strtolower($val[strlen($val)-1]);
                                            switch($last) {
                                                case 'g':
                                                    $val *= 1024;
                                                case 'm':
                                                    $val *= 1024;
                                                case 'k':
                                                    $val *= 1024;
                                            }

                                            if($file['size'] > $val){
                                                $msg = Text::_('COM_CONTENTBUILDER_FILESIZE_EXCEEDED') . ' ' . $the_upload_fields[$id]['options']->max_filesize . 'b';
                                            }
                                        }

                                        // FILE EXT TEST

                                        if($dest != '' && isset($the_upload_fields[$id]['options']) && isset($the_upload_fields[$id]['options']->allowed_file_extensions) && $the_upload_fields[$id]['options']->allowed_file_extensions != ''){

                                            $allowed = explode(',',str_replace(' ','',strtolower($the_upload_fields[$id]['options']->allowed_file_extensions)));
                                            $ext = strtolower(JFile::getExt($filename));

                                            if(!in_array($ext, $allowed)){
                                                $msg = Text::_('COM_CONTENTBUILDER_FILE_EXTENSION_NOT_ALLOWED');
                                            }
                                        }

                                        // UPLOAD

                                        if($dest != '' && $msg == ''){

                                            // limit file's name size
                                            $ext = strtolower(JFile::getExt($filename));
                                            $stripped = JFile::stripExt($filename);
                                            // in some apache configurations unknown file extensions could lead to security risks
                                            // because it will try to find an executable extensions within the chain of dots. So we simply remove them.
                                            $filename = str_replace(array(' ','.'),'_',$stripped).'.'.$ext;

                                            $maxnamesize = 100;
                                            if(function_exists('mb_strlen')){
                                                if(mb_strlen($filename) > $maxnamesize){
                                                    $filename = mb_substr($filename, mb_strlen($filename)-$maxnamesize);
                                                }
                                            }else{
                                                if(strlen($filename) > $maxnamesize){
                                                    $filename = substr($filename, strlen($filename)-$maxnamesize);
                                                }
                                            }

                                            // take care of existing filenames
                                            if(JFile::exists($dest . DS . $filename)){
                                                $filename = md5(mt_rand(0, mt_getrandmax()) . time()) . '_' . $filename;
                                            }

                                            // create pseudo security index.html
                                            if(!JFile::exists($dest.DS.'index.html')){
                                                JFile::write($dest.DS.'index.html', $buffer = '');
                                            }

                                            if(count($_items)){
                                                $files_to_delete = array();

                                                foreach($_items As $_item){
                                                    if($_item->recElementId == $the_upload_fields[$id]['reference_id']){
                                                        $_value = $_item->recValue;
                                                        $_files = explode("\n", str_replace("\r",'',$_value));
                                                        foreach($_files As $_file){
                                                            if(strpos(strtolower($_file), '{cbsite}') === 0){
                                                                $_file = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $_file);
                                                            }
                                                            $files_to_delete[] = $_file;
                                                        }
                                                        break;
                                                    }
                                                }
                                                foreach( $files_to_delete As $file_to_delete ){
                                                    if(JFile::exists($file_to_delete)){
                                                        JFile::delete($file_to_delete);
                                                    }
                                                }
                                            }

                                            // final upload file moving
                                            $uploaded = JFile::upload($src, $dest . DS . $filename, false, true);

                                            if(!$uploaded){
                                                $msg = Text::_('COM_CONTENTBUILDER_UPLOAD_FAILED');
                                            }
                                        }

                                        if($dest == '' || $uploaded !== true){
                                            CBRequest::setVar('cb_submission_failed', 1);
                                            Factory::getApplication()->enqueueMessage($msg . ' ('.$infile.')', 'error');
                                            $the_upload_fields[$id]['value'] = '';
                                        }
                                        else
                                        {
                                            if(strpos(strtolower($tmp_dest), '{cbsite}') === 0){
                                                $dest = str_replace(array(JPATH_SITE, JPATH_SITE), array('{cbsite}','{CBSite}'), $dest);
                                            }
                                            $values[$id] = $dest . DS . $filename;
                                            $the_upload_fields[$id]['value'] = $values[$id];
                                        }

                                        $the_upload_fields[$id]['orig_value'] = JFile::makeSafe($file['name']);
                                    }

                                    if(trim($the_upload_fields[$id]['custom_validation_script'])){
                                        $msg = self::customValidate(trim($the_upload_fields[$id]['custom_validation_script']), $the_upload_fields[$id], array_merge($the_upload_fields, $the_fields, $the_html_fields), CBRequest::getCmd('record_id',0), $data->form, isset($values[$id]) ? $values[$id] : '');
                                        $msg = trim($msg);
                                        if(!empty($msg)){
                                            CBRequest::setVar('cb_submission_failed', 1);
                                            Factory::getApplication()->enqueueMessage(trim($msg), 'error');
                                        }
                                    }

                                    $validations = explode(',', $the_upload_fields[$id]['validations']);

                                    foreach($validations As $validation){
	                                    \Joomla\CMS\Plugin\PluginHelper4::importPlugin('contentbuilder_validation', $validation);
                                    }

                                    $results = Factory::getApplication()->triggerEvent('onValidate', array($the_upload_fields[$id], array_merge($the_upload_fields, $the_fields, $the_html_fields), CBRequest::getCmd('record_id',0), $data->form, isset($values[$id]) ? $values[$id] : ''));
	                                Factory::getApplication()->getDispatcher()->clearListeners('onValidate');

                                    $all_errors = implode('',$results);
                                    if(!empty($all_errors)){
                                        if(isset($values[$id]) && JFile::exists($values[$id])){
                                            JFile::delete($values[$id]);
                                        }
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        foreach($results As $result){
                                            $result = trim($result);
                                            if(!empty($result)){
                                                Factory::getApplication()->enqueueMessage(trim($result), 'error');
                                            }
                                        }
                                    }
                                }
                                
                            }
                            else if(isset($the_fields[$id]))
                            {
                                $the_fields[$id]['name'] = $name;
                                $the_fields[$id]['value'] = $value;
                            }
                        }
                    }

                    foreach($names As $id => $name){

                        if($noneditable_fields == null || !in_array($id, $noneditable_fields)){

                            if(isset($the_upload_fields[$id]) && $id == $the_upload_fields[$id]['reference_id']){
                                // nothing, done above already
                            } 
                            else
                            {
                                $f = null;
                                
                                if(isset($the_html_fields[$id]))
                                {
                                    $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', 'STRING', CBREQUEST_ALLOWHTML );
                                    $f = $the_html_fields[$id];
                                    $the_html_fields[$id]['value'] = $value;
                                } 
                                
                                if(isset($the_failed_registration_fields[$id]))
                                {
                                    $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', 'STRING', CBREQUEST_ALLOWRAW );
                                    $f = $the_failed_registration_fields[$id];
                                    $the_failed_registration_fields[$id]['value'] = $value;
                                }
                                
                                if(isset($the_fields[$id])) 
                                {
                                    $is_array = 'STRING';
                                    if(is_array(CBRequest::getVar( 'cb_' . $id, ''))){
                                        $is_array = 'ARRAY';
                                    }
                                    if( isset($the_fields[$id]['options']->allow_raw) && $the_fields[$id]['options']->allow_raw ){
                                        $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, CBREQUEST_ALLOWRAW );
                                    }
                                    else if( isset($the_fields[$id]['options']->allow_html) && $the_fields[$id]['options']->allow_html ){
                                        $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', $is_array, CBREQUEST_ALLOWHTML );
                                    }
                                    else{
                                        $value = CBRequest::getVar( 'cb_' . $id, '', 'POST', $is_array );
                                    }
                                    if(isset($the_fields[$id]['options']->transfer_format)){
                                        $value = contentbuilder_convert_date($value, $the_fields[$id]['options']->format, $the_fields[$id]['options']->transfer_format);
                                    }
                                    $f = $the_fields[$id];
                                    $the_fields[$id]['value'] = $value;
                                }

                                if($f !== null){
                                    
                                    if(trim($f['custom_validation_script'])){
                                        $msg = self::customValidate(trim($f['custom_validation_script']), $f, array_merge($the_upload_fields, $the_fields, $the_html_fields), CBRequest::getCmd('record_id',0), $data->form, $value);
                                        $msg = trim($msg);
                                        if(!empty($msg)){
                                            CBRequest::setVar('cb_submission_failed', 1);
                                            Factory::getApplication()->enqueueMessage(trim($msg), 'error');
                                        }
                                    }

                                    $validations = explode(',', $f['validations']);

                                    foreach($validations As $validation){
	                                    \Joomla\CMS\Plugin\PluginHelper4::importPlugin('contentbuilder_validation', $validation);
                                    }

                                    $results = Factory::getApplication()->triggerEvent('onValidate', array($f, array_merge($the_upload_fields, $the_fields, $the_html_fields), CBRequest::getCmd('record_id',0), $data->form, $value));
	                                Factory::getApplication()->getDispatcher()->clearListeners('onValidate');

                                    $all_errors = implode('',$results);
                                    $values[$id] = $value;
                                    if(!empty($all_errors)){
                                        CBRequest::setVar('cb_submission_failed', 1);
                                        foreach($results As $result){
                                            $result = trim($result);
                                            if(!empty($result)){
                                                Factory::getApplication()->enqueueMessage(trim($result), 'error');
                                            }
                                        }
                                    }
                                    else
                                    {

	                                    \Joomla\CMS\Plugin\PluginHelper4::importPlugin('contentbuilder_form_elements', $f['type']);

                                        $plugin_validations = Factory::getApplication()->triggerEvent('onAfterValidationSuccess', array($f, $m = array_merge($the_upload_fields, $the_fields, $the_html_fields), CBRequest::getCmd('record_id',0), $data->form, $value));
	                                    Factory::getApplication()->getDispatcher()->clearListeners('onAfterValidationSuccess');

                                        if(count($plugin_validations)){
                                            $form_elements_objects[] = $plugin_validations[0];
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    $submit_before_result = Factory::getApplication()->triggerEvent('onBeforeSubmit', array(CBRequest::getCmd('record_id',0), $data->form, $values));
                            
                    if(CBRequest::getVar('cb_submission_failed',0)){
                        Factory::getSession()->set('cb_failed_values', $values, 'com_contentbuilder.'.$this->_id);
                        return CBRequest::getCmd('record_id',0);
                    }

                    $record_return = $data->form->saveRecord(CBRequest::getCmd('record_id',0), $values);
                    
                    foreach($form_elements_objects As $form_elements_object){
                        if($form_elements_object instanceof CBFormElementAfterValidation){
                            $form_elements_object->onSaveRecord($record_return);
                        }
                    }

	                if($data->act_as_registration && $record_return){
                        
                        $meta = $data->form->getRecordMetadata($record_return);


                        if(!$data->registration_bypass_plugin || $meta->created_id){
                            
                            $user_id = $this->register(
                                '',
                                '',
                                '',
                                $meta->created_id,
                                CBRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ), 
                                CBRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ),
                                CBRequest::getVar( 'cb_' . $the_email_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ), 
                                CBRequest::getVar( 'cb_' . $the_password_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW )
                            );
                            
                            if( intval($user_id) > 0 ){
                            
                                Factory::getSession()->set('cb_last_record_user_id', $user_id, 'com_contentbuilder');

                                $data->form->saveRecordUserData( 
                                        $record_return,
                                        $user_id,
                                        CBRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ), 
                                        CBRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW )
                                );
                            } else {

	                            // rollback upon registration problems
	                            $data->form->clearDirtyRecordUserData($record_return);

                                throw new Exception('Failed attempt to register user');
                            }

                        } else {

                            if( !$meta->created_id ) {
                            
                                $bypass = new stdClass();
                                $verification_name = str_replace(array(';','___','|'), '-', trim($data->registration_bypass_verification_name) ? trim($data->registration_bypass_verification_name) : $data->title);
                                $verify_view = trim($data->registration_bypass_verify_view) ? trim($data->registration_bypass_verify_view) : $data->id;
                                $bypass->text = $orig_text = '{CBVerify plugin: '.$data->registration_bypass_plugin.'; verification-name: '.$verification_name.'; verify-view: '.$verify_view.'; '.str_replace(array("\r", "\n"),'',$data->registration_bypass_plugin_params).'}';
                                $params = new stdClass();

                                JPluginHelper::importPlugin('content', 'contentbuilder_verify');
                                $bypass_result = Factory::getApplication()->triggerEvent('onPrepareContent', array(&$bypass, &$params));

                                $verification_id = '';

                                if($bypass->text != $orig_text){
                                    $verification_id = md5(uniqid(null,true) . mt_rand(0, mt_getrandmax()) . Factory::getApplication()->getIdentity()->get('id',0));
                                }

                                $user_id = $this->register(
                                    $data->registration_bypass_plugin,
                                    $verification_name,
                                    $verification_id,
                                    $meta->created_id,
                                    CBRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ), 
                                    CBRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ),
                                    CBRequest::getVar( 'cb_' . $the_email_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ), 
                                    CBRequest::getVar( 'cb_' . $the_password_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW )
                                );

                                if( intval($user_id) > 0 ){

                                    Factory::getSession()->set('cb_last_record_user_id', $user_id, 'com_contentbuilder');

                                    $data->form->saveRecordUserData( 
                                            $record_return,
                                            $user_id,
                                            CBRequest::getVar( 'cb_' . $the_name_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW ), 
                                            CBRequest::getVar( 'cb_' . $the_username_field['reference_id'], '', 'POST', 'STRING', CBREQUEST_ALLOWRAW )
                                    );
                                } else {

                                	// rollback upon registration problems
	                                $data->form->clearDirtyRecordUserData($record_return);

                                    throw new Exception('Failed attempt to register user');
                                }

                                if($bypass->text != $orig_text && intval($user_id) > 0){

                                    $_now = Factory::getDate();

                                    $setup = Factory::getSession()->get($data->registration_bypass_plugin.$verification_name, '', 'com_contentbuilder.verify.'.$data->registration_bypass_plugin.$verification_name);
                                    Factory::getSession()->clear($data->registration_bypass_plugin.$verification_name, 'com_contentbuilder.verify.'.$data->registration_bypass_plugin.$verification_name);
                                    $___now = $_now->toSql();
                                    
                                    $this->_db->setQuery("
                                            Insert Into #__contentbuilder_verifications
                                            (
                                            `verification_hash`,
                                            `start_date`,
                                            `verification_data`,
                                            `user_id`,
                                            `plugin`,
                                            `ip`,
                                            `setup`,
                                            `client`
                                            )
                                            Values
                                            (
                                            ".$this->_db->Quote($verification_id).",
                                            ".$this->_db->Quote($___now).",
                                            ".$this->_db->Quote('type=registration&').",
                                            ".$user_id.",
                                            ".$this->_db->Quote($data->registration_bypass_plugin).",
                                            ".$this->_db->Quote($_SERVER['REMOTE_ADDR']).",
                                            ".$this->_db->Quote($setup).",
                                            ".intval(Factory::getApplication()->isClient('administrator') ? 1 : 0)."
                                            )
                                    ");
                                    $this->_db->execute();
                                }
                            }
                        }
                    }
                    
                    if( $this->frontend && !CBRequest::getCmd('record_id',0) && $record_return && !CBRequest::getVar('return','') ){
                        
                        if( $data->force_login ){
                            if( !Factory::getApplication()->getIdentity()->get('id', 0) ){
                                if(!$this->is15){
                                    CBRequest::setVar('return', cb_b64enc(JRoute::_('index.php?option=com_users&view=login&Itemid='.CBRequest::getInt('Itemid', 0), false)));
                                }
                                else
                                {
                                    CBRequest::setVar('return', cb_b64enc(JRoute::_('index.php?option=com_user&view=login&Itemid='.CBRequest::getInt('Itemid', 0), false)));
                                }
                                
                            }else{
                                
                                if(!$this->is15){
                                    CBRequest::setVar('return', cb_b64enc(JRoute::_('index.php?option=com_users&view=profile&Itemid='.CBRequest::getInt('Itemid', 0), false)));
                                }
                                else
                                {
                                    CBRequest::setVar('return', cb_b64enc(JRoute::_('index.php?option=com_user&view=user&Itemid='.CBRequest::getInt('Itemid', 0), false)));
                                }
                            }
                        }
                        else if( trim($data->force_url) ){
                           CBRequest::setVar('cbInternalCheck', 0);
                           CBRequest::setVar('return', cb_b64enc(trim($data->force_url))); 
                        }
                    }
                    
                    if($record_return){

                        $sef = '';
                        $ignore_lang_code = '*';
                        if($data->default_lang_code_ignore){
                            $this->_db->setQuery("Select lang_code From #__languages Where published = 1 And sef = " . $this->_db->Quote(trim(CBRequest::getCmd('lang',''))));
                            $ignore_lang_code = $this->_db->loadResult();
                            if(!$ignore_lang_code){
                                $ignore_lang_code = '*';
                            }

                            $sef = trim(CBRequest::getCmd('lang',''));
                            if($ignore_lang_code == '*'){
                                $sef = '';
                            }

                        } else {
	                        $this->_db->setQuery("Select sef From #__languages Where published = 1 And lang_code = " . $this->_db->Quote($data->default_lang_code));
	                        $sef = $this->_db->loadResult();
                        }

                        $language = $data->default_lang_code_ignore ? $ignore_lang_code : $data->default_lang_code;

                        $this->_db->setQuery("Select id, edited From #__contentbuilder_records Where `type` = ".$this->_db->Quote($data->type)." And `reference_id` = ".$this->_db->Quote($data->form->getReferenceId())." And record_id = " . $this->_db->Quote($record_return));
                        $res = $this->_db->loadAssoc();
                        $last_update = Factory::getDate();
                        $last_update = $last_update->toSql();

                        if(!is_array($res)){

                            $is_future = 0;
                            $created_up = Factory::getDate();
                            $created_up = $created_up->toSql();

                            if(intval($data->default_publish_up_days) != 0){
                                $is_future = 1;
                                $date = Factory::getDate(strtotime('now +'.intval($data->default_publish_up_days).' days'));
	                            $created_up = $date->toSql();
                            }
                            $created_down = '0000-00-00 00:00:00';
                            if(intval($data->default_publish_down_days) != 0){
                                $date = Factory::getDate(strtotime($created_up.' +'.intval($data->default_publish_down_days).' days'));
	                            $created_down = $date->toSql();
                            }
                            $this->_db->setQuery("Insert Into #__contentbuilder_records (session_id,`type`,last_update,is_future,lang_code, sef, published, record_id, reference_id, publish_up, publish_down) Values ('".Factory::getSession()->getId()."',".$this->_db->Quote($data->type).",".$this->_db->Quote($last_update).",$is_future,".$this->_db->Quote($language).",".$this->_db->Quote(trim($sef)).",".$this->_db->Quote($data->auto_publish && !$is_future ? 1 : 0).", ".$this->_db->Quote($record_return).", ".$this->_db->Quote($data->form->getReferenceId()).", ".$this->_db->Quote($created_up).", ".$this->_db->Quote($created_down).")");
                            $this->_db->execute();
                        }else{
                            $this->_db->setQuery("Update #__contentbuilder_records Set last_update = ".$this->_db->Quote($last_update).",lang_code = ".$this->_db->Quote($language).", sef = ".$this->_db->Quote(trim($sef)).", edited = edited + 1 Where `type` = ".$this->_db->Quote($data->type)." And  `reference_id` = ".$this->_db->Quote($data->form->getReferenceId())." And record_id = " . $this->_db->Quote($record_return));
                            $this->_db->execute();
                        }
                    }
                
                }  else {
                    
                    $record_return = CBRequest::getCmd('record_id',0);
                    
                }

                $data->items = $data->form->getRecord($record_return, $data->published_only, $this->frontend ? ( $data->own_only_fe ? Factory::getApplication()->getIdentity()->get('id', 0)  : -1 ) : ( $data->own_only ? Factory::getApplication()->getIdentity()->get('id', 0)  : -1 ), true );

                $data_email_items = $data->form->getRecord($record_return, false, -1, true );

                $this->_db->setQuery("Select * From #__contentbuilder_records");

                $data->labels = $data->form->getElementLabels();
                $ids = array();
                foreach ($data->labels As $reference_id => $label) {
                    $ids[] = $this->_db->Quote($reference_id);
                }
                $data->labels = array();
                if (count($ids)) {
                    $this->_db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval($this->_id) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                    $rows = $this->_db->loadAssocList();
                    $ids = array();
                    foreach ($rows As $row) {
                        $ids[] = $row['reference_id'];
                    }
                }
                
                $article_id = 0;

                // creating the article
                if($data->create_articles && count($data->items)){

                    $data->page_title = $data->use_view_name_as_title ? $data->name : $data->form->getPageTitle();
                    
                    //if(!count($data->items)){
                   //     JError::raiseError(404, Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'));
                    //}
                    
                    $this->_db->setQuery("Select articles.`id` From #__contentbuilder_articles As articles, #__content As content Where content.id = articles.article_id And (content.state = 1 Or content.state = 0) And articles.form_id = " . intval($this->_id) . " And articles.record_id = " . $this->_db->Quote($record_return));
                    $article = $this->_db->loadResult();
                    
                    $config = array();
                    if($article){
	                    $config = CBRequest::getVar('jform',array());
                    }

                    $full = $this->frontend ? contentbuilder::authorizeFe('fullarticle') : contentbuilder::authorize('fullarticle');
                    $article_id = contentbuilder::createArticle($this->_id, $record_return, $data->items, $ids, $data->title_field, $data->form->getRecordMetadata($record_return), $config, $full, $this->frontend ? $data->limited_article_options_fe : $data->limited_article_options, CBRequest::getVar('cb_category_id',null));

                    if(isset($form_elements_objects)){
                        foreach($form_elements_objects As $form_elements_object){
                            if($form_elements_object instanceof CBFormElementAfterValidation){
                                $form_elements_object->onSaveArticle($article_id);
                            }
                        }
                    }
                }
                
                // required to determine blocked users in system plugin
                if($data->act_as_registration && isset($user_id) && intval($user_id) > 0){
                    $this->_db->setQuery("Insert Into #__contentbuilder_registered_users (user_id, form_id, record_id) Values (".intval($user_id).", ".$this->_id.", ".$this->_db->Quote($record_return).")");
                    $this->_db->execute();
                }
                
                if(!$data->edit_by_type){
                    
                    $cleanedValues = array();
                    foreach($values As $rawvalue){
                        if( is_array($rawvalue) ){
                            if( isset($rawvalue[0]) && $rawvalue[0] == 'cbGroupMark' ){
                                unset($rawvalue[0]);
                                $cleanedValues[] = array_values($rawvalue);
                            } else {
                                $cleanedValues[] = $rawvalue;
                            }  
                        }else{
                            $cleanedValues[] = $rawvalue;
                        }
                    }
                    
                    $submit_after_result = Factory::getApplication()->triggerEvent('onAfterSubmit', array($record_return, $article_id, $data->form, $cleanedValues));
                
                    foreach($fields As $actionField){
                        if(trim($actionField['custom_action_script'])){
                            self::customAction(trim($actionField['custom_action_script']), $record_return, $article_id, $data->form, $actionField, $fields, $cleanedValues);
                        }
                    }
                    
                    if( (!CBRequest::getCmd('record_id',0) && $data->email_notifications) || (CBRequest::getCmd('record_id',0) && $data->email_update_notifications) ){
                        $from     = $MailFrom = CBCompat::getJoomlaConfig('config.mailfrom');
                        $fromname = CBCompat::getJoomlaConfig('config.fromname');
                        
                        
                        $mailer = Factory::getMailer();
                        
                        $email_admin_template = '';
                        $email_template       = '';
                        
                        // admin email
                        if( trim($data->email_admin_recipients) ){
                            
                            // sender
                            if( trim($data->email_admin_alternative_from) ){
                                foreach($data->items As $item){
                                    $data->email_admin_alternative_from = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_alternative_from);
                                }
                                $from = $data->email_admin_alternative_from;
                            }
                            
                            if( trim($data->email_admin_alternative_fromname) ){
                                foreach($data->items As $item){
                                    $data->email_admin_alternative_fromname = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_alternative_fromname);
                                }
                                $fromname = $data->email_admin_alternative_fromname;
                            }
                            
                            $mailer->setSender(array(trim($MailFrom), trim($fromname)));
                            $mailer->addReplyTo( $from, $fromname );
                            
                            // recipients
                            foreach($data->items As $item){
                                $data->email_admin_recipients = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_recipients);
                            }
                            
                            $recipients_checked_admin = array();
                            $recipients_admin = explode(';', $data->email_admin_recipients );
                            
                            foreach($recipients_admin As $recipient_admin){
                                if(contentbuilder_is_email(trim($recipient_admin))){
                                    $recipients_checked_admin[] = trim($recipient_admin);
                                }
                            }
                            
                            $main_recipient = '';
                            
                            if( count($recipients_checked_admin) > 0 ){
                                $main_recipient = $recipients_checked_admin[0];
                                unset($recipients_checked_admin[0]);
                                $empty_array = array();
                                // fixing indexes
                                $recipients_checked_admin = array_merge($recipients_checked_admin, $empty_array);
                                // sending all the others
                                $mailer->addBCC($recipients_checked_admin);
                            }
                            
                            $mailer->addRecipient($main_recipient);
                            
                            $recipients_checked_admin = array_merge(array($main_recipient), $recipients_checked_admin);

                            $email_admin_template = contentbuilder::getEmailTemplate($this->_id, $record_return, $data_email_items, $ids, true);
                        
                            // subject
                            $subject_admin = Text::_('COM_CONTENTBUILDER_EMAIL_RECORD_RECEIVED');
                            if( trim($data->email_admin_subject) ){
                                foreach($data->items As $item){
                                    $data->email_admin_subject = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_admin_subject);
                                }
                                $subject_admin = $data->email_admin_subject;
                                $subject_admin = str_replace(array('{RECORD_ID}','{record_id}'), $record_return, $subject_admin);
                                $subject_admin = str_replace(array('{USER_ID}','{user_id}'), Factory::getApplication()->getIdentity()->get('id'), $subject_admin);
                                $subject_admin = str_replace(array('{USERNAME}','{username}'), Factory::getApplication()->getIdentity()->get('username'), $subject_admin);
                                $subject_admin = str_replace(array('{USER_FULL_NAME}','{user_full_name}'), Factory::getApplication()->getIdentity()->get('name'), $subject_admin);
                                $subject_admin = str_replace(array('{EMAIL}','{email}'), Factory::getApplication()->getIdentity()->get('email'), $subject_admin);
                                $subject_admin = str_replace(array('{VIEW_NAME}','{view_name}'), $data->name, $subject_admin);
                                $subject_admin = str_replace(array('{VIEW_ID}','{view_id}'), $this->_id, $subject_admin);
                                $subject_admin = str_replace(array('{IP}','{ip}'), $_SERVER['REMOTE_ADDR'], $subject_admin);
                                
                            }
                            
                            $mailer->setSubject($subject_admin);
                            
                            // attachments
                            foreach($data->items As $item){
                                $data->email_admin_recipients_attach_uploads = str_replace('{'.$item->recName.'}',$item->recValue, $data->email_admin_recipients_attach_uploads);
                            }
                            
                            $attachments_admin = explode(';', $data->email_admin_recipients_attach_uploads );
                            
                            $attached_admin = array();
                            foreach($attachments_admin As $attachment_admin){
                                $attachment_admin = explode("\n",str_replace("\r","",trim($attachment_admin)));
                                foreach($attachment_admin As $att_admin){
                                    if(strpos(strtolower($att_admin), '{cbsite}') === 0){
                                        $att_admin = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $att_admin);
                                    }
                                    if(JFile::exists(trim($att_admin))){
                                        $attached_admin[] = trim($att_admin);
                                    }
                                }
                            }
                            
                            $mailer->addAttachment($attached_admin);
                            
                            $mailer->isHTML($data->email_admin_html);
                            $mailer->setBody($email_admin_template);
                            
                            if(count($recipients_checked_admin)){
                            
                                $send = $mailer->Send();

                                if ( $send !== true ) {
                                    Factory::getApplication()->enqueueMessage('Error sending email: ' . $mailer->ErrorInfo, 'error');
                                }
                            }
                            
                            $mailer->ClearAddresses();
                            $mailer->ClearAllRecipients();
                            $mailer->ClearAttachments();                            
                        }
                        
                        // public email
                        if( trim($data->email_recipients) ){
                            
                            // sender
                            if( trim($data->email_alternative_from) ){
                                foreach($data->items As $item){
                                    $data->email_alternative_from = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_alternative_from);
                                }
                                $from = $data->email_alternative_from;
                            }
                            
                            if( trim($data->email_alternative_fromname) ){
                                foreach($data->items As $item){
                                    $data->email_alternative_fromname = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_alternative_fromname);
                                }
                                $fromname = $data->email_alternative_fromname;
                            }
                            
                            $mailer->setSender(array(trim($MailFrom), trim($fromname)));
                            $mailer->addReplyTo( $from, $fromname );
                            
                            // recipients
                            foreach($data->items As $item){
                                $data->email_recipients = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_recipients);
                            }
                            
                            $recipients_checked = array();
                            $recipients = explode(';', $data->email_recipients );
                            
                            foreach($recipients As $recipient){
                                if(contentbuilder_is_email($recipient)){
                                    $recipients_checked[] = $recipient;
                                }
                            }
                            
                            $main_recipient = '';
                            
                            if( count($recipients_checked) > 0 ){
                                $main_recipient = $recipients_checked[0];
                                unset($recipients_checked[0]);
                                $empty_array = array();
                                // fixing indexes
                                $recipients_checked_admin = array_merge($recipients_checked, $empty_array);
                                // sending all the others
                                $mailer->addBCC($recipients_checked);
                            }
                            
                            $mailer->addRecipient($main_recipient);
                            
                            $recipients_checked = array_merge(array($main_recipient), $recipients_checked);
                            
                            $email_template = contentbuilder::getEmailTemplate($this->_id, $record_return, $data_email_items, $ids, false);
                        
                            // subject
                            $subject = Text::_('COM_CONTENTBUILDER_EMAIL_RECORD_RECEIVED');
                            if( trim($data->email_subject) ){
                                foreach($data->items As $item){
                                    $data->email_subject = str_replace('{'.$item->recName.'}',cbinternal($item->recValue), $data->email_subject);
                                }
                                $subject = $data->email_subject;
                                $subject = str_replace(array('{RECORD_ID}','{record_id}'), $record_return, $subject);
                                $subject = str_replace(array('{USER_ID}','{user_id}'), Factory::getApplication()->getIdentity()->get('id'), $subject);
                                $subject = str_replace(array('{USERNAME}','{username}'), Factory::getApplication()->getIdentity()->get('username'), $subject);
                                $subject = str_replace(array('{EMAIL}','{email}'), Factory::getApplication()->getIdentity()->get('email'), $subject);
                                $subject = str_replace(array('{USER_FULL_NAME}','{user_full_name}'), Factory::getApplication()->getIdentity()->get('name'), $subject);
                                $subject = str_replace(array('{VIEW_NAME}','{view_name}'), $data->name, $subject);
                                $subject = str_replace(array('{VIEW_ID}','{view_id}'), $this->_id, $subject);
                                $subject = str_replace(array('{IP}','{ip}'), $_SERVER['REMOTE_ADDR'], $subject);
                                
                            }
                            
                            $mailer->setSubject($subject);
                            
                            // attachments
                            foreach($data->items As $item){
                                $data->email_recipients_attach_uploads = str_replace('{'.$item->recName.'}',$item->recValue, $data->email_recipients_attach_uploads);
                            }
                            
                            $attachments = explode(';', $data->email_recipients_attach_uploads );
                            
                            $attached = array();
                            foreach($attachments As $attachment){
                                $attachment = explode("\n",str_replace("\r","",trim($attachment)));
                                foreach($attachment As $att){
                                    if(strpos(strtolower($att), '{cbsite}') === 0){
                                        $att = str_replace(array('{cbsite}','{CBSite}'), array(JPATH_SITE, JPATH_SITE), $att);
                                    }
                                    if(JFile::exists(trim($att))){
                                        $attached[] = trim($att);
                                    }
                                }
                                
                            }
                            
                            $mailer->addAttachment($attached);
                            
                            $mailer->isHTML($data->email_html);
                            $mailer->setBody($email_template);
                            
                            if(count($recipients_checked)){
                            
                                $send = $mailer->Send();

                                if ( $send !== true ) {
                                    Factory::getApplication()->enqueueMessage('Error sending email: ' . $mailer->ErrorInfo, 'error');
                                }
                            }
                            
                            $mailer->ClearAddresses();
                            $mailer->ClearAllRecipients();
                            $mailer->ClearAttachments();
                        }
                    }
                }
                
                return $record_return;
            }
        }

	    $cache = Factory::getCache('com_content');
	    $cache->clean();
	    $cache = Factory::getCache('com_contentbuilder');
	    $cache->clean();
        
        return false;
    }

	function register($bypass_plugin, $bypass_verification_name, $verification_id, $user_id, $the_name_field, $the_username_field, $the_email_field, $the_password_field){

		if( $the_name_field === null || $the_email_field === null || $the_password_field === null || $the_username_field === null ){

			return 0;
		}

		if( $user_id ){

			jimport('joomla.user.helper');
			$db = Factory::getContainer()->get(DatabaseInterface::class);

			$pw = '';
			if(!empty($the_password_field)){
				$crypt = JUserHelper::hashPassword($the_password_field);
				$pw = $crypt;
			}

			$db->setQuery("Update #__users Set `name` = ".$db->Quote($the_name_field).", `username` = ".$db->Quote($the_username_field).", `email` = ".$db->Quote($the_email_field)." ".(!empty($pw) ? ", `password` = '$pw'" : '')." Where id = " . intval($user_id));
			$db->execute();

			return $user_id;
		}

		// else execute the registration
		Factory::getLanguage()->load('com_users', JPATH_SITE );

		$config = Factory::getConfig();
		$params = JComponentHelper::getParams('com_users');

		// Initialise the table with JUser.
		$user = new JUser;
		$data = array();
		$data['activation'] = '';
		$data['block'] = 0;

		// Prepare the data for the user object.
		$data['email']		= $the_email_field;
		$data['password']	= $the_password_field;
		$data['password_clear']	= $the_password_field;
		$data['name']	        = $the_name_field;
		$data['username']	= $the_username_field;
		$data['groups']         = array($params->get('new_usertype'));
		$useractivation = $params->get('useractivation');

		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2)) {
			$data['activation'] = ApplicationHelper::getHash(JUserHelper::genRandomPassword());
			$data['block'] = 1;
		}

		// Bind the data.
		if (!$user->bind($data)) {
			$this->setError(Text::sprintf('COM_USERS_REGISTRATION_BIND_FAILED', $user->getError()));
			return false;
		}

		// Load the users plugin group.
		JPluginHelper::importPlugin('user');

		// Store the data.
		if (!$user->save()) {
			$this->setError(Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));
			return false;
		}

		$query = Factory::getContainer()->get(DatabaseInterface::class)->getQuery(true);

		// Compile the notification mail values.
		$data = $user->getProperties();

		$data['fromname']	= $config->get('fromname');
		$data['mailfrom']	= $config->get('mailfrom');
		$data['sitename']	= $config->get('sitename');
		$data['siteurl']	= JUri::root();

		// Handle account activation/confirmation emails.
		if ($useractivation == 2)
		{
			// Set the link to confirm the user email.
			$uri = Uri::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject = Text::_('COM_USERS_EMAIL_ACCOUNT_DETAILS');
			$emailSubject = str_replace('{NAME}', $data['name'], $emailSubject);
			$emailSubject = str_replace('{SITENAME}', $data['sitename'], $emailSubject);

			$siteurl = $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'];
			if($bypass_plugin){
				$siteurl = $data['siteurl'].'index.php?option=com_contentbuilder&controller=verify&plugin='.urlencode($bypass_plugin).'&verification_name='.urlencode($bypass_verification_name).'&token='.$data['activation'].'&verification_id='.$verification_id.'&format=raw';
			}

			$emailBody = Text::_('COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY');
			$emailBody = str_replace('{NAME}', $data['name'], $emailBody);
			$emailBody = str_replace('{SITENAME}', $data['sitename'], $emailBody);
			$emailBody = str_replace('{ACTIVATE}', $siteurl, $emailBody);
			$emailBody = str_replace('{SITEURL}', $data['siteurl'], $emailBody);
			$emailBody = str_replace('{USERNAME}', $data['username'], $emailBody);
			$emailBody = str_replace('{PASSWORD_CLEAR}', $data['password_clear'], $emailBody);
		}
		else if ($useractivation == 1)
		{
			// Set the link to activate the user account.
			$uri = Uri::getInstance();
			$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
			$data['activate'] = $base.JRoute::_('index.php?option=com_users&task=registration.activate&token='.$data['activation'], false);

			$emailSubject = Text::_('COM_USERS_EMAIL_ACCOUNT_DETAILS');
			$emailSubject = str_replace('{NAME}', $data['name'], $emailSubject);
			$emailSubject = str_replace('{SITENAME}', $data['sitename'], $emailSubject);

			$siteurl = $data['siteurl'].'index.php?option=com_users&task=registration.activate&token='.$data['activation'];
			if($bypass_plugin){
				$siteurl = $data['siteurl'].'index.php?option=com_contentbuilder&controller=verify&plugin='.urlencode($bypass_plugin).'&verification_name='.urlencode($bypass_verification_name).'&token='.$data['activation'].'&verification_id='.$verification_id.'&format=raw';
			}

			$emailBody = Text::_('COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY');
			$emailBody = str_replace('{NAME}', $data['name'], $emailBody);
			$emailBody = str_replace('{SITENAME}', $data['sitename'], $emailBody);
			$emailBody = str_replace('{ACTIVATE}', $siteurl, $emailBody);
			$emailBody = str_replace('{SITEURL}', $data['siteurl'], $emailBody);
			$emailBody = str_replace('{USERNAME}', $data['username'], $emailBody);
			$emailBody = str_replace('{PASSWORD_CLEAR}', $data['password_clear'], $emailBody);

		} else {

			$emailSubject = Text::_('COM_USERS_EMAIL_ACCOUNT_DETAILS');
			$emailSubject = str_replace('{NAME}', $data['name'], $emailSubject);
			$emailSubject = str_replace('{SITENAME}', $data['sitename'], $emailSubject);

			$emailBody = Text::_('COM_USERS_EMAIL_REGISTERED_BODY');
			$emailBody = str_replace('{NAME}', $data['name'], $emailBody);
			$emailBody = str_replace('{SITENAME}', $data['sitename'], $emailBody);
			$emailBody = str_replace('{SITEURL}', $data['siteurl'], $emailBody);
		}

		// Send the registration email.
		$return = false;

		try {
			$return = Factory::getMailer()->sendMail( $data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody );
		}catch(Exception $e){

		}

		// Send Notification mail to administrators
		if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1))
		{

			$emailSubject = Text::_('COM_USERS_EMAIL_ACCOUNT_DETAILS');
			$emailSubject = str_replace('{NAME}', $data['name'], $emailSubject);
			$emailSubject = str_replace('{SITENAME}', $data['sitename'], $emailSubject);

			$emailBodyAdmin = Text::_('COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY');
			$emailBodyAdmin = str_replace('{NAME}', $data['name'], $emailBodyAdmin);
			$emailBodyAdmin = str_replace('{USERNAME}', $data['username'], $emailBodyAdmin);
			$emailBodyAdmin = str_replace('{SITEURL}', $data['siteurl'], $emailBodyAdmin);

			// Get all admin users
			$query->clear()
			      ->select(Factory::getDbo()->quoteName(array('name', 'email', 'sendEmail')))
			      ->from(Factory::getDbo()->quoteName('#__users'))
			      ->where(Factory::getDbo()->quoteName('sendEmail') . ' = ' . 1);

            Factory::getContainer()->get(DatabaseInterface::class)->setQuery($query);

			try
			{
				$rows = Factory::getContainer()->get(DatabaseInterface::class)->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

				return false;
			}

			// Send mail to all superadministrators id
			foreach ($rows as $row)
			{
				$return = Factory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

				// Check for an error.
				if ($return !== true)
				{
					$this->setError(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

					return false;
				}
			}
		}

		if ($useractivation == 0)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
		}
		elseif ($useractivation == 1)
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
		}

		// Check for an error.
		if ($return !== true) {

			Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'), 'error');

			$this->setError(Text::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

			// Send a system message to administrators receiving system mails
			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$q = "SELECT id
                        FROM #__users
                        WHERE block = 0
                        AND sendEmail = 1";
			$db->setQuery($q);
        	$sendEmail = $db->loadColumn();

            if (count($sendEmail) > 0) {
				$jdate = new JDate();
				// Build the query to add the messages
				$q = "INSERT INTO `#__messages` (`user_id_from`, `user_id_to`, `date_time`, `subject`, `message`)
                                VALUES ";
				$messages = array();
        		$___jdate = $jdate->toSql();

                foreach ($sendEmail as $userid) {
					$messages[] = "(".$userid.", ".$userid.", '".$___jdate."', '".Text::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')."', '".Text::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username'])."')";
				}
				$q .= implode(',', $messages);
				$db->setQuery($q);
				$db->execute();
			}
			return $user->id;
		}

		return $user->id;
	}
    
    function _sendMail($bypass_plugin, $bypass_verification_name, $verification_id, &$user, $password)
    {
            global $mainframe;

            $db		= Factory::getContainer()->get(DatabaseInterface::class);

            $name 		= $user->get('name');
            $email 		= $user->get('email');
            $username 	= $user->get('username');

            $usersConfig 	= JComponentHelper::getParams( 'com_users' );
            $sitename 		= $mainframe->getCfg( 'sitename' );
            $useractivation = $usersConfig->get( 'useractivation' );
            $mailfrom 		= $mainframe->getCfg( 'mailfrom' );
            $fromname 		= $mainframe->getCfg( 'fromname' );
            $siteURL		= Uri::base();

            $subject 	= sprintf ( Text::_( 'Account details for' ), $name, $sitename);
            $subject 	= html_entity_decode($subject, ENT_QUOTES);

            $siteurl_ = $siteURL."index.php?option=com_user&task=activate&activation=".$user->get('activation');
            if($bypass_plugin){
                $siteurl_ = $siteURL.'index.php?option=com_contentbuilder&controller=verify&plugin='.urlencode($bypass_plugin).'&verification_name='.urlencode($bypass_verification_name).'&token='.$user->get('activation').'&verification_id='.$verification_id.'&format=raw';
            }
            
            if ( $useractivation == 1 ){
                    $message = sprintf ( Text::_( 'SEND_MSG_ACTIVATE' ), $name, $sitename, $siteurl_, $siteURL, $username, $password);
            } else {
                    $message = sprintf ( Text::_( 'SEND_MSG' ), $name, $sitename, $siteURL);
            }

            $message = html_entity_decode($message, ENT_QUOTES);

            //get all super administrator
            $query = 'SELECT name, email, sendEmail' .
                            ' FROM #__users' .
                            ' WHERE LOWER( usertype ) = "super administrator"';
            $db->setQuery( $query );
            $rows = $db->loadObjectList();

            // Send email to user
            if ( ! $mailfrom  || ! $fromname ) {
                    $fromname = $rows[0]->name;
                    $mailfrom = $rows[0]->email;
            }

            JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);

            // Send notification to all administrators
            $subject2 = sprintf ( Text::_( 'Account details for' ), $name, $sitename);
            $subject2 = html_entity_decode($subject2, ENT_QUOTES);

            // get superadministrators id
            foreach ( $rows as $row )
            {
                    if ($row->sendEmail)
                    {
                            $message2 = sprintf ( Text::_( 'SEND_MSG_ADMIN' ), $row->name, $sitename, $name, $email, $username);
                            $message2 = html_entity_decode($message2, ENT_QUOTES);
                            JUtility::sendMail($mailfrom, $fromname, $row->email, $subject2, $message2);
                    }
            }
    }
                        
    
    function delete(){
        $items	= CBRequest::getVar( 'cid', array(), 'request', 'array' );
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_data = $this->_getList($query, 0, 1);

            if(!count($this->_data)){
	            throw new Exception(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 404);
            }

            foreach($this->_data As $data){
                if(!$this->frontend && $data->display_in == 0){
	                throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
                }else if($this->frontend && $data->display_in == 1){
	                throw new Exception(Text::_('COM_CONTENTBUILDER_RECORD_NOT_FOUND'), 404);
                }
                $data->form_id = $this->_id;
                if($data->type && $data->reference_id){
                    $data->form = contentbuilder::getForm($data->type, $data->reference_id);
                    $res = $data->form->delete($items, $data->form_id);
                    $cnt = count($items);
                    $new_items = array();
                    if($res && $cnt){
                        for($i = 0; $i < $cnt; $i++){
                            $new_items[] = $this->_db->Quote($items[$i]);
                        }
                        $new_items = implode(',', $new_items);
                        $this->_db->setQuery("Delete From #__contentbuilder_list_records Where form_id = ".intval($this->_id)." And record_id In ($new_items)");
                        $this->_db->execute();
                        $this->_db->setQuery("Delete From #__contentbuilder_records Where `type` = ".$this->_db->Quote($data->type)." And  `reference_id` = ".$this->_db->Quote($data->form->getReferenceId())." And record_id In ($new_items)");
                        $this->_db->execute();
                        if($data->delete_articles){
                            $this->_db->setQuery("Select article_id From #__contentbuilder_articles Where `type` = ".$this->_db->Quote($data->type)." And reference_id = ".$this->_db->Quote($data->form->getReferenceId())." And record_id In ($new_items)");
                            $articles = $this->_db->loadColumn();

                            if( count($articles) ){
                                $article_items = array();
								$article_ids = array();
                                foreach($articles As $article){
                                    $article_items[] = $this->_db->Quote('com_content.article.'.$article);
									$article_ids[] = $article;
                                    $table = JTable::getInstance('content');
                                    // Trigger the onContentBeforeDelete event.
                                    if(!$this->is15 && $table->load($article)){
	                                    Factory::getApplication()->triggerEvent('onContentBeforeDelete', array('com_content.article', $table));
                                    }
                                    $this->_db->setQuery("Delete From #__content Where id = ".intval($article));
                                    $this->_db->execute();
                                    // Trigger the onContentAfterDelete event.
                                    $table->reset();
                                    if(!$this->is15){
	                                    Factory::getApplication()->triggerEvent('onContentAfterDelete', array('com_content.article', $table));
                                    }
                                }
	                            $this->_db->setQuery("Delete From #__assets Where `name` In (".implode(',', $article_items).")");
	                            $this->_db->execute();

	                            $this->_db->setQuery("Delete From #__workflow_associations Where item_id In (".implode(',', $article_ids).")");
	                            $this->_db->execute();

								echo "Delete From #__workflow_associations Where item_id In (".implode(',', $article_ids).")";

                            }
                        }
                        
                        $this->_db->setQuery("Delete From #__contentbuilder_articles Where `type` = ".$this->_db->Quote($data->type)." And reference_id = ".$this->_db->Quote($data->form->getReferenceId())." And record_id In ($new_items)");
                        $this->_db->execute();
                    }
                }
            }
        }
        
        if(!$this->is15){
            $cache = Factory::getCache('com_content');
            $cache->clean();
            $cache = Factory::getCache('com_contentbuilder');
            $cache->clean();
            
        }else{
            $cache = Factory::getCache('com_content');
            $cache->clean();
            $cache = Factory::getCache('com_contentbuilder');
            $cache->clean();
        }
    }
    
    function change_list_states(){
        
        $this->_db->setQuery('Select reference_id From #__contentbuilder_forms Where id = '.intval($this->_id));
        $reference_id = $this->_db->loadResult(); 
        if(!$reference_id){
            return;
        }
        
        // prevent from changing to an unpublished state
        $this->_db->setQuery("Select id, action From #__contentbuilder_list_states Where published = 1 And id = ".CBRequest::getInt('list_state', 0)." And form_id = " . $this->_id);
        $res = $this->_db->loadAssoc();
        if(!is_array($res)){
            return;
        }
        
        JPluginHelper::importPlugin('contentbuilder_listaction', $res['action']);
        $items	= CBRequest::getVar( 'cid', array(), 'request', 'array' );
        
        $result = Factory::getApplication()->triggerEvent('onBeforeAction', array($this->_id, $items));
        $error = implode('',$result);
        
        if($error){
            Factory::getApplication()->enqueueMessage($error);
        }
        
        foreach($items As $item){
            $this->_db->setQuery("Select id From #__contentbuilder_list_records Where form_id = ".$this->_id." And record_id = " . $this->_db->Quote($item));
            $res = $this->_db->loadResult();
            if(!$res){
                $this->_db->setQuery("Insert Into #__contentbuilder_list_records (state_id, form_id, record_id, reference_id) Values (".CBRequest::getInt('list_state', 0).", ".$this->_id.", ".$this->_db->Quote($item).", ".$this->_db->Quote($reference_id).")");
                $this->_db->execute();
            }else{
                $this->_db->setQuery("Update #__contentbuilder_list_records Set state_id = ".CBRequest::getInt('list_state', 0) . " Where form_id = ".$this->_id." And record_id = " . $this->_db->Quote($item));
                $this->_db->execute();
            }
        }
        
        $result = Factory::getApplication()->triggerEvent('onAfterAction', array($this->_id, $items, $error));
        $error = implode('',$result);
        
        if($error){
            Factory::getApplication()->enqueueMessage($error);
        }
    }
    
    function change_list_language(){
        $this->_db->setQuery('Select reference_id,`type` From #__contentbuilder_forms Where id = '.intval($this->_id));
        $typeref = $this->_db->loadAssoc();
         
        if(!is_array($typeref)){
            return;
        }
        
        $reference_id = $typeref['reference_id'];
        $type = $typeref['type'];
        
        $items	= CBRequest::getVar( 'cid', array(), 'request', 'array' );
        
        $sef = '';
        $this->_db->setQuery("Select sef From #__languages Where published = 1 And lang_code = " . $this->_db->Quote(CBRequest::getVar('list_language','*')));
        $sef = $this->_db->loadResult();
        
        foreach($items As $item){
            $this->_db->setQuery("Select id From #__contentbuilder_records Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
            $res = $this->_db->loadResult();
            if(!$res){
                $this->_db->setQuery("Insert Into #__contentbuilder_records (`type`,lang_code, sef, record_id, reference_id) Values (".$this->_db->Quote($type).",".$this->_db->Quote(CBRequest::getVar('list_language','*')).", ".$this->_db->Quote($sef).", ".$this->_db->Quote($item).", ".$this->_db->Quote($reference_id).")");
                $this->_db->execute();
            }else{
                $this->_db->setQuery("Update #__contentbuilder_records Set sef = ".$this->_db->Quote($sef).", lang_code = ".$this->_db->Quote(CBRequest::getVar('list_language','*')) . " Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
                $this->_db->execute();
            }

	        $this->_db->setQuery("Update #__contentbuilder_articles As articles, #__content As content Set content.language = ".$this->_db->Quote(CBRequest::getVar('list_language','*')) . " Where ( content.state = 1 Or content.state = 0 ) And content.id = articles.article_id And articles.`type` = ".intval($type)." And articles.reference_id = ".$this->_db->Quote($reference_id)." And articles.record_id = " . $this->_db->Quote($item));
	        $this->_db->execute();
        }

        $cache = Factory::getCache('com_content');
        $cache->clean();
        $cache = Factory::getCache('com_contentbuilder');
        $cache->clean();
    }
    
    function change_list_publish(){
        $this->_db->setQuery('Select reference_id,`type` From #__contentbuilder_forms Where id = '.intval($this->_id));
        $typeref = $this->_db->loadAssoc();
         
        if(!is_array($typeref)){
            return;
        }
        
        $reference_id = $typeref['reference_id'];
        $type = $typeref['type'];
        
        $items	= CBRequest::getVar( 'cid', array(), 'request', 'array' );
        
        $this->_db->setQuery("SET @ids := null");
        $this->_db->execute();
        
        $created_up = Factory::getDate();
	    $created_up = $created_up->toSql();
        
        foreach($items As $item){
            $this->_db->setQuery("Select id, publish_up From #__contentbuilder_records Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
            $res = $this->_db->loadAssoc();
            
            if(!is_array($res)){
                $this->_db->setQuery("Insert Into #__contentbuilder_records (`type`,published, record_id, reference_id) Values (".$this->_db->Quote($type).",".(CBRequest::getInt('list_publish', 0) ? 1 : 0).", ".$this->_db->Quote($item).", ".$this->_db->Quote($reference_id).")");
                $this->_db->execute();
            }else{
                $this->_db->setQuery("Update #__contentbuilder_records Set is_future = 0, ".(CBRequest::getInt('list_publish', 0) ? "publish_up = '".$created_up."',publish_down = '0000-00-00 00:00:00'," : "publish_up = '0000-00-00 00:00:00',publish_down = '0000-00-00 00:00:00',")." published = ".(CBRequest::getInt('list_publish', 0) ? 1 : 0) . " Where `type` = ".$this->_db->Quote($type)." And `reference_id` = ".$this->_db->Quote($reference_id)." And record_id = " . $this->_db->Quote($item));
                $this->_db->execute();
            }

            $this->_db->setQuery("Update #__contentbuilder_articles As articles, #__content As content Set ".(CBRequest::getInt('list_publish', 0) ? "content.publish_up = '".$created_up."',publish_down = '0000-00-00 00:00:00'," : "content.publish_up = '".(is_array($res) ? $res['publish_up'] : $created_up)."',publish_down = '0000-00-00 00:00:00',")." content.state = ".(CBRequest::getInt('list_publish', 0) ? 1 : 0) . " Where ( SELECT @ids := CONCAT_WS(',', content.id, @ids) ) And ( content.state = 1 Or content.state = 0 ) And content.id = articles.article_id And articles.`type` = ".$this->_db->quote($type)." And articles.reference_id = ".$this->_db->Quote($reference_id)." And articles.record_id = " . $this->_db->Quote($item));
            $this->_db->execute();
        }
        $this->_db->setQuery("SELECT @ids");
        $select_ids = $this->_db->loadResult();
        if( $select_ids ){
            $affected_articles = explode(',',$this->_db->loadResult());
        }
	    $cache = Factory::getCache('com_content');
	    $cache->clean();
	    $cache = Factory::getCache('com_contentbuilder');
	    $cache->clean();

	    // Trigger the onContentChangeState event.
	    $result = Factory::getApplication()->triggerEvent('onContentChangeState', array('com_content.article', $affected_articles, CBRequest::getInt('list_publish', 0)));

    }
}

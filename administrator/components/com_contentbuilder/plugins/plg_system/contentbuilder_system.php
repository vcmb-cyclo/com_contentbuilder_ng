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

if( !defined( 'DS' ) ){
    define('DS', DIRECTORY_SEPARATOR);
}

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');

jimport( 'joomla.plugin.plugin' );

class  plgSystemContentbuilder_system extends JPlugin
{
        private $caching = 0;
        
	function __construct( &$subject, $params )
        {
            parent::__construct($subject, $params);
            
        }
        
        function onBeforeRender(){
            
            $pluginParams = CBCompat::getPluginParams($this, 'system', 'contentbuilder_system');

            if($pluginParams->def('nocache', 1)){
                CBCompat::setJoomlaConfig('config.caching', $this->caching);
            }
        }
        
        function onAfterDispatch(){
            
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
        
            if(!JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php'))
            {
                return;
            }
            
            $db   = Factory::getContainer()->get(DatabaseInterface::class);
            $app  = Factory::getApplication();
            
             // managing auto-groups
            jimport('joomla.version');
            $version = new JVersion();
            
            if(version_compare($version->getShortVersion(), '1.6', '>=') && ( CBRequest::getVar('option') == 'com_kunena' || CBRequest::getVar('option') == 'com_contentbuilder')){
                
                $pluginParams = CBCompat::getPluginParams($this, 'system', 'contentbuilder_system');
                
                if(intval($pluginParams->get('is_auto_groups', 0)) == 1 && count($pluginParams->get('auto_groups', array()))){
                
                    $db = Factory::getContainer()->get(DatabaseInterface::class);

                    $operateViews = array();
                    if($pluginParams->get('auto_groups_limit_views','') != ''){
                        $operateViews = explode(',',$pluginParams->get('auto_groups_limit_views',''));
                        $operateViewsCnt = count($operateViews);
                        for($i = 0; $i < $operateViewsCnt; $i++){
                            $operateViews[$i] = intval($operateViews[$i]);
                        }
                    }
                    
                    // KUNENA SUPPORT, REMOVES THE KUNENA SESSION IF EXISTING ON GROUP UPDATES
                    jimport('joomla.filesystem.folder');
                    $kill_kunena_session = false;
                    if(JFolder::exists(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_kunena'.DS)){
                        $kill_kunena_session = true;
                    }
                    
                    $db->setQuery("
                        Select cv.userid, cv.verified_view, cv.verification_date_view, forms.verification_days_view, groups.group_id, groups.user_id
                            From 
                        (
                            #__contentbuilder_users As cv,
                            #__contentbuilder_forms As forms
                        )
                        Left Join #__user_usergroup_map As groups On ( groups.user_id = cv.userid And groups.group_id In (".implode(',', $pluginParams->get('auto_groups', array())).") )
                            Where 
                        cv.verification_date_view <> '0000-00-00 00:00:00' 
                            And 
                        cv.verified_view = 1
                            And
                        cv.userid <> 0
                            And
                        cv.form_id = forms.id 
                            And
                        cv.published = 1
                            And
                        forms.verification_required_view = 1
                            And
                        ".(count($operateViews) ? ' forms.id In ('.implode(',', $operateViews).') And ' : '')."
                        forms.published = 1
                            And
                        (
                            (
                               groups.user_id Is Null And groups.group_id Is Null
                                Or
                               groups.user_id = cv.userid And groups.group_id Not In (".implode(',', $pluginParams->get('auto_groups', array())).")
                            )
                        )");
                    
                    $users = $db->loadAssocList();
                    
                    foreach($users As $user){
                        $groups = $pluginParams->get('auto_groups', array());
                        foreach($groups As $group){
                            $db->setQuery("Insert Ignore Into #__user_usergroup_map (user_id, group_id) Values (".$user['userid'].", ".intval($group).")");
                            $db->execute();
                            if($kill_kunena_session){
                                $db->setQuery("Delete From #__kunena_sessions Where userid = ".$user['userid']);
                                $db->execute();
                            }
                        }
                    }
                    
                    $db->setQuery("
                        Select cv.id, groups.user_id, groups.group_id, cv.userid, cv.verified_view
                            From 
                        #__user_usergroup_map As groups
                            Left Join #__contentbuilder_users As cv On ( cv.userid = groups.user_id And groups.group_id In (".implode(',', $pluginParams->get('auto_groups', array())).") ) 
                        Where 
                            cv.userid = groups.user_id
                        And
                            cv.userid Is Not Null
                        And
                            groups.group_id In (".implode(',', $pluginParams->get('auto_groups', array())).")
                        Group By groups.user_id, groups.group_id
                            Having Sum(cv.verified_view) = 0"
                    );
                    
                    $user_groups = $db->loadAssocList();
                    
                    foreach($user_groups As $user_group){
                        $db->setQuery("Delete From #__user_usergroup_map Where user_id = ".$user_group['user_id']." And group_id = ".intval($user_group['group_id'])."");
                        $db->execute();
                        if($kill_kunena_session){
                            $db->setQuery("Delete From #__kunena_sessions Where userid = ".$user_group['user_id']);
                            $db->execute();
                        }
                    }
                }
            }
            // managing auto-groups END
            
            if($app->isClient('site')){
                
                // loading the required themes, if any
                $body = JFactory::getDocument()->getBuffer('component');
                preg_match_all("/<!--\(cbArticleId:(\d{1,})\)-->/si", $body, $matched_ids);
                
                $ids = array();
                if(isset($matched_ids[1]) && is_array($matched_ids[1])){
                    foreach( $matched_ids[1] As $id ){
                        if( !in_array(intval($id), $ids) ){
                            $ids[] = intval($id);
                        }
                    }
                }
                $the_ids = implode(',', $ids);
                
                if($the_ids){
                    JFactory::getDocument()->addScript(JURI::root(true).'/components/com_contentbuilder/assets/js/contentbuilder.js');
                    $db->setQuery("Select Distinct forms.theme_plugin From #__contentbuilder_forms As forms, #__contentbuilder_articles As articles, #__content As content Where forms.id = articles.form_id And articles.article_id In (".$the_ids.") And content.id = articles.article_id And (content.state = 1 Or content.state = 0)");
                    $themes = $db->loadColumn();
                    foreach($themes As $theme){
                        if($theme){
                            JPluginHelper::importPlugin('contentbuilder_themes', $theme);
                            $results_css = Factory::getApplication()->triggerEvent('onContentTemplateCss', array());
                            $results_js  = Factory::getApplication()->triggerEvent('onContentTemplateJavascript', array());
                            JFactory::getDocument()->addStyleDeclaration(implode('',$results_css));
                            JFactory::getDocument()->addScriptDeclaration(implode('',$results_js));
                        }
                    }
                }
                // theme loading end
                
                $option = CBRequest::getCmd('option', '');
                $view   = CBRequest::getCmd('view', '');
                $task   = CBRequest::getCmd('task', '');
                $layout = CBRequest::getCmd('layout', '');
                $id     = CBRequest::getVar('id', 0);
                $id     = explode(':', $id);
                $id     = intval($id[0]);
                $a_id   = CBRequest::getVar('a_id', 0);
                $a_id   = explode(':', $a_id);
                $a_id   = intval($a_id[0]);
                
                $pluginParams = CBCompat::getPluginParams($this, 'system', 'contentbuilder_system');
                
                // if somebody tries to submit an article through the built-in joomla content submit
                if( $pluginParams->def('disable_new_articles', 0) && trim(CBRequest::getCmd('option','')) == 'com_content' && ( trim(CBRequest::getCmd('task','')) == 'new' || trim(CBRequest::getCmd('task','')) == 'article.add' || ( trim(CBRequest::getCmd('view','')) == 'article' && trim(CBRequest::getCmd('layout','')) == 'form' ) || ( trim(CBRequest::getCmd('view','')) == 'form' && trim(CBRequest::getCmd('layout','')) == 'edit' ) && $a_id <= 0 ) ){
                    JFactory::getLanguage()->load('com_contentbuilder');
	                Factory::getApplication()->enqueueMessage(JText::_('COM_CONTENTBUILDER_PERMISSIONS_NEW_NOT_ALLOWED'), 'error');
                    JFactory::getApplication()->redirect('index.php');
                }
                
                // redirect to content edit if there is a record existing for this article
                if( $option == 'com_content' && ( ( $id && $view == 'article' && $task == 'edit' ) || ( $a_id && $view == 'form' && $layout == 'edit' ) ) ){
                    $id = $a_id;
                    $db->setQuery("Select article.record_id, article.form_id From #__contentbuilder_articles As article, #__content As content Where content.id = " . intval($id) . " And (content.state = 0 Or content.state = 1) And article.article_id = content.id");
                    $article = $db->loadAssoc();
                    if(is_array($article)){
                        JFactory::getApplication()->redirect('index.php?option=com_contentbuilder&controller=edit&id='.$article['form_id']."&record_id=".$article['record_id']."&jsback=1&Itemid=".CBRequest::getInt('Itemid',0));
                    }
                }
            }
        }
        
        function onAfterRoute(){
            
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
        
            if(!JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php'))
            {
                return;
            }
            
            // register non-existent records
            if( in_array(CBRequest::getVar('option', ''), array('com_contentbuilder', 'com_content')) ){
                
                $db = Factory::getContainer()->get(DatabaseInterface::class);
                
                require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
                $db->setQuery("Select `type`, `reference_id` From #__contentbuilder_forms Where published = 1");
                $views = $db->loadAssocList();
                $typeview = array();
                foreach($views As $view){
                    if(!isset($typeview[$view['type'].$view['reference_id']])){
                        $typeview[$view['type'].$view['reference_id']] = true;
                        $form = contentbuilder::getForm($view['type'], $view['reference_id']);
                        if(is_object($form)){
                            $form->synchRecords();
                        }
                    }
                }
            }
            
            if(CBRequest::getCmd('option', '') == 'com_content' || CBRequest::getCmd('option', '') == 'com_contentbuilder'){
                // managing published states
                
                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $date = JFactory::getDate();
                
                $db->setQuery("Update #__contentbuilder_records Set published = 1 Where is_future = 1 And publish_up <> '0000-00-00 00:00:00' And publish_up <= '".CBCompat::toSql($date)."'");
                $db->execute();
                
                $db->setQuery("Update #__contentbuilder_records Set published = 0 Where publish_down <> '0000-00-00 00:00:00' And publish_down <= '".CBCompat::toSql($date)."'");
                $db->execute();
                
                // published states END
            }
            
            // joomla 1.5 and following obviously has problems when logging out and being in list view and the menu item access being registered.
            // J! is then trying to redirect to com_content (for non-obvious reasons), using the view variable orginally used in contentbuilder and then it will 
            // throw an error 500, view not found
            // this will get rid of the view parameter and pass the rest of the url to the return parameter
            $base = 'base';
            $sixty_four = '64';
            $enc = cb_b64dec(CBRequest::getVar('return', '', 'method', $base.$sixty_four));
            if(is_string($enc)){
                $enc = explode('?',$enc);
                count($enc) > 1 ? parse_str($enc[1], $out) : $out = array();
                if(isset($out['option']) && $out['option'] == 'com_contentbuilder'){
                    $i = 0;
                    $length = count($out);
                    $return = '';
                    foreach($out As $key => $value){
                        if(strtolower($key) != 'view'){
                            $return .= $key.'='.$value.($i + 1 < $length ? '&' : '');
                        }
                        $i++;
                    }
                    CBRequest::setVar('return', cb_b64enc('index.php'.($return ? '?' : '').$return));
                }
            }
            
            if( in_array(CBRequest::getVar('option'), array('com_content') ) ){
                
                $pluginParams = CBCompat::getPluginParams($this, 'system', 'contentbuilder_system');
                
                if($pluginParams->def('nocache', 1)){
                    $this->caching = CBCompat::getJoomlaConfig('config.caching');
                    CBCompat::setJoomlaConfig('config.caching', 0);
                }
            }
             
            if(CBRequest::getVar('option') == 'com_contentbuilder'){
                
                Factory::getContainer()->get(DatabaseInterface::class)->setQuery("
                    Update 
                        #__contentbuilder_records As records,
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        records.published = 0
                    Where
                        records.reference_id = forms.reference_id
                    And
                        records.published = 1
                    And
                        records.`type` = forms.`type`
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        records.record_id = cbusers.record_id
                    And
                      (
                        (
                            users.id = cbusers.user_id
                          And
                            users.block = 1
                        )
                      )
                    ");
                Factory::getContainer()->get(DatabaseInterface::class)->execute();
                
                Factory::getContainer()->get(DatabaseInterface::class)->setQuery("
                    Update 
                        #__contentbuilder_records As records,
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        records.published = forms.auto_publish
                    Where
                        records.reference_id = forms.reference_id
                    And
                        records.published = 0
                    And
                        records.`type` = forms.`type`
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        records.record_id = cbusers.record_id
                    And
                        users.id = cbusers.user_id
                    And
                        users.block = 0
                    ");
                Factory::getContainer()->get(DatabaseInterface::class)->execute();
            }
        }
        
        function onAfterInitialise()
	{
            $this->onAfterInitialize();
        }
        
	function onAfterInitialize()
	{
            jimport('joomla.filesystem.file');
            jimport('joomla.filesystem.folder');
        
            if(!JFile::exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php'))
            {
                return;
            }
            
            $app  = JFactory::getApplication();
            
            if(!$app->isClient('site')){
               return; 
            }
            
            // synch the records if there are any changes
            if($app->isClient('site')){

                $db         = Factory::getContainer()->get(DatabaseInterface::class);
                $user       = Factory::getUser();
                
                $db->setQuery("
                    Update
                        #__contentbuilder_articles As articles,
                        #__content As content, 
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        content.state = 0
                    Where 
                        articles.article_id = content.id
                    And
                        content.state = 1
                    And
                        articles.form_id = forms.id
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        content.created_by = cbusers.user_id
                    And
                      (
                        (
                            users.id = cbusers.user_id
                          And
                            users.block = 1
                        )
                      )
                    ");
                $db->execute();
                
                $db->setQuery("
                    Update 
                        #__contentbuilder_articles As articles,
                        #__content As content, 
                        #__contentbuilder_forms As forms,
                        #__contentbuilder_records As records,
                        #__contentbuilder_registered_users As cbusers,
                        #__users As users
                    Set 
                        content.state = forms.auto_publish
                    Where 
                        articles.article_id = content.id
                    And
                        content.state = 0
                    And
                        articles.form_id = forms.id
                    And
                        forms.act_as_registration = 1
                    And
                        forms.id = cbusers.form_id
                    And
                        content.created_by = cbusers.user_id
                    And
                        users.id = cbusers.user_id
                    And
                        records.record_id = cbusers.record_id
                    And
                        records.`type` = forms.`type`
                    And
                        users.block = 0
                    ");
                $db->execute();
                
                $pluginParams = CBCompat::getPluginParams($this, 'system', 'contentbuilder_system');
                
                require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
                
                $db->setQuery("
                Select 
                    form.id As form_id,
                    form.act_as_registration,
                    form.default_category,
                    form.registration_name_field, 
                    form.registration_username_field, 
                    form.registration_email_field, 
                    form.registration_email_repeat_field, 
                    form.`last_update`,
                    article.`article_id`,
                    form.`title_field`,
                    form.`create_articles`,
                    form.`name`,
                    form.`use_view_name_as_title`,
                    form.`protect_upload_directory`,
                    form.`reference_id`,
                    records.`record_id`,
                    form.`type`,
                    form.`published_only`,
                    form.`own_only`,
                    form.`own_only_fe`,
                    records.`last_update` As record_last_update,
                    article.`last_update` As article_last_update
                From
                    #__contentbuilder_records As records
                    Left Join #__contentbuilder_forms As form On ( form.`type` = records.`type` And form.reference_id = records.reference_id )
                    Left Join #__contentbuilder_articles As article On ( form.`type` = records.`type` And form.reference_id = records.reference_id And article.form_id = form.id And article.record_id = records.record_id )
                    Left Join #__content As content On ( form.`type` = records.`type` And form.reference_id = records.reference_id And article.article_id = content.id And article.form_id = form.id And article.record_id = records.record_id )
                Where 
                    form.`published` = 1
                And
                    form.create_articles = 1
                And
                    form.`type` = records.`type`
                And 
                    form.reference_id = records.reference_id
                And
                   (
                     (
                        article.form_id = form.id 
                      And 
                        article.record_id = records.record_id
                      And 
                        article.article_id = content.id 
                      And 
                        ( content.state = 1 Or content.state = 0 )
                      And
                      (
                        form.`last_update` > article.`last_update`   
                       Or
                        records.`last_update` > article.`last_update`
                      )
                     )
                     Or
                     (
                        form.id Is Not Null And records.id Is Not Null And content.id Is Null And article.id Is Null
                     )
                   )
                Limit " . intval($pluginParams->def('limit_per_turn', 50)));
                $list = $db->loadAssocList();
                
                if(isset($list[0])){
                    $lang = JFactory::getLanguage();
                    $lang->load('com_contentbuilder', JPATH_ADMINISTRATOR);
                }
                
                $jdate = JFactory::getDate();
                $now   = CBCompat::toSql($jdate);
                
                foreach($list As $data){
                
                    if(is_array($data)){

                        $form = contentbuilder::getForm($data['type'], $data['reference_id']);
                        if(!$form || !$form->exists){
                            return;
                        }

                        // creating the article
                        if($data['create_articles']){
                            
                            $data['labels'] = $form->getElementLabels();
                            $ids = array();
                            foreach ($data['labels'] As $reference_id => $label) {
                                $ids[] = $db->Quote($reference_id);
                            }

                            if (count($ids)) {
                                $db->setQuery("Select Distinct `label`, reference_id From #__contentbuilder_elements Where form_id = " . intval($data['form_id']) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                                $rows = $db->loadAssocList();
                                $ids = array();
                                foreach ($rows As $row) {
                                    $ids[] = $row['reference_id'];
                                }
                            }
                            
                            $data['items'] = $form->getRecord($data['record_id'], false, -1, true);

                            $article_id = contentbuilder::createArticle($data['form_id'], $data['record_id'], $data['items'], $ids, $data['title_field'], $form->getRecordMetadata($data['record_id']), array(), false, 1, $data['default_category']);
                
                            if($article_id){
                                $db->setQuery("Update #__contentbuilder_articles Set `last_update`=".$db->Quote($now) . " Where article_id = " . $db->Quote($article_id) . " And record_id = " . $db->Quote($data['record_id']) . " And form_id = " . $db->Quote($data['form_id']));
                                $db->execute();
                            }
                        }
                    }
                }
            }
	}
}

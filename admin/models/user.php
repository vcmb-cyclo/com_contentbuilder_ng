<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/


// No direct access

use Joomla\Utilities\ArrayHelper;

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderModelUser extends CBModel
{
    private $_form_id = 0;

    function  __construct($config)
    {
        parent::__construct();

        $this->setIds(CBRequest::getInt('joomla_userid',  0), CBRequest::getInt('form_id',  ''));
        
    }

    /*
     * MAIN DETAILS AREA
     */

    /**
     *
     * @param int $id
     */
    function setIds($id, $form_id) {
        // Set id and wipe data
        $this->_id = $id;
        $this->_form_id = $form_id;
        $this->_data = null;
    }

    private function _buildQuery(){
        return 'Select SQL_CALC_FOUND_ROWS users.*, contentbuilder_users.limit_edit, contentbuilder_users.limit_add, contentbuilder_users.id As cb_id, contentbuilder_users.form_id, contentbuilder_users.verification_date_edit, contentbuilder_users.verification_date_new, contentbuilder_users.verification_date_view, contentbuilder_users.verified_view, contentbuilder_users.verified_new, contentbuilder_users.verified_edit, contentbuilder_users.records, contentbuilder_users.published From #__users As users Left Join #__contentbuilder_users As contentbuilder_users On ( users.id = contentbuilder_users.userid And contentbuilder_users.form_id = '.CBRequest::getInt('form_id',0).' ) Where users.id = ' . $this->_id;
                
    }
    
    function setListVerifiedView()
    {
        $items	= CBRequest::getVar( 'cid', array(), 'post', 'array' );
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".CBRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && CBRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".CBRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->execute();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_view = 1 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->execute();
        }
    }
    
    function setListNotVerifiedView()
    {
        $items	= CBRequest::getVar( 'cid', array(), 'post', 'array' );
        ArrayHelper::toInteger($items);
        if (count($items)) {
            
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".CBRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && CBRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".CBRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->execute();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_view = 0 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->execute();
        }
    }

    function setListVerifiedNew()
    {
        $items	= CBRequest::getVar( 'cid', array(), 'post', 'array' );
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".CBRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && CBRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".CBRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->execute();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_new = 1 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->execute();
        }
    }
    
    function setListNotVerifiedNew()
    {
        $items	= CBRequest::getVar( 'cid', array(), 'post', 'array' );
        ArrayHelper::toInteger($items);
        if (count($items)) {
            
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".CBRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && CBRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".CBRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->execute();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_new = 0 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->execute();
        }
    }
    
    function setListVerifiedEdit()
    {
        $items	= CBRequest::getVar( 'cid', array(), 'post', 'array' );
        ArrayHelper::toInteger($items);
        if (count($items)) {
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".CBRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && CBRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".CBRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->execute();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_edit = 1 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->execute();
        }
    }
    
    function setListNotVerifiedEdit()
    {
        $items	= CBRequest::getVar( 'cid', array(), 'post', 'array' );
        ArrayHelper::toInteger($items);
        if (count($items)) {
            
            $cids = $items;
            foreach($cids As $cid){
                $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".CBRequest::getInt('form_id',0)." And userid = " . $cid);
                if(!$this->_db->loadResult() && CBRequest::getInt('form_id',0) && $cid){
                    $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".CBRequest::getInt('form_id',0).", $cid, 1)");
                    $this->_db->execute();
                }
            }
            
            $this->_db->setQuery( ' Update #__contentbuilder_users '.
                        '  Set verified_edit = 0 Where form_id = '.$this->_form_id.' And userid In ( '.implode(',', $items) . ')' );
            $this->_db->execute();
        }
    }
    
    function getData()
    {
        // Lets load the data if it doesn't already exist
        if (empty( $this->_data ))
        {
            $query = $this->_buildQuery();
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
            
            if($this->_data->published === null){
                $this->_data->published = 1;
            }
            
            return $this->_data;
        }
        return null;
    }
    
    function store()
    {
        $insert = 0;
        $this->_db->setQuery("Select id From #__contentbuilder_users Where form_id = ".CBRequest::getInt('form_id',0)." And userid = " . CBRequest::getInt('joomla_userid',0));
        if(!$this->_db->loadResult() && CBRequest::getInt('form_id',0) && CBRequest::getInt('joomla_userid',0)){
            $this->_db->setQuery("Insert Into #__contentbuilder_users (form_id, userid, published) Values (".CBRequest::getInt('form_id',0).", ".CBRequest::getInt('joomla_userid',0).", 1)");
            $this->_db->execute();
            $insert = $this->_db->insertid();
        }
        
        $data = CBRequest::get( 'post' );
        
        if(!$insert){
            $data['id'] = intval($data['cb_id']);
        }else{
            $data['id'] = $insert;
        }
        
        $data['userid'] = $data['joomla_userid'];
        
        
        $data['verified_view'] = CBRequest::getInt('verified_view',0);
        $data['verified_new'] = CBRequest::getInt('verified_new',0);
        $data['verified_edit'] = CBRequest::getInt('verified_edit',0);
        $data['published'] = CBRequest::getInt('published',0);
        
        $row = $this->getTable('cbuser');
        
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        if (!$row->check()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        $storeRes = $row->store();

        if (!$storeRes) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }
        
        return true;
    }
}

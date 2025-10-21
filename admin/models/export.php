<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_contentbuilder'.DS.'classes'.DS.'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderModelExport extends CBModel
{

    private $frontend = false;
    
    private $_menu_filter = array();
    
    private $_menu_filter_order = array();
    
    function  __construct($config) {
        parent::__construct($config);

        $this->frontend = Factory::getApplication()->isClient('site');
        
        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $this->setId(CBRequest::getInt('id',0));

        if(Factory::getApplication()->getSession()->get($option.'formsd_id', 0) == 0 || Factory::getApplication()->getSession()->get($option.'formsd_id', 0) == $this->_id ){
            $filter_order     = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_order', 'filter_order', '', 'cmd' );
            $filter_order_Dir = $mainframe->getUserStateFromRequest( $option.'formsd_filter_order_Dir', 'filter_order_Dir', 'desc', 'cmd' );
            $filter           = $mainframe->getUserStateFromRequest(  $option.'formsd_filter', 'filter', '', 'string' );
            $filter_state     = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_state', 'list_state_filter', 0, 'int' );
            $filter_publish   = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_publish', 'list_publish_filter', -1, 'int' );
            $filter_language  = $mainframe->getUserStateFromRequest(  $option.'formsd_filter_language', 'list_language_filter', '', 'cmd' );
        }else{
            $mainframe->setUserState($option.'formsd_filter_order', CBRequest::getCmd('filter_order',''));
            $mainframe->setUserState($option.'formsd_filter_order_Dir', CBRequest::getCmd('filter_order_Dir',''));
            $mainframe->setUserState($option.'formsd_filter', CBRequest::getVar('filter',''));
            $mainframe->setUserState($option.'formsd_filter_state', CBRequest::getInt('list_state_filter',0));
            $mainframe->setUserState($option.'formsd_filter_publish', CBRequest::getInt('list_publish_filter',-1));
            $mainframe->setUserState($option.'formsd_filter_language', CBRequest::getCmd('list_language_filter',''));
            $filter_order     = CBRequest::getCmd('filter_order','');
            $filter_order_Dir = CBRequest::getCmd('filter_order_Dir','');
            $filter           = CBRequest::getVar('filter','');
            $filter_state     = CBRequest::getInt('list_state_filter',0);
            $filter_publish   = CBRequest::getInt('list_publish_filter',-1);
            $filter_language  = CBRequest::getCmd('list_language_filter','');
        }
        
        $this->setState('formsd_filter_state', $filter_state);
        $this->setState('formsd_filter_publish', $filter_publish);
        $this->setState('formsd_filter_language', empty($filter_language) ? null : $filter_language);
        $this->setState('formsd_filter', $filter);
        $this->setState('formsd_filter_order', $filter_order);
        $this->setState('formsd_filter_order_Dir', $filter_order_Dir);

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
        
        Factory::getApplication()->getSession()->set($option.'forms_id', $this->_id);
    }

    function setId($id) {
        // Set id and wipe data
        $this->_id      = $id;
        $this->_data    = null;
    }

    /*
     *
     * MAIN LIST AREA
     *
     */

    /**
     * @return string The query
     */
    private function _buildQuery(){
        return 'Select SQL_CALC_FOUND_ROWS * From #__contentbuilder_forms Where id = '.intval($this->_id).' And published = 1';
    }

    /**
    * Gets the currencies
    * @return array List of products
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
	                throw new Exception(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 404);
                }else if($this->frontend && $data->display_in == 1){
	                throw new Exception(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 404);
                }

                if(is_array($data->export_xls) && !count($data->export_xls)){
	                throw new Exception(Text::_('Not exportable error'), 404);
                }
                $data->form_id = $this->_id;
                if($data->type && $data->reference_id){
                    $data->form = contentbuilder::getForm($data->type, $data->reference_id);
                    if(!$data->form->exists){
	                    throw new Exception(Text::_('COM_CONTENTBUILDER_FORM_NOT_FOUND'), 404);
                    }
                    $searchable_elements = contentbuilder::getListSearchableElements($this->_id);
                    $data->labels = $data->form->getElementLabels();
                    
                    if(
                            Factory::getApplication()->getSession()->get('com_contentbuilder.filter_signal.'.$this->_id, false) 
                                
                            && $data->allow_external_filter){
                        
                        $orders = array();
                        $filters = array();
                        $filters_from = array();
                        $filters_to = array();
                        $calendar_formats = array();
                        
                        $filters = Factory::getApplication()->getSession()->get('com_contentbuilder.filter.'.$this->_id, array());
                        $filters_from = Factory::getApplication()->getSession()->get('com_contentbuilder.calendar_filter_from.'.$this->_id, array());
                        $filters_to = Factory::getApplication()->getSession()->get('com_contentbuilder.calendar_filter_to.'.$this->_id, array());
                        $calendar_formats = Factory::getApplication()->getSession()->get('com_contentbuilder.calendar_formats.'.$this->_id, array());
                        $filter_keywords = Factory::getApplication()->getSession()->get('com_contentbuilder.filter_keywords.'.$this->_id, '');
                        $filter_cats = Factory::getApplication()->getSession()->get('com_contentbuilder.filter_article_categories.'.$this->_id, -1);

                        if($filter_keywords != ''){
                            $this->setState('formsd_filter', $filter_keywords);
                        }

                        if($filter_cats != -1){
                            $this->setState('article_category_filter', $filter_cats);
                        }
                        
                        foreach($calendar_formats As $col => $calendar_format){
                            if(isset($filters[$col])){
                                $filter_exploded = explode('/', $filters[$col]);
                                if(isset($filter_exploded[2])){
                                    $to_exploded = explode('to',$filter_exploded[2]);
                                    switch(count($to_exploded)){
                                        case 2:
                                            if($to_exploded[0] != ''){
                                                $filters[$col] = '@range/date/'.  contentbuilder_convert_date(trim($to_exploded[0]), $calendar_format) . ' to ' . contentbuilder_convert_date(trim($to_exploded[1]), $calendar_format);
                                            }
                                            else
                                            {
                                                $filters[$col] = '@range/date/to ' . contentbuilder_convert_date(trim($to_exploded[1]), $calendar_format);
                                            }
                                            break;
                                        case 1:
                                            $filters[$col] = '@range/date/'.  contentbuilder_convert_date(trim($to_exploded[0]), $calendar_format);
                                            break;
                                    }
                                    if(isset($to_exploded[0]) && isset($to_exploded[1]) && trim($to_exploded[0]) == '' && trim($to_exploded[1]) == ''){
                                       $filters[$col] = ''; 
                                    }
                                    if(isset($to_exploded[0]) && !isset($to_exploded[1]) && trim($to_exploded[0]) == ''){
                                       $filters[$col] = ''; 
                                    }
                                }
                            }
                        }
                        
                        $new_filters = array();
                        $i = 1;
                        foreach($filters As $filter_key => $filter){
                            if($filter != ''){
                                $orders[$filter_key] = $i;
                                $new_filters[$filter_key] = explode('|', $filter);
                            }
                            $i++;
                        }
                        
                        $this->_menu_filter = $new_filters;
                        $this->_menu_filter_order = $orders;
                    }
                    
                    $ordered_extra_title = '';
                    foreach($this->_menu_filter_order As $order_key => $order){
                        if(isset($this->_menu_filter[$order_key])){
                            // range test
                            $is_range = strstr(strtolower(implode(',', $this->_menu_filter[$order_key])), '@range') !== false;
                            $is_match = strstr(strtolower(implode(',', $this->_menu_filter[$order_key])), '@match') !== false;
                            if($is_range){
                               $ex = explode('/', implode(', ', $this->_menu_filter[$order_key]));
                               if(count($ex) == 3){
                                  $ex2 = explode('to', trim($ex[2]));
                                  $out = '';
                                  $val = $ex2[0];
                                  $val2 = '';
                                  if(isset($ex2[1])){
                                    $val2 = $ex2[1];
                                  }
                                  if(strtolower(trim($ex[1])) == 'date'){
                                      $val = HTMLHelper::_('date', $ex2[0], Text::_('DATE_FORMAT_LC3'));
                                      if(isset($ex2[1])){
                                        $val2 = HTMLHelper::_('date', $ex2[1], Text::_('DATE_FORMAT_LC3'));
                                      }
                                  }
                                  if(count($ex2) == 2){
                                      $out = (trim($ex2[0]) ? Text::_('COM_CONTENTBUILDER_FROM') . ' ' . trim($val) : '') . ' '.Text::_('COM_CONTENTBUILDER_TO').' ' . trim($val2);
                                  }else if(count($ex2) > 0){
                                      $out = Text::_('COM_CONTENTBUILDER_FROM2') . ' ' . trim($val);
                                  }
                                  if($out){
                                    $this->_menu_filter[$order_key] = $ex;
                                    $ordered_extra_title .= ' &raquo; ' . htmlentities($data->labels[$order_key], ENT_QUOTES, 'UTF-8'). ': ' . htmlentities($out, ENT_QUOTES, 'UTF-8');
                                  }
                               }
                            }
                            else if($is_match){
                                $ex = explode('/', implode(', ', $this->_menu_filter[$order_key]));
                                if(count($ex) == 2){
                                    $ex2 = explode(';', trim($ex[1]));
                                    $out = '';
                                    $size = count($ex2);
                                    $i = 0;
                                    foreach($ex2 As $val){
                                       if($i + 1 < $size){
                                           $out .= trim($val) . ' ' . Text::_('COM_CONTENTBUILDER_AND') . ' ';
                                       }else{
                                           $out .= trim($val);
                                       }
                                       $i++;
                                    }
                                    if($out){
                                        $this->_menu_filter[$order_key] = $ex;
                                        $ordered_extra_title .= ' &raquo; ' . htmlentities($data->labels[$order_key], ENT_QUOTES, 'UTF-8'). ': ' . htmlentities($out, ENT_QUOTES, 'UTF-8');
                                    }
                                }
                            }
                            else{
                                $ordered_extra_title .= ' &raquo; ' . htmlentities($data->labels[$order_key], ENT_QUOTES, 'UTF-8'). ': ' . htmlentities(implode(', ', $this->_menu_filter[$order_key]), ENT_QUOTES, 'UTF-8');
                            }
                        }
                    }
                    $order_types = array();
                    $ids = array();
                    foreach($data->labels As $reference_id => $label){
                        $ids[] = $this->_db->Quote($reference_id);
                    }
                    if(count($ids)){
                        $this->_db->setQuery("Select Distinct `id`,`label`, reference_id, `order_type` From #__contentbuilder_elements Where form_id = " . intval($this->_id) . " And reference_id In (" . implode(',', $ids) . ") And published = 1 Order By ordering");
                        $rows = $this->_db->loadAssocList();
                        $ids = array();
                        foreach($rows As $row){
                           // cleaned up, in desired order
                           $ids[] = $row['reference_id'];
                           $labels[] = $row['label'];
                           $order_types['col'.$row['reference_id']] = $row['order_type'];
                        }
                    }
                    $data->visible_cols = $ids;
                    $data->visible_labels = $labels;
                    $act_as_registration = array();
                    
                    if( $data->act_as_registration &&
                            $data->registration_username_field &&
                            $data->registration_name_field &&
                            $data->registration_email_field &&
                            $data->registration_email_repeat_field &&
                            $data->registration_password_field &&
                            $data->registration_password_repeat_field
                    ){
                        $act_as_registration[$data->registration_username_field] = 'registration_username_field';
                        $act_as_registration[$data->registration_name_field] = 'registration_name_field';
                        $act_as_registration[$data->registration_email_field] = 'registration_email_field';
                    }
                    $data->items = $data->form->getListRecords($ids, $this->getState('formsd_filter'), $searchable_elements, 0, 0, $this->getState('formsd_filter_order'), $order_types, $this->getState('formsd_filter_order_Dir'), 0, $data->published_only, $this->frontend && $data->own_only_fe ? Factory::getUser()->get('id', 0) : -1, $this->getState('formsd_filter_state'), $this->getState('formsd_filter_publish'), $data->initial_sort_order == -1 ? -1 : 'col'. $data->initial_sort_order, $data->initial_sort_order2 == -1 ? -1 : 'col'. $data->initial_sort_order2, $data->initial_sort_order3 == -1 ? -1 : 'col'. $data->initial_sort_order3, $this->_menu_filter, $this->frontend ? $data->show_all_languages_fe : true, $this->getState('formsd_filter_language'), $act_as_registration, $data, $this->getState('article_category_filter'));
                }

                return $data;
            }
        }
        return null;
    }
}

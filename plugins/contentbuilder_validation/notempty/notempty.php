<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
use Joomla\CMS\Language\Text;

jimport( 'joomla.plugin.plugin' );


class plgContentbuilder_validationNotempty extends JPlugin
{
        function __construct( &$subject, $params )
        {
            parent::__construct($subject, $params);
        }
        
        function onValidate($field, $fields, $record_id, $form, $value){
            
            $lang = JFactory::getLanguage();
            $lang->load('plg_contentbuilder_validation_notempty', JPATH_ADMINISTRATOR);

            $db = CBFactory::getDbo();
            $msg = '';
            
            if(!is_array($value)){
                
                if($field['type'] == 'upload'){
                   $msg = '';
                   $record_with_file_found = false;
                   $record = $form->getRecord($record_id, false, -1, true);
                   foreach($record As $item){
                       if($item->recElementId == $field['reference_id']){
                           if($item->recValue != ''){
                               $record_with_file_found = true;
                           }
                           break;
                       }
                   }
                   if(!$record_with_file_found && empty($value)){
                       $msg = trim($field['validation_message']) ? trim($field['validation_message']) : Text::_('COM_CONTENTBUILDER_VALIDATION_VALUE_EMPTY') . ': ' . $field['label'];
                   }
                }else{
                    $value = trim($value);
                    if(empty($value)){
                        $msg = trim($field['validation_message']) ? trim($field['validation_message']) : Text::_('COM_CONTENTBUILDER_VALIDATION_VALUE_EMPTY') . ': ' . $field['label'];
                    }
                }
            } else {
                $has = '';
                foreach($value As $item){
                    if($item != 'cbGroupMark'){
                        $has .= $item;
                    }
                }
                if(!$has){
                    $msg = trim($field['validation_message']) ? trim($field['validation_message']) : Text::_('COM_CONTENTBUILDER_VALIDATION_VALUE_EMPTY') . ': ' . $field['label'];
                }
            }
            return $msg;
        }
}

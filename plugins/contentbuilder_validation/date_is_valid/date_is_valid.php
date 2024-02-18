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

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder_helpers.php');

class plgContentbuilder_validationDate_is_valid extends JPlugin
{
        function __construct( &$subject, $params )
        {
            parent::__construct($subject, $params);
        }
        
        function onValidate($field, $fields, $record_id, $form, $value){
            
            $lang = JFactory::getLanguage();
            $lang->load('plg_contentbuilder_validation_date_is_valid', JPATH_ADMINISTRATOR);

            $options = $field['options'];
            
            $values = array();
            $values[0] = $value;
            
            if(is_array($value)){
               $values = array();
               foreach($value As $val){
                   $values[] = $val;
               }
            }
            
            foreach( $values As $val ){
                if( !contentbuilder_is_valid_date($val, isset($options->transfer_format) ? $options->transfer_format : 'YYYY-mm-dd') ){
                    return Text::_('COM_CONTENTBUILDER_VALIDATION_DATE_IS_VALID') . ': ' . $field['label'] . ($val ? ' ('.$val.')' : '');
                }
            }
           
            return '';
        } 
}

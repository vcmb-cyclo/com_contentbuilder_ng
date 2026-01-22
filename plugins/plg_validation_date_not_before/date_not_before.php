<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderHelper;

class plgContentbuilder_validationDate_not_before extends CMSPlugin
{
        function __construct( &$subject, $params )
        {
            parent::__construct($subject, $params);
        }
        
        function onValidate($field, $fields, $record_id, $form, $value){
            
            $lang = Factory::getApplication()->getLanguage();
            $lang->load('plg_contentbuilder_validation_date_not_before', JPATH_ADMINISTRATOR);

            foreach($fields As $other_field){
                if(isset($other_field['name']) && isset($other_field['value']) && isset($field['name']) && $field['name'].'_later' == $other_field['name']){
                 
                    if(is_array($value)){
                       return Text::_('COM_CONTENTBUILDER_VALIDATION_DATE_NOT_BEFORE_GROUPS');
                    }
                    
                    $other_value = $other_field['value'];
                    $other_value = ContentbuilderHelper::convertDate($other_value, $other_field['options']->transfer_format, 'YYYY-MM-DD');
                    $value = ContentbuilderHelper::convertDate($value, $field['options']->transfer_format, 'YYYY-MM-DD');
                    
                    if(is_array($other_value)){
                        return Text::_('COM_CONTENTBUILDER_VALIDATION_DATE_NOT_BEFORE_GROUPS');
                    }
                    
                    $value = preg_replace("/[^0-9]/",'',$value);
                    $other_value = preg_replace("/[^0-9]/",'',$other_value);
                    
                    if($other_value < $value){
                        return Text::_('COM_CONTENTBUILDER_VALIDATION_DATE_NOT_BEFORE') . ': ' . $other_field['label'] . ' (' . $other_field['value'] . ')';
                    }
                    
                    return '';
                }
            }
            
            return '';
        }
}

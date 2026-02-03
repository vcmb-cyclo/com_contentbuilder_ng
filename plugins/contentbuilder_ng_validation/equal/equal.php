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
use Joomla\Event\SubscriberInterface;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderHelper;

class plgContentbuilder_ng_validationEqual extends CMSPlugin implements SubscriberInterface
{
        public static function getSubscribedEvents(): array
        {
            return ['onValidate' => 'onValidate'];
        }
        
        function onValidate($field, $fields, $record_id, $form, $value){
            
            $lang = Factory::getApplication()->getLanguage();
            $lang->load('plg_contentbuilder_ng_validation_equal', JPATH_ADMINISTRATOR);

            foreach($fields As $other_field){
                if(isset($other_field['name']) && isset($other_field['value']) && isset($field['name']) && $field['name'].'_repeat' == $other_field['name']){
                    
                    $value = isset($field['orig_value']) ? $field['orig_value'] : $value;
                    
                    if(is_array($value)){
                       $val_group = '';
                       foreach($value As $val){
                           $val_group .= $val;
                       } 
                       $value = $val_group;
                    }
                    
                    $other_value = isset($other_field['orig_value']) ? $other_field['orig_value'] : $other_field['value'];
                    
                    if(is_array($other_value)){
                        $val_group = '';
                        foreach($value As $val){
                            $val_group .= $val;
                        } 
                        $other_value = $val_group;
                    }
                    
                    if( $value == $other_value ){
                        return '';
                    } else {
                        return Text::_('COM_CONTENTBUILDER_NG_VALIDATION_NOT_EQUAL') . ': ' . $field['label'] . ' / ' . $other_field['label'];
                    }
                }
            }
            
            return '';
        }
}

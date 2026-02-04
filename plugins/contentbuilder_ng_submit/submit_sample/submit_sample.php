<?php
/**
 * @version     6.0
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
*/

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class plgContentbuilder_ng_submitSubmit_sample extends CMSPlugin implements SubscriberInterface
{
        public static function getSubscribedEvents(): array
        {
            return [
                'onBeforeSubmit' => 'onBeforeSubmit',
                'onAfterSubmit' => 'onAfterSubmit',
            ];
        }
        
        /**
         *
         * @param mixed $record_id The record id, type as required by the type of storage
         * @param stdClass $form the form object
         * @param array $values indices are the element IDs as defined by the type of storage. Group values will be stored as array
         */
        function onBeforeSubmit($record_id, $form, array $values){
            
        }
        
        /**
         *
         * @param mixed $record_id The record id, type as required by the type of storage
         * @param int $article_id the article id, 0 if there isn't any
         * @param stdClass $form the form object
         * @param array $values indices are the element IDs as defined by the type of storage. Group values will be stored as array
         */
        function onAfterSubmit($record_id, $article_id, $form, array $values){
            
        }
}

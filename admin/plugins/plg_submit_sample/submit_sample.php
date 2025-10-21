<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Plugin\CMSPlugin;

class plgContentbuilder_submitSubmit_sample extends CMSPlugin
{
        function __construct( &$subject, $params )
        {
            parent::__construct($subject, $params);
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

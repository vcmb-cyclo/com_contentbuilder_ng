<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgContentbuilder_listactionUntrash extends CMSPlugin
{
    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
    }

    /**
     * @param int $form_id use it to find the record for the appropriate view
     * @param array $record_ids an array of record_id. Please note that the record_ids may be _non_numeric_
     * @return string error
     */
    function onBeforeAction($form_id, $record_ids)
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

        $lang = Factory::getApplication()->getLanguage();
        $lang->load('plg_contentbuilder_listaction_untrash', JPATH_ADMINISTRATOR);

        foreach ($record_ids as $record_id) {

            $db->setQuery("Update #__content As content, #__contentbuilder_records As record, #__contentbuilder_articles As article Set content.state = record.published Where article.record_id = record.record_id And article.form_id = " . intval($form_id) . " And article.record_id = " . $db->Quote($record_id) . " And content.id = article.article_id");
            $db->execute();
        }

        Factory::getApplication()->enqueueMessage(Text::_('COM_CONTENTBUILDER_UNTRASH_SUCCESSFULL'));

        return ''; // no error
    }

    /**
     *
     * @param int $form_id use it to find the record for the appropriate view
     * @param array $record_ids an array of record_id. Please note that the record_ids may be _non_numeric_
     * @param type $previous_errors error messages thrown by onBeforeAction
     * @return type 
     */
    function onAfterAction($form_id, $record_ids, $previous_errors)
    {
        return ''; // no error
    }

    /**
     * This event will be triggered on article creation and update.
     * 
     * It gives you the chance to force the article to stay into previously set states
     * 
     * @param int $form_id
     * @param mixed $record_id
     * @param int $article_id 
     * @return string message
     */
    function onAfterArticleCreation($form_id, $record_id, $article_id)
    {

        return '';
    }
}

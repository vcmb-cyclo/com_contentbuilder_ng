<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// no direct access
defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

class plgContentbuilder_listactionTrash extends CMSPlugin
{
    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  5.0.0
     */
    protected $app;

    /**
     * Database object.
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  5.0.0
     */
    protected $db;

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
        $lang = $this->app->getLanguage();
        $lang->load('plg_contentbuilder_listaction_trash', JPATH_ADMINISTRATOR);

        foreach ($record_ids as $record_id) {
            $this->db->setQuery("Update #__content As content, #__contentbuilder_articles As article Set content.state = -2 Where article.form_id = " . intval($form_id) . " And article.record_id = " . $this->db->Quote($record_id) . " And content.id = article.article_id");
            $this->db->execute();
        }

        $this->app->enqueueMessage(Text::_('COM_CONTENTBUILDER_TRASH_SUCCESSFULL'));
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
     * In this case we delete the newly created article if there is a trashed state assigned to this record.
     * 
     * @param int $form_id
     * @param mixed $record_id
     * @param int $article_id 
     * @return string message
     */
    function onAfterArticleCreation($form_id, $record_id, $article_id)
    {
        $this->db->setQuery("Select action From #__contentbuilder_list_records As lr, #__contentbuilder_list_states As ls Where lr.state_id = ls.id And lr.form_id = ls.form_id And lr.form_id = " . intval($form_id) . " And lr.record_id = " . $this->db->Quote($record_id));
        $action = $this->db->loadResult();

        if ($action == 'trash') {
            $this->db->setQuery("Delete From #__content Where id = " . intval($article_id));
            $this->db->execute();
        }

        return '';
    }
}

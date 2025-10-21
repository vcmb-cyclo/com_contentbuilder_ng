<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;


require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderModelAjax extends CBModel
{

    private $frontend = false;
    private $_subject = '';

    function __construct($config)
    {
        parent::__construct($config);

        $this->frontend = Factory::getApplication()->isClient('site');

        $mainframe = Factory::getApplication();
        $option = 'com_contentbuilder';

        $this->_id = CBRequest::getInt('id', 0);
        $this->_subject = CBRequest::getCmd('subject', '');

    }

    function getData()
    {

        switch ($this->_subject) {

            case 'get_unique_values':

                if ($this->frontend) {
                    if (!contentbuilder::authorizeFe('listaccess')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED')));
                    }
                } else {
                    if (!contentbuilder::authorize('listaccess')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED')));
                    }
                }

                $this->_db->setQuery("Select `type`, `reference_id`, `rating_slots` From #__contentbuilder_forms Where id = " . $this->_id);
                $result = $this->_db->loadAssoc();

                $form = contentbuilder::getForm($result['type'], $result['reference_id']);

                if (!$form || !$form->exists) {
                    return json_encode(array('code' => 2, 'msg' => Text::_('COM_CONTENTBUILDER_FORM_ERROR')));
                }

                $values = $form->getUniqueValues(CBRequest::getCmd('field_reference_id', ''), CBRequest::getCmd('where_field', ''), CBRequest::getVar('where', ''));

                return json_encode(array('code' => 0, 'field_reference_id' => CBRequest::getCmd('field_reference_id', ''), 'msg' => $values));


                break;

            case 'rating':

                if ($this->frontend) {
                    if (!contentbuilder::authorizeFe('rating')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_RATING_NOT_ALLOWED')));
                    }
                } else {
                    if (!contentbuilder::authorize('rating')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_RATING_NOT_ALLOWED')));
                    }
                }

                $this->_db->setQuery("Select `type`, `reference_id`, `rating_slots` From #__contentbuilder_forms Where id = " . $this->_id);
                $result = $this->_db->loadAssoc();

                $form = contentbuilder::getForm($result['type'], $result['reference_id']);

                if (!$form || !$form->exists) {
                    return json_encode(array('code' => 2, 'msg' => Text::_('COM_CONTENTBUILDER_FORM_ERROR')));
                }

                $rating = 0;

                switch ($result['rating_slots']) {
                    case 1:
                        $rating = 1;
                        //$rating = 5;
                        break;
                    case 2:
                        $rating = CBRequest::getInt('rate', 5);
                        if ($rating > 5)
                            $rating = 5;
                        if ($rating < 4)
                            $rating = 0;

                        //if($rating == 2) $rating = 5;
                        break;
                    case 3:
                        $rating = CBRequest::getInt('rate', 3);
                        if ($rating > 3)
                            $rating = 3;
                        if ($rating < 1)
                            $rating = 1;

                        //if($rating == 2) $rating = 3;
                        //if($rating == 3) $rating = 5;
                        break;
                    case 4:
                        $rating = CBRequest::getInt('rate', 4);
                        if ($rating > 4)
                            $rating = 4;
                        if ($rating < 1)
                            $rating = 1;

                        //if($rating == 3) $rating = 4;
                        //if($rating == 4) $rating = 5;
                        break;
                    case 5:
                        $rating = CBRequest::getInt('rate', 5);
                        if ($rating > 5)
                            $rating = 5;
                        if ($rating < 1)
                            $rating = 1;
                        break;
                }

                if ($result['rating_slots'] == 2 || $rating) {

                    $_now = Factory::getDate();

                    // clear rating cache
                    $___now = $_now->toSql();

                    $this->_db->setQuery("Delete From #__contentbuilder_rating_cache Where Datediff('" . $___now . "', `date`) >= 1");
                    $this->_db->execute();

                    // test if already voted
                    $this->_db->setQuery("Select `form_id` From #__contentbuilder_rating_cache Where `record_id` = " . $this->_db->Quote(CBRequest::getCmd('record_id', '')) . " And `form_id` = " . $this->_id . " And `ip` = " . $this->_db->Quote($_SERVER['REMOTE_ADDR']));
                    $cached = $this->_db->loadResult();
                    $rated = Factory::getApplication()->getSession()->get('rated' . $this->_id . CBRequest::getCmd('record_id', ''), false, 'com_contentbuilder.rating');

                    if ($rated || $cached) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_RATED_ALREADY')));
                    } else {
                        Factory::getApplication()->getSession()->set('rated' . $this->_id . CBRequest::getCmd('record_id', ''), true, 'com_contentbuilder.rating');
                    }

                    // adding vote
                    $this->_db->setQuery("Update #__contentbuilder_records Set rating_count = rating_count + 1, rating_sum = rating_sum + " . $rating . ", lastip = " . $this->_db->Quote($_SERVER['REMOTE_ADDR']) . " Where `type` = " . $this->_db->Quote($result['type']) . " And `reference_id` = " . $this->_db->Quote($result['reference_id']) . " And `record_id` = " . $this->_db->Quote(CBRequest::getCmd('record_id', '')));
                    $this->_db->execute();

                    // adding vote to cache
                    $___now = $_now->toSql();
                    $this->_db->setQuery("Insert Into #__contentbuilder_rating_cache (`record_id`,`form_id`,`ip`,`date`) Values (" . $this->_db->Quote(CBRequest::getCmd('record_id', '')) . ", " . $this->_id . "," . $this->_db->Quote($_SERVER['REMOTE_ADDR']) . ",'" . $___now . "')");
                    $this->_db->execute();

                    // updating article's votes if there is an article bound to the record & view
                    $this->_db->setQuery("Select a.article_id From #__contentbuilder_articles As a, #__content As c Where c.id = a.article_id And (c.state = 1 Or c.state = 0) And a.form_id = " . $this->_id . " And a.record_id = " . $this->_db->Quote(CBRequest::getCmd('record_id', '')));
                    $article_id = $this->_db->loadResult();

                    if ($article_id) {

                        $this->_db->setQuery("Select content_id From #__content_rating Where content_id = " . $article_id);
                        $exists = $this->_db->loadResult();

                        if ($exists) {
                            $this->_db->setQuery("
                                Update 
                                    #__content_rating As cr, 
                                    #__contentbuilder_records As cbr, 
                                    #__contentbuilder_articles As cba
                                Set
                                    cr.rating_count = cbr.rating_count,
                                    cr.rating_sum = cbr.rating_sum,
                                    cr.lastip = cbr.lastip
                                Where
                                    cbr.record_id = " . $this->_db->Quote(CBRequest::getCmd('record_id', '')) . "
                                And
                                    cbr.record_id = cba.record_id
                                And
                                    cbr.reference_id = " . $this->_db->Quote($result['reference_id']) . "
                                And
                                    cbr.`type` = " . $this->_db->Quote($result['type']) . " 
                                And 
                                    cba.form_id = " . $this->_id . "
                                And
                                    cr.content_id = cba.article_id
                            ");
                            $this->_db->execute();
                        } else {
                            $this->_db->setQuery("
                                Insert Into 
                                    #__content_rating 
                                (
                                    content_id,
                                    rating_sum,
                                    rating_count,
                                    lastip
                                ) 
                                Values
                                (
                                    $article_id,
                                    $rating,
                                    1,
                                    " . $this->_db->Quote($_SERVER['REMOTE_ADDR']) . "
                                )");
                            $this->_db->execute();
                        }
                    }
                }

                return json_encode(array('code' => 0, 'msg' => Text::_('COM_CONTENTBUILDER_THANK_YOU_FOR_RATING')));
                break;
        }
        return null;
    }
}

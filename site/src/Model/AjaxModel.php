<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Site\Model;

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use CB\Component\Contentbuilder\Administrator\CBRequest;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

class AjaxModel extends BaseDatabaseModel
{

    private $frontend = false;
    private $_subject = '';

    public function __construct(
        $config,
        MVCFactoryInterface $factory
    ) {
        // IMPORTANT : on transmet factory/app/input à ListModel
        parent::__construct($config, $factory);

        $this->frontend = Factory::getApplication()->isClient('site');

        $app = Factory::getApplication();
        $option = 'com_contentbuilder';

        $this->_id = CBRequest::getInt('id', 0);
        $this->_subject = CBRequest::getCmd('subject', '');

    }

    function getData()
    {
        switch ($this->_subject) {
            case 'get_unique_values':
                if ($this->frontend) {
                    if (!ContentbuilderLegacyHelper::authorizeFe('listaccess')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED')));
                    }
                } else {
                    if (!ContentbuilderLegacyHelper::authorize('listaccess')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED')));
                    }
                }

                $this->getDatabase()->setQuery("Select `type`, `reference_id`, `rating_slots` From #__contentbuilder_forms Where id = " . $this->_id);
                $result = $this->getDatabase()->loadAssoc();

                $form = ContentbuilderLegacyHelper::getForm($result['type'], $result['reference_id']);

                if (!$form || !$form->exists) {
                    return json_encode(array('code' => 2, 'msg' => Text::_('COM_CONTENTBUILDER_FORM_ERROR')));
                }

                $values = $form->getUniqueValues(CBRequest::getCmd('field_reference_id', ''), CBRequest::getCmd('where_field', ''), CBRequest::getVar('where', ''));

                return json_encode(array('code' => 0, 'field_reference_id' => CBRequest::getCmd('field_reference_id', ''), 'msg' => $values));


                break;

            case 'rating':

                if ($this->frontend) {
                    if (!ContentbuilderLegacyHelper::authorizeFe('rating')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_RATING_NOT_ALLOWED')));
                    }
                } else {
                    if (!ContentbuilderLegacyHelper::authorize('rating')) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_RATING_NOT_ALLOWED')));
                    }
                }

                $this->getDatabase()->setQuery("Select `type`, `reference_id`, `rating_slots` From #__contentbuilder_forms Where id = " . $this->_id);
                $result = $this->getDatabase()->loadAssoc();

                $form = ContentbuilderLegacyHelper::getForm($result['type'], $result['reference_id']);

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

                    $this->getDatabase()->setQuery("Delete From #__contentbuilder_rating_cache Where Datediff('" . $___now . "', `date`) >= 1");
                    $this->getDatabase()->execute();

                    // test if already voted
                    $this->getDatabase()->setQuery("Select `form_id` From #__contentbuilder_rating_cache Where `record_id` = " . $this->getDatabase()->Quote(CBRequest::getCmd('record_id', '')) . " And `form_id` = " . $this->_id . " And `ip` = " . $this->getDatabase()->Quote($_SERVER['REMOTE_ADDR']));
                    $cached = $this->getDatabase()->loadResult();
                    $rated = Factory::getApplication()->getSession()->get('rated' . $this->_id . CBRequest::getCmd('record_id', ''), false, 'com_contentbuilder.rating');

                    if ($rated || $cached) {
                        return json_encode(array('code' => 1, 'msg' => Text::_('COM_CONTENTBUILDER_RATED_ALREADY')));
                    } else {
                        Factory::getApplication()->getSession()->set('rated' . $this->_id . CBRequest::getCmd('record_id', ''), true, 'com_contentbuilder.rating');
                    }

                    // adding vote
                    $this->getDatabase()->setQuery("Update #__contentbuilder_records Set rating_count = rating_count + 1, rating_sum = rating_sum + " . $rating . ", lastip = " . $this->getDatabase()->Quote($_SERVER['REMOTE_ADDR']) . " Where `type` = " . $this->getDatabase()->Quote($result['type']) . " And `reference_id` = " . $this->getDatabase()->Quote($result['reference_id']) . " And `record_id` = " . $this->getDatabase()->Quote(CBRequest::getCmd('record_id', '')));
                    $this->getDatabase()->execute();

                    // adding vote to cache
                    $___now = $_now->toSql();
                    $this->getDatabase()->setQuery("Insert Into #__contentbuilder_rating_cache (`record_id`,`form_id`,`ip`,`date`) Values (" . $this->getDatabase()->Quote(CBRequest::getCmd('record_id', '')) . ", " . $this->_id . "," . $this->getDatabase()->Quote($_SERVER['REMOTE_ADDR']) . ",'" . $___now . "')");
                    $this->getDatabase()->execute();

                    // updating article's votes if there is an article bound to the record & view
                    $this->getDatabase()->setQuery("Select a.article_id From #__contentbuilder_articles As a, #__content As c Where c.id = a.article_id And (c.state = 1 Or c.state = 0) And a.form_id = " . $this->_id . " And a.record_id = " . $this->getDatabase()->Quote(CBRequest::getCmd('record_id', '')));
                    $article_id = $this->getDatabase()->loadResult();

                    if ($article_id) {

                        $this->getDatabase()->setQuery("Select content_id From #__content_rating Where content_id = " . $article_id);
                        $exists = $this->getDatabase()->loadResult();

                        if ($exists) {
                            $this->getDatabase()->setQuery("
                                Update 
                                    #__content_rating As cr, 
                                    #__contentbuilder_records As cbr, 
                                    #__contentbuilder_articles As cba
                                Set
                                    cr.rating_count = cbr.rating_count,
                                    cr.rating_sum = cbr.rating_sum,
                                    cr.lastip = cbr.lastip
                                Where
                                    cbr.record_id = " . $this->getDatabase()->Quote(CBRequest::getCmd('record_id', '')) . "
                                And
                                    cbr.record_id = cba.record_id
                                And
                                    cbr.reference_id = " . $this->getDatabase()->Quote($result['reference_id']) . "
                                And
                                    cbr.`type` = " . $this->getDatabase()->Quote($result['type']) . " 
                                And 
                                    cba.form_id = " . $this->_id . "
                                And
                                    cr.content_id = cba.article_id
                            ");
                            $this->getDatabase()->execute();
                        } else {
                            $this->getDatabase()->setQuery("
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
                                    " . $this->getDatabase()->Quote($_SERVER['REMOTE_ADDR']) . "
                                )");
                            $this->getDatabase()->execute();
                        }
                    }
                }

                return json_encode(array('code' => 0, 'msg' => Text::_('COM_CONTENTBUILDER_THANK_YOU_FOR_RATING')));
                break;
        }
        return null;
    }
}

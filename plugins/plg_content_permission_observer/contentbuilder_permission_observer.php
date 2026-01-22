<?php
/**
 * @version 2.0
 * @package ContentBuilder Image Scale
 * @copyright (C) 2011 by Markus Bopp
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license Released under the terms of the GNU General Public License
 **/

/** ensure this file is being included by a parent file */
\defined('_JEXEC') or die ('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use CB\Component\Contentbuilder\Administrator\Helper\ContentbuilderLegacyHelper;
use CB\Component\Contentbuilder\Administrator\CBRequest;

class plgContentContentbuilder_permission_observer extends CMSPlugin
{

    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
    }

    /**
     * Joomla 1.5 compatibility
     */
    function onPrepareContent(&$article, &$params, $limitstart = 0)
    {
        $this->onContentPrepare('', $article, $params, $limitstart);
    }

    function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        if (!file_exists(JPATH_SITE .'/administrator/components/com_contentbuilder/src/contentbuilder.php')) {
            return true;
        }

        if (isset ($article->id) && $article->id) {

            $frontend = true;
            if (Factory::getApplication()->isClient('administrator')) {
                $frontend = false;
            }

            $db = Factory::getContainer()->get(DatabaseInterface::class);
            $db->setQuery("Select form.`reference_id`,article.`record_id`,article.`form_id`,form.`type`,form.`published_only`,form.`own_only`,form.`own_only_fe` From #__contentbuilder_articles As article, #__contentbuilder_forms As form Where form.`published` = 1 And form.id = article.`form_id` And article.`article_id` = " . $db->quote($article->id));
            $data = $db->loadAssoc();

            require_once (JPATH_SITE .'/administrator/components/com_contentbuilder/src/contentbuilder.php');
            $form = ContentbuilderLegacyHelper::getForm($data['type'], $data['reference_id']);

            if (!$form || !$form->exists) {
                return true;
            }

            if ($form && !(CBRequest::getVar('option', '') == 'com_contentbuilder' && CBRequest::getVar('controller', '') == 'edit')) {

                Factory::getApplication()->getLanguage()->load('com_contentbuilder');
                ContentbuilderLegacyHelper::setPermissions($data['form_id'], $data['record_id'], $frontend ? '_fe' : '');

                if (CBRequest::getCmd('view') == 'article') {
                    ContentbuilderLegacyHelper::checkPermissions('view', Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED'), $frontend ? '_fe' : '');
                } else {
                    if ($frontend) {
                        if (!ContentbuilderLegacyHelper::authorizeFe('view')) {
                            $article->text = Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED');
                        }
                    } else {
                        if (!ContentbuilderLegacyHelper::authorize('view')) {
                            $article->text = Text::_('COM_CONTENTBUILDER_PERMISSIONS_VIEW_NOT_ALLOWED');
                        }
                    }
                }
            }
        }
        return true;
    }
}
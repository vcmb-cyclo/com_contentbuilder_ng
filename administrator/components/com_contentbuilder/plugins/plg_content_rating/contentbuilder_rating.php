<?php
/**
 * @version     1.0
 * @package     ContentBuilder Image Scale
 * @copyright   (C) 2011 by Markus Bopp
 * @copyright   (C) 2024 by XDA+GIL
 * @license     Released under the terms of the GNU General Public License
 **/


/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\File;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Plugin\CMSPlugin;

if (!function_exists('cb_b64enc')) {

    function cb_b64enc($str)
    {
        $base = 'base';
        $sixty_four = '64_encode';
        return call_user_func($base . $sixty_four, $str);
    }

}

if (!function_exists('cb_b64dec')) {
    function cb_b64dec($str)
    {
        $base = 'base';
        $sixty_four = '64_decode';
        return call_user_func($base . $sixty_four, $str);
    }
}

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');

class plgContentContentbuilder_rating extends CMSPlugin
{

    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
    }

    /**
     * Joomla 1.5 compatibility
     */
    function onPrepareContent(&$article, &$params, $limitstart = 0, $is_list = false, $form = null, $item = null)
    {
        $this->onContentPrepare('', $article, $params, $limitstart, $is_list, $form, $item);
    }

    function onContentPrepare($context, &$article, &$params, $limitstart = 0, $is_list = false, $form = null, $item = null)
    {

        $protect = false;

        $plugin = PluginHelper::getPlugin('content', 'contentbuilder_rating');
        $pluginParams = CBCompat::getParams($plugin->params);

        if (!file_exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php')) {
            return true;
        }

        require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');

        $lang = Factory::getLanguage();
        $lang->load('plg_content_contentbuilder_rating', JPATH_ADMINISTRATOR);

        /*
         * As of Joomla! 1.6 there is just the text passed if the article data is not passed in article context.
         * (for instance with categories).
         * But we need the article id, so we use the article id flag from content generation.
         */
        if (is_object($article) && !isset($article->id) && !isset($article->cbrecord) && isset($article->text) && $article->text) {
            preg_match_all("/<!--\(cbArticleId:(\d{1,})\)-->/si", $article->text, $matched_id);
            if (isset($matched_id[1]) && isset($matched_id[1][0])) {
                $article->id = intval($matched_id[1][0]);
            }
        }

        // if this content plugin has been called from within list context
        if ($is_list) {

            if (!trim($article->text)) {
                return true;
            }

            $article->cbrecord = $form;
            $article->cbrecord->items = array();
            $article->cbrecord->items[0] = $item;
            $article->cbrecord->record_id = $item->colRecord;
        }

        if (!is_dir(JPATH_SITE . DS . 'media' . DS . 'contentbuilder')) {
            Folder::create(JPATH_SITE . DS . 'media' . DS . 'contentbuilder');
        }

        if (!file_exists(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'index.html'))
            File::write(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'index.html', $def = '');

        if (!is_dir(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'plugins')) {
            Folder::create(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'plugins');
        }

        if (!file_exists(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'plugins' . DS . 'index.html'))
            File::write(JPATH_SITE . DS . 'media' . DS . 'contentbuilder' . DS . 'plugins' . DS . 'index.html', $def = '');

        if (isset($article->id) || isset($article->cbrecord)) {

            $db = Factory::getContainer()->get(DatabaseInterface::class);

            $matches = array();

            preg_match_all("/\{CBRating([^}]*)\}/i", $article->text, $matches);

            if (isset($matches[0]) && is_array($matches[0]) && isset($matches[1]) && is_array($matches[1])) {

                $form_id = 0;
                $record_id = 0;

                $frontend = true;
                if (Factory::getApplication()->isClient('administrator')) {
                    $frontend = false;
                }

                if (isset($article->id) && $article->id && !isset($article->cbrecord)) {

                    // try to obtain the record id if if this is just an article
                    $db->setQuery("Select form.rating_slots,form.`title_field`,form.`protect_upload_directory`,form.`reference_id`,article.`record_id`,article.`form_id`,form.`type`,form.`published_only`,form.`own_only`,form.`own_only_fe` From #__contentbuilder_articles As article, #__contentbuilder_forms As form Where form.`published` = 1 And form.id = article.`form_id` And article.`article_id` = " . $db->quote($article->id));
                    $data = $db->loadAssoc();

                    require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php');
                    $form = contentbuilder::getForm($data['type'], $data['reference_id']);
                    if (!$form || !$form->exists) {
                        return true;
                    }

                    if ($form) {

                        $form_id = $data['form_id'];
                        $record_id = $data['record_id'];
                        $rating_slots = $data['rating_slots'];
                    }

                } else if (isset($article->cbrecord) && isset($article->cbrecord->id) && $article->cbrecord->id) {

                    $form = $article->cbrecord->form;
                    $form_id = $article->cbrecord->id;
                    $record_id = $article->cbrecord->record_id;
                    $rating_slots = $article->cbrecord->rating_slots;

                }

                $rating = 0;
                $rating_count = 0;
                $rating_sum = 0;

                if (!is_object($form)) {
                    return true;
                }

                $record = $form->getRecord($record_id, false, -1, true);

                if (count($record)) {
                    $rating = $record[0]->recRating;
                    $rating_count = $record[0]->recRatingCount;
                    $rating_sum = $record[0]->recRatingSum;
                }

                $rating_allowed = true;

                if (!$is_list) {

                    contentbuilder::setPermissions($form_id, $record_id, $frontend ? '_fe' : '');

                    if ($frontend) {
                        if (!contentbuilder::authorizeFe('rating')) {
                            $rating_allowed = false;
                        }
                    } else {
                        if (!contentbuilder::authorize('rating')) {
                            $rating_allowed = false;
                        }
                    }
                }

                $i = 0;
                foreach ($matches[1] as $match) {

                    $options = explode(';', trim($match));
                    foreach ($options as $option) {
                        $keyval = explode(':', trim($option), 2);
                        if (count($keyval) == 2) {

                            $value = trim($keyval[1]);
                            switch (strtolower(trim($keyval[0]))) {
                                default:
                            }
                        }
                    }

                    $out = contentbuilder::getRating($form_id, $record_id, $rating, $rating_slots, CBRequest::getCmd('lang', ''), $rating_allowed, $rating_count, $rating_sum);

                    $article->text = str_replace($matches[0][$i], $out, $article->text);

                    $i++;
                }
            }
        }

        return true;
    }
}
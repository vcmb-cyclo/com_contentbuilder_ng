<?php
/**
 * @version     1.0
 * @package     ContentBuilder Image Scale
 * @copyright   (C) 2011 by Markus Bopp
 * @copyright   (C) 2024 by XDA+GIL
 * @license     Released under the terms of the GNU General Public License
 **/

/** ensure this file is being included by a parent file */

defined('_JEXEC') or die ('Direct Access to this location is not allowed.');

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

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

class plgContentContentbuilder_verify extends CMSPlugin
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

    function getValueByLanguage($value)
    {

        $firstval = '';
        $parts = explode('|', $value);

        foreach ($parts as $part) {
            $keyval = explode('___', $part, 2);
            if (count($keyval) == 2) {
                if (!$firstval) {
                    $firstval = trim($keyval[1]);
                }
                $lang = strtolower(trim($keyval[0]));
                $val = trim($keyval[1]);
                if ($lang && $lang == strtolower(CBRequest::getVar('lang', ''))) {
                    return $val;
                }
            }
        }

        if ($firstval) {
            return $firstval;
        }

        return $value;
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

        if (!$article || !isset ($article->text) || !file_exists(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder.php')) {
            return true;
        }

        $matches = array();
        preg_match_all("/\{CBVerify([^}]*)\}/i", $article->text, $matches);

        if (isset ($matches[0]) && is_array($matches[0]) && isset ($matches[1]) && is_array($matches[1])) {

            $i = 0;
            foreach ($matches[1] as $match) {

                $return_admin = '';
                $return_site = '';
                $plugin = '';
                $verification_name = '';
                $verification_msg = '';
                $image = 'none';
                $image_width = 0;
                $image_height = 0;
                $desc = 'Verify';
                $verify_view = 0;
                $verify_levels = '';
                $require_view = 0;
                $plugin_options = array();

                $options = explode(';', trim($match));
                foreach ($options as $option) {
                    $keyval = explode(':', trim($option), 2);
                    if (count($keyval) == 2) {

                        $value = trim($keyval[1]);
                        switch (strtolower(trim($keyval[0]))) {
                            case 'plugin':
                                $plugin = $value;
                                break;
                            case 'verification-name':
                                $verification_name = $this->getValueByLanguage($value); // lang
                                break;
                            case 'verification-msg':
                                $verification_msg = $this->getValueByLanguage($value); // lang
                                break;
                            case 'image':
                                $image = $this->getValueByLanguage($value); // lang
                                break;
                            case 'image-width':
                                $image_width = $this->getValueByLanguage($value); // lang
                                break;
                            case 'image-height':
                                $image_height = $this->getValueByLanguage($value); // lang
                                break;
                            case 'desc':
                                $desc = $this->getValueByLanguage($value); // lang
                                break;
                            case 'verify-view':
                                $verify_view = $this->getValueByLanguage($value);
                                break;
                            case 'verify-levels':
                                $vl = explode(',', $this->getValueByLanguage($value));
                                $verify_levelss = array();
                                foreach ($vl as $l) {
                                    if (in_array(strtolower(trim($l)), array('new', 'edit', 'view'))) {
                                        $verify_levelss[] = strtolower(trim($l));
                                    }
                                }
                                $verify_levels = implode(',', $verify_levelss);
                                break;
                            case 'require-view':
                                $require_view = $this->getValueByLanguage($value);
                                break;
                            case 'return-admin':
                                $return_admin = $this->getValueByLanguage($value);
                                break;
                            case 'return-site':
                                $return_site = $this->getValueByLanguage($value);
                                break;
                            default:
                                $plugin_options[strtolower(trim($keyval[0]))] = $this->getValueByLanguage($value);
                        }
                    }
                }

                if ($plugin && $verification_name && $verify_view) {

                    $plugin_settings = 'return-site=' . ($return_site ? cb_b64enc($return_site) : '') . '&return-admin=' . ($return_admin ? cb_b64enc($return_admin) : '') . '&client=' . ($this->app->isClient('site') ? 0 : 1) . '&plugin=' . $plugin . '&verification_msg=' . urlencode($verification_msg) . '&verification_name=' . urlencode($verification_name) . '&verify_view=' . $verify_view . '&verify_levels=' . $verify_levels . '&require_view=' . $require_view . '&plugin_options=' . cb_b64enc($this->buildStr($plugin_options));

                    $this->app->getSession()->clear($plugin . $verification_name, 'com_contentbuilder.verify.' . $plugin . $verification_name);
                    $this->app->getSession()->set($plugin . $verification_name, $plugin_settings, 'com_contentbuilder.verify.' . $plugin . $verification_name);

                    $link = Uri::root(true) . '/index.php?option=com_contentbuilder&controller=verify&plugin=' . urlencode($plugin) . '&verification_name=' . urlencode($verification_name) . '&format=raw';
                    PluginHelper::importPlugin('contentbuilder_verify', $plugin);
                    $eventResult = $this->app->getDispatcher()->dispatch('onViewport', new Joomla\Event\Event('onVerify', array($link, $plugin_settings)));
                    $results = $eventResult->getArgument('result') ?: [];
                    $viewport_result = implode('', $results);

                    if ($viewport_result) {
                        $article->text = str_replace($matches[0][$i], $viewport_result, $article->text);
                    } else {
                        $article->text = str_replace($matches[0][$i], '<a class="cb_verification_link" href="' . $link . '">' . ($image && $image != 'none' ? '<img class="cb_verification_image" border="0" ' . ($image_width ? 'width="' . $image_width . '" ' : '') . '' . ($image_height ? 'height="' . $image_height . '" ' : '') . 'src="' . $image . '" alt="' . $desc . '" title="' . $desc . '"/>' : $desc) . '</a>', $article->text);
                    }

                } else {
                    $article->text = str_replace($matches[0][$i], '<span style="color:red;">WARNING: Verify plugin requires the options "plugin", "verification-name" and "verify-view". Please update your content template.</span>', $article->text);
                }

                $i++;
            }
        }

        return true;
    }

    private function buildStr($query_array)
    {
        $query_string = array();
        foreach ($query_array as $k => $v) {
            $query_string[] = $k . '=' . urlencode($v);
        }
        return join('&', $query_string);
    }
}
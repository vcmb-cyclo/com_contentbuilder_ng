<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.log
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Joomla! System Logging Plugin.
 *
 * @since  1.5
 */
class PlgSystemSysbreezingforms extends JPlugin
{
    public function onBeforeRender()
    {

        if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_breezingforms/breezingforms.php')) {
            return;
        }

        $app = FactorytApplication();

        try {
                $bNotValid = FactorytApplication()->isClient('administrator') &&
                (
                    (
                        $app->input->getString('option') == 'com_breezingforms' &&
                        $app->input->getString('act', '') != '' &&
                        $app->input->getString('act', '') != 'configuration'
                    )
                    ||
                    $app->input->getString('option') == 'com_installer' &&
                    $app->input->getString('view', '') == 'update'
                    );

//            if ($bNotValid) {
                if (false) {
                $message = 'Please enter your update key in the BreezingForms configuration.<br />Without this key you won\'t be able to receive future upates.<br />You can get your personal update key at Crosstec.org in the My Account => My Downloads section after login.<br />If your membership is expired, you can renew it by <a style="font-weight: bold; text-decoration: underline;" target="_blank" href="https://crosstec.org/en/downloads/joomla-forms.html">purchasing a membership</a>.';

                $db = Factory::getContainer()->get(DatabaseInterface::class);
                $db->setQuery("Select extra_query From #__update_sites Where `name` = 'BreezingForms Pro' And `type` = 'extension'");
                $query = $db->loadResult();

                $exp = explode('=', $query);
                if (isset($exp[1])) {
                    $exp = explode('-', $exp[1]);

                    if (is_numeric($exp[0])) {

                        if ($exp[0] > 0) { // 0 = unlimited

                            $time = strtotime(JHTML::_('date', 'now', 'Y-m-d H:i:s', false));

                            if ($time > $exp[0]) {
                                $message = 'Your membership for the update key seems to be expired, you can renew it by <a style="font-weight: bold; text-decoration: underline;" target="_blank" href="https://crosstec.org/en/downloads/joomla-forms.html">purchasing a membership</a>.<br/>After purchase, please get the update key from My Account => My Downloads at Crosstec.org and enter it in the BreezingForms configuration.';
                                $query = '';
                            }
                        }

                    } else {

                        $query = '';
                    }

                } else {

                    $query = '';
                }

                if (trim($query) == '') {

                    $breaks2 = '';
                    $breaks = '';
                    if (
                        $app->input->getString('option') == 'com_installer' &&
                        $app->input->getString('view', '') == 'update'
                    ) {
                        $breaks = '<br /><h4>BreezingForms Pro</h4>';
                        $breaks2 = '<br /><br />';
                    }
                    FactorytApplication()->enqueueMessage($breaks . $message . $breaks2, 'warning');
                }
            }

        } catch (Exception $e) {

        } catch (Error $e) {

        }
    }

    public function onAfterRender()
    {

        if (!file_exists(JPATH_ADMINISTRATOR . '/components/com_breezingforms/breezingforms.php')) {
            return;
        }

        $app = FactorytApplication();

        if ($app->input->getString('option') == 'com_menus' && $app->input->getString('view') == 'items') {

            $body = FactorytApplication()->getBody();
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/bf_icon.png width=23px; /&gt;', '', $body);
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/bf_icon.png width=23; /&gt;', '', $body);
            FactorytApplication()->setBody($body);
        }

        if ($app->input->getString('option') == 'com_cpanel' && $app->input->getString('dashboard') == 'components') {

            $body = FactorytApplication()->getBody();
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/folder-open.png width=17; /&gt;', '', $body);
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/pencil-square.png width=17; /&gt;', '', $body);
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/code.png width=17; /&gt;', '', $body);
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/puzzle-pieces.png width=17; /&gt;', '', $body);
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/link.png width=17; /&gt;', '', $body);
            $body = str_replace('&lt;img src=../administrator/components/com_breezingforms/images/icons/component-menu-icons/cog.png width=17; /&gt;', '', $body);
            FactorytApplication()->setBody($body);
        }
    }
}

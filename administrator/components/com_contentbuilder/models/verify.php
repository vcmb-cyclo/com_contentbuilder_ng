<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Application\ApplicationHelper;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'joomla_compat.php');
require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'modellegacy.php');

require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'classes' . DS . 'contentbuilder.php');

class ContentbuilderModelVerify extends CBModel
{

    private $frontend = false;
    var $mainframe;

    function __construct($config)
    {
        parent::__construct($config);

        $this->mainframe = Factory::getApplication();
        $this->frontend = $this->mainframe->isClient('site');

        $option = 'com_contentbuilder';

        $plugin = CBRequest::getVar('plugin', '');
        $verification_name = CBRequest::getVar('verification_name', '');

        $verification_id = CBRequest::getVar('verification_id', '');
        $setup = '';
        $user_id = 0;

        if (CBRequest::getBool('verify_by_admin', 0)) {

            $this->activate_by_admin(CBRequest::getVar('token', ''));
        }

        if (!$verification_id) {
            $user_id = $this->mainframe->getIdentity()->get('id', 0);
            $setup = $this->mainframe->getSession()->get($plugin . $verification_name, '', 'com_contentbuilder.verify.' . $plugin . $verification_name);
        } else {
            $this->_db->setQuery("Select `setup`,`user_id` From #__contentbuilder_verifications Where `verification_hash` = " . $this->_db->Quote($verification_id));
            $setup = $this->_db->loadAssoc();
            if (is_array($setup)) {
                $user_id = $setup['user_id'];
                $setup = $setup['setup'];
            }
        }

        $out = array();

        if ($setup) {
            parse_str($setup, $out);
        }

        if (isset($out['plugin']) && $out['plugin'] && isset($out['verification_name']) && $out['verification_name'] && isset($out['verify_view']) && $out['verify_view']) {
            // alright 
        } else {
            $this->mainframe->enqueueMessage('Spoofed data or invalid verification id', 'error');
            $this->mainframe->redirect('index.php');
        }

        if (isset($out['plugin_options'])) {
            $options = cb_b64dec($out['plugin_options']);
            parse_str($options, $opts);
            $out['plugin_options'] = $opts;
            if (!count($out['plugin_options'])) {
                $out['plugin_options'] = array();
            }
        } else {
            $out['plugin_options'] = array();
        }

        $_now = Factory::getDate();

        //$this->_db->setQuery("Select count(id) From #__contentbuilder_verifications Where Timestampdiff(Second, `start_date`, '".strtotime($_now->toSQL())."') < 1 And ip = " . $this->_db->Quote($_SERVER['REMOTE_ADDR']));
        //$ver = $this->_db->loadResult();

        //if($ver >= 5){
        //    $this->_db->setQuery("Delete From #__contentbuilder_verifications Where `verification_date` = '0000-00-00 00:00:00' And ip = " . $this->_db->Quote($_SERVER['REMOTE_ADDR']));
        //    $this->_db->execute();
        //    JError::raiseError(500, 'Penetration Denied');
        //}

        //$this->_db->setQuery("Delete From #__contentbuilder_verifications Where Timestampdiff(Second, `start_date`, '".strtotime($_now->toSQL())."') > 86400 And `verification_date` = '0000-00-00 00:00:00'");
        //$this->_db->execute();

        $rec = null;
        $redirect_view = '';

        if (isset($out['require_view']) && is_numeric($out['require_view']) && intval($out['require_view']) > 0) {

            if ($this->mainframe->getSession()->get('cb_last_record_user_id', 0, 'com_contentbuilder')) {
                $user_id = $this->mainframe->getSession()->get('cb_last_record_user_id', 0, 'com_contentbuilder');
                $this->mainframe->getSession()->clear('cb_last_record_user_id', 'com_contentbuilder');
            }

            $id = intval($out['require_view']);

            $this->_db->setQuery("Select `type`, `reference_id`, `show_all_languages_fe` From #__contentbuilder_forms Where published = 1 And id = " . $id);
            $formsettings = $this->_db->loadAssoc();

            if (!is_array($formsettings)) {
                throw new Exception('Verification Setup failed. Reason: View id ' . $out['require_view'] . ' has been requested but is not available (not existent or unpublished). Please update your content template or publish the view.', 500);
            }

            $form = contentbuilder::getForm($formsettings['type'], $formsettings['reference_id']);
            $labels = $form->getElementLabels();

            $ids = array();

            foreach ($labels as $reference_id => $label) {
                $ids[] = $reference_id;
            }

            if (intval($user_id) == 0) {
                $this->mainframe->redirect('index.php?option=com_contentbuilder&lang=' . CBRequest::getCmd('lang', '') . '&return=' . cb_b64enc(Uri::getInstance()->toString()) . '&controller=edit&record_id=&id=' . $id . '&rand=' . rand(0, getrandmax()));
            }

            $rec = $form->getListRecords($ids, '', array(), 0, 1, '', array(), 'desc', 0, false, $user_id, 0, -1, -1, -1, -1, array(), true, null);

            if (count($rec) > 0) {
                $rec = $rec[0];
                $rec = $form->getRecord($rec->colRecord, false, -1, true);
            }

            if (!$form->getListRecordsTotal($ids)) {
                $this->mainframe->redirect('index.php?option=com_contentbuilder&lang=' . CBRequest::getCmd('lang', '') . '&return=' . cb_b64enc(Uri::getInstance()->toString()) . '&controller=edit&record_id=&id=' . $id . '&rand=' . rand(0, getrandmax()));
            }
        }

        // clearing session after possible required view to make re-visits possible
        $this->mainframe->getSession()->clear($plugin . $verification_name, 'com_contentbuilder.verify.' . $plugin . $verification_name);

        $verification_data = '';
        if (is_array($rec) && count($rec)) {
            foreach ($rec as $value) {
                $verification_data .= urlencode(str_replace(array("\r", "\n"), '', $value->recTitle)) . "=" . urlencode(str_replace(array("\r", "\n"), '', $value->recValue)) . "&";
            }
            $verification_data = rtrim($verification_data, '&');
        }

        if (!CBRequest::getBool('verify', 0) && !CBRequest::getVar('token', '')) {
            $___now = $_now->toSql();

            $verification_id = md5(uniqid(null, true) . mt_rand(0, mt_getrandmax()) . $user_id);
            $this->_db->setQuery("
                    Insert Into #__contentbuilder_verifications
                    (
                    `verification_hash`,
                    `start_date`,
                    `verification_data`,
                    `user_id`,
                    `plugin`,
                    `ip`,
                    `setup`,
                    `client`
                    )
                    Values
                    (
                    " . $this->_db->Quote($verification_id) . ",
                    " . $this->_db->Quote($___now) . ",
                    " . $this->_db->Quote('type=normal&' . $verification_data) . ",
                    " . $user_id . ",
                    " . $this->_db->Quote($plugin) . ",
                    " . $this->_db->Quote($_SERVER['REMOTE_ADDR']) . ",
                    " . $this->_db->Quote($setup) . ",
                    " . intval($out['client']) . "
                    )
            ");
            $this->_db->execute();
        }

        /*
         if(intval($out['client']) && !$this->mainframe->isClient('administrator')){
            parse_str(Uri::getInstance()->getQuery(), $data1);
            $this_page = Uri::getInstance()->base() . 'administrator/index.php?'.http_build_query($data1, '', '&');
        }else{
            parse_str(Uri::getInstance()->getQuery(), $data1);
            $urlex = explode('?', Uri::getInstance()->toString());
            $this_page = $urlex[0] . '?' . http_build_query($data1, '', '&');
        }
         */
        if (intval($out['client']) && !$this->mainframe->isClient('administrator')) {
            $this_page = Uri::getInstance()->base() . 'administrator/index.php?' . Uri::getInstance()->getQuery();
        } else {
            $this_page = Uri::getInstance()->toString();
        }

        PluginHelper::importPlugin('contentbuilder_verify', $plugin);
        $setup_result = $this->mainframe->getDispatcher()->dispatch('onSetup', array($this_page, $out));

        if (!implode('', $setup_result)) {

            if (!CBRequest::getBool('verify', 0)) {

                if ($this->mainframe->isClient('administrator')) {
                    $local = explode('/', Uri::getInstance()->base());
                    unset($local[count($local) - 1]);
                    unset($local[count($local) - 1]);
                    parse_str(Uri::getInstance()->getQuery(), $data);
                    $this_page = implode('/', $local) . '/index.php?' . http_build_query($data, '', '&') . '&verify=1&verification_id=' . $verification_id;
                } else {
                    parse_str(Uri::getInstance()->getQuery(), $data);
                    $urlex = explode('?', Uri::getInstance()->toString());
                    $this_page = $urlex[0] . '?' . http_build_query($data, '', '&') . '&verify=1&verification_id=' . $verification_id;
                }

                $forward_result = $this->mainframe->getDispatcher()->dispatch('onForward', array($this_page, $out));
                $forward = implode('', $forward_result);

                if ($forward) {
                    $this->mainframe->redirect($forward);
                }
            } else {

                if ($verification_id) {

                    $msg = '';

                    $verify_result = $this->mainframe->getDispatcher()->dispatch('onVerify', array($this_page, $out));

                    if (count($verify_result)) {

                        if ($verify_result[0] === false) {

                            $msg = Text::_('COM_CONTENTBUILDER_VERIFICATION_FAILED');

                        } else {

                            if (isset($verify_result[0]['msg']) && $verify_result[0]['msg']) {

                                $msg = $verify_result[0]['msg'];
                            } else {
                                if (isset($out['verification_msg']) && $out['verification_msg']) {
                                    $msg = urldecode($out['verification_msg']);
                                } else {
                                    $msg = Text::_('COM_CONTENTBUILDER_VERIFICATION_SUCCESS');
                                }
                            }

                            if ((!$out['client'] && (!isset($out['return-site']) || !$out['return-site'])) || ($out['client'] && (!isset($out['return-admin']) || !$out['return-admin']))) {
                                if (intval($out['client']) && !$this->mainframe->isClient('administrator')) {
                                    $redirect_view = Uri::getInstance()->base() . 'administrator/index.php?option=com_contentbuilder&controller=list&lang=' . CBRequest::getCmd('lang', '') . '&id=' . $out['verify_view'];
                                } else {
                                    $redirect_view = 'index.php?option=com_contentbuilder&controller=list&lang=' . CBRequest::getCmd('lang', '') . '&id=' . $out['verify_view'];
                                }
                            }

                            $this->_db->setQuery("Select id From #__contentbuilder_users Where userid = " . $this->_db->Quote($user_id) . " And form_id = " . intval($out['verify_view']));
                            $usertableid = $this->_db->loadResult();

                            $levels = explode(',', $out['verify_levels']);
                            $___now = $_now->toSql();
                            if ($usertableid) {
                                $this->_db->setQuery("Update #__contentbuilder_users
                                Set
                                " . (in_array('view', $levels) ? ' verified_view=1, verification_date_view=' . $this->_db->Quote($___now) . ", " : '') . "
                                " . (in_array('new', $levels) ? ' verified_new=1, verification_date_new=' . $this->_db->Quote($___now) . ", " : '') . "
                                " . (in_array('edit', $levels) ? ' verified_edit=1, verification_date_edit=' . $this->_db->Quote($___now) . ", " : '') . "
                                published = 1
                                Where id = $usertableid
                                ");
                                $this->_db->execute();
                            } else {
                                $this->_db->setQuery("
                                Insert Into #__contentbuilder_users
                                (
                                " . (in_array('view', $levels) ? 'verified_view, verification_date_view,' : '') . "
                                " . (in_array('new', $levels) ? 'verified_new, verification_date_new,' : '') . "
                                " . (in_array('edit', $levels) ? 'verified_edit, verification_date_edit,' : '') . "
                                published,
                                userid,
                                form_id
                                )
                                Values
                                (
                                " . (in_array('view', $levels) ? '1, ' . $this->_db->Quote($___now) . ',' : '') . "
                                " . (in_array('new', $levels) ? '1, ' . $this->_db->Quote($___now) . ',' : '') . "
                                " . (in_array('edit', $levels) ? '1, ' . $this->_db->Quote($___now) . ',' : '') . "
                                1,
                                " . $this->_db->Quote($user_id) . ",
                                " . intval($out['verify_view']) . "
                                )
                                ");
                                $this->_db->execute();
                            }

                            $verification_data = ($verification_data ? '&' : '') . '';
                            if (isset($verify_result[0]['data']) && is_array($verify_result[0]['data']) && count($verify_result[0]['data'])) {
                                foreach ($verify_result[0]['data'] as $key => $value) {
                                    $verification_data .= urlencode(str_replace(array("\r", "\n"), '', $key)) . "=" . urlencode(str_replace(array("\r", "\n"), '', $value)) . "&";
                                }
                                $verification_data = rtrim($verification_data, '&');
                            }

                            $this->_db->setQuery("
                                Update #__contentbuilder_verifications
                                Set
                                `verification_hash` = '',
                                `is_test` = " . (isset($verify_result[0]['is_test']) ? intval(isset($verify_result[0]['is_test'])) : 0) . ",
                                `verification_date` = " . $this->_db->Quote($___now) . " 
                                " . ($verification_data ? ',verification_data = concat(verification_data, ' . $this->_db->Quote($verification_data) . ') ' : '') . "
                                Where
                                verification_hash = " . $this->_db->Quote($verification_id) . "
                                And
                                verification_hash <> ''
                                And
                                `verification_date` = '0000-00-00 00:00:00'
                                
                            ");
                            $this->_db->execute();

                            // token check if given
                            if (CBRequest::getVar('token', '')) {
                                $this->activate(CBRequest::getVar('token', ''));
                            }

                            // exit if requested
                            if (count($verify_result) && isset($verify_result[0]['exit']) && $verify_result[0]['exit']) {

                                @ob_end_clean();

                                if (isset($verify_result[0]['header']) && $verify_result[0]['header']) {
                                    header($verify_result[0]['header']);
                                }

                                exit;
                            }
                        }
                    }
                } else {
                    $msg = Text::_('COM_CONTENTBUILDER_VERIFICATION_NOT_EXECUTED');
                }

                $this->mainframe->enqueueMessage($msg, 'warning');

                if (!$out['client']) {
                    $this->mainframe->redirect($redirect_view ? $redirect_view : (!$out['client'] && isset($out['return-site']) && $out['return-site'] ? cb_b64dec($out['return-site']) : 'index.php'));
                } else {
                    $this->mainframe->redirect($redirect_view ? $redirect_view : ($out['client'] && isset($out['return-admin']) && $out['return-admin'] ? cb_b64dec($out['return-admin']) : 'index.php'));
                }
            }
        } else {
            throw new Exception('Verification Setup failed. Reason: ' . implode('', $setup_result), 500);
        }
    }

    public function activate_by_admin($token)
    {

        $user = $this->mainframe->getIdentity();

        if (!$user->authorise('core.create', 'com_users')) {

            throw new Exception('You are not allowed to perform this action.', 500);
        }

        Factory::getLanguage()->load('com_users', JPATH_SITE);

        $config = Factory::getConfig();
        $userParams = ComponentHelper::getParams('com_users');
        $db = $this->getDbo();

        // Get the user id based on the token.
        $db->setQuery(
            'SELECT `id` FROM `#__users`' .
            ' WHERE `activation` = ' . $db->Quote($token) .
            ' AND `block` = 1' .
            ' AND `lastvisitDate` = ' . $db->Quote($db->getNullDate())
        );
        $userId = (int) $db->loadResult();

        // Check for a valid user id.
        if (!$userId) {
            throw new Exception(Text::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'), 500);
        }

        // Load the users plugin group.
        PluginHelper::importPlugin('user');

        $query = $db->getQuery(true);

        // Activate the user.
        $container = Factory::getContainer();
        // To create a user instance:
        $user = $container->get(UserFactoryInterface::class)->loadUserById($userId);
        $user->set('activation', '');
        $user->set('block', '0');

        // Store the user object.
        if (!$user->save()) {
            throw new Exception(Text::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()), 500);
        }

        $params = ComponentHelper::getParams('com_users');
        $config = Factory::getConfig();

        // Compile the notification mail values.
        $data = $user->getProperties();
        $data['fromname'] = $config->get('fromname');
        $data['mailfrom'] = $config->get('mailfrom');
        $data['sitename'] = $config->get('sitename');
        $data['siteurl'] = Uri::root();

        $sendpassword = $params->get('sendpassword', 1);

        $emailSubject = Text::sprintf(
            'COM_USERS_EMAIL_ACCOUNT_DETAILS',
            $data['name'],
            $data['sitename']
        );

        if ($sendpassword) {
            $emailBody = Text::sprintf(
                'COM_USERS_EMAIL_REGISTERED_BODY',
                $data['name'],
                $data['sitename'],
                $data['siteurl'],
                $data['username'],
                $data['password_clear']
            );
        } else {
            $emailBody = Text::sprintf(
                'COM_USERS_EMAIL_REGISTERED_BODY_NOPW',
                $data['name'],
                $data['sitename'],
                $data['siteurl']
            );
        }


        // Send the registration email.
        $return = Factory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

        $this->mainframe->enqueueMessage(Text::_('COM_USERS_REGISTRATION_ADMINACTIVATE_SUCCESS'));
        $this->mainframe->redirect(Route::_('index.php?option=com_users', false));
    }

    public function activate($token)
    {
        Factory::getLanguage()->load('com_users', JPATH_SITE);

        $config = Factory::getConfig();
        $userParams = ComponentHelper::getParams('com_users');
        $db = $this->getDbo();

        // Get the user id based on the token.
        $db->setQuery(
            'SELECT `id` FROM `#__users`' .
            ' WHERE `activation` = ' . $db->Quote($token) .
            ' AND `block` = 1' .
            ' AND `lastvisitDate` = ' . $db->Quote($db->getNullDate())
        );
        $userId = (int) $db->loadResult();

        // Check for a valid user id.
        if (!$userId) {
            throw new Exception(Text::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'), 500);
        }

        // Load the users plugin group.
        PluginHelper::importPlugin('user');

        $query = $db->getQuery(true);

        // Activate the user.
        $user = Factory::getUser($userId);

        // Admin activation is on and user is verifying their email
        if (($userParams->get('useractivation') == 2) && !$user->getParam('activate', 0)) {
            $uri = Uri::getInstance();

            // Compile the admin notification mail values.
            $data = $user->getProperties();
            $data['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
            $user->set('activation', $data['activation']);
            $data['siteurl'] = Uri::root();
            $data['activate'] = Uri::root() . 'index.php?option=com_contentbuilder&controller=verify&token=' . $data['activation'] . '&verify_by_admin=1&format=raw';

            // Remove administrator/ from activate url in case this method is called from admin
            if ($this->mainframe->isClient('administrator')) {
                $adminPos = strrpos($data['activate'], 'administrator/');
                $data['activate'] = substr_replace($data['activate'], '', $adminPos, 14);
            }

            $data['fromname'] = $config->get('fromname');
            $data['mailfrom'] = $config->get('mailfrom');
            $data['sitename'] = $config->get('sitename');
            $user->setParam('activate', 1);
            $emailSubject = Text::sprintf(
                'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_SUBJECT',
                $data['name'],
                $data['sitename']
            );

            $emailBody = Text::sprintf(
                'COM_USERS_EMAIL_ACTIVATE_WITH_ADMIN_ACTIVATION_BODY',
                $data['sitename'],
                $data['name'],
                $data['email'],
                $data['username'],
                $data['activate']
            );

            // Get all admin users
            $query->clear()
                ->select($db->quoteName(array('name', 'email', 'sendEmail', 'id')))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('sendEmail') . ' = ' . 1);

            $db->setQuery($query);

            try {
                $rows = $db->loadObjectList();
            } catch (RuntimeException $e) {
                $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

                return false;
            }

            // Send mail to all users with users creating permissions and receiving system emails
            foreach ($rows as $row) {
                $usercreator = Factory::getUser($row->id);

                if ($usercreator->authorise('core.create', 'com_users')) {
                    $return = Factory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBody);

                    // Check for an error.
                    if ($return !== true) {
                        $this->setError(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

                        return false;
                    }
                }
            }

            $this->mainframe->enqueueMessage(Text::_('COM_USERS_REGISTRATION_VERIFY_SUCCESS'));
        }
        // Admin activation is on and admin is activating the account
        elseif (($userParams->get('useractivation') == 2) && $user->getParam('activate', 0)) {
            $user->set('activation', '');
            $user->set('block', '0');

            // Compile the user activated notification mail values.
            $data = $user->getProperties();
            $user->setParam('activate', 0);
            $data['fromname'] = $config->get('fromname');
            $data['mailfrom'] = $config->get('mailfrom');
            $data['sitename'] = $config->get('sitename');
            $data['siteurl'] = Uri::root();
            $emailSubject = Text::sprintf(
                'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_SUBJECT',
                $data['name'],
                $data['sitename']
            );

            $emailBody = Text::sprintf(
                'COM_USERS_EMAIL_ACTIVATED_BY_ADMIN_ACTIVATION_BODY',
                $data['name'],
                $data['siteurl'],
                $data['username']
            );

            $return = Factory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

            // Check for an error.
            if ($return !== true) {
                $this->setError(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

                return false;
            }

            $this->mainframe->enqueueMessage(Text::_('COM_USERS_REGISTRATION_VERIFY_SUCCESS'));
        } else {

            $user->set('activation', '');
            $user->set('block', '0');

            $this->mainframe->enqueueMessage(Text::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));

        }

        // Store the user object.
        if (!$user->save()) {
            throw new Exception(Text::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()), 500);
        }

        return true;
    }
}

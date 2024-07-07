<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://www.crosstec.org
 * @copyright   Copyright (C) 2024 by XDA+GIL
 * @license     GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

require_once(JPATH_SITE . DS . 'administrator' . DS . 'components' . DS . 'com_contentbuilder' . DS . 'classes' . DS . 'contentbuilder_helpers.php');

class plgContentbuilder_validationEmail extends CMSPlugin
{
    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
    }

    function onValidate($field, $fields, $record_id, $form, $value)
    {

        $lang = Factory::getLanguage();
        $lang->load('plg_contentbuilder_validation_email', JPATH_ADMINISTRATOR);

        $msg = '';

        if (!is_array($value)) {
            if (!contentbuilder_is_email($value)) {
                return Text::_('COM_CONTENTBUILDER_VALIDATION_EMAIL_INVALID') . ': ' . $field['label'];
            }
        } else {
            foreach ($value as $val) {
                if (!contentbuilder_is_email($val)) {
                    $msg .= $val;
                }
            }
            if ($msg) {
                return Text::_('COM_CONTENTBUILDER_VALIDATION_EMAIL_INVALID') . ': ' . $field['label'] . ' (' . $msg . ')';
            }
        }

        return $msg;
    }
}

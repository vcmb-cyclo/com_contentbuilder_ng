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

require_once(JPATH_SITE .'/administrator/components/com_contentbuilder_ng/classes/contentbuilder_helpers.php');

class plgContentbuilder_validationDate_is_valid extends CMSPlugin
{
    function __construct(&$subject, $params)
    {
        parent::__construct($subject, $params);
    }

    function onValidate($field, $fields, $record_id, $form, $value)
    {

        $lang = Factory::getApplication()->getLanguage();
        $lang->load('plg_contentbuilder_validation_date_is_valid', JPATH_ADMINISTRATOR);

        $options = $field['options'];

        $values = array();
        $values[0] = $value;

        if (is_array($value)) {
            $values = array();
            foreach ($value as $val) {
                $values[] = $val;
            }
        }

        foreach ($values as $val) {
            if (!contentbuilder_is_valid_date($val, isset($options->transfer_format) ? $options->transfer_format : 'YYYY-mm-dd')) {
                return Text::_('COM_CONTENTBUILDER_VALIDATION_DATE_IS_VALID') . ': ' . $field['label'] . ($val ? ' (' . $val . ')' : '');
            }
        }

        return '';
    }
}

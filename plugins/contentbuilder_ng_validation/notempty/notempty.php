<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die ('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;

class plgContentbuilder_ng_validationNotempty extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onValidate' => 'onValidate'];
    }

    function onValidate($field, $fields, $record_id, $form, $value)
    {

        $lang = Factory::getApplication()->getLanguage();
        $lang->load('plg_contentbuilder_ng_validation_notempty', JPATH_ADMINISTRATOR);

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $msg = '';

        if (!is_array($value)) {

            if ($field['type'] == 'upload') {
                $msg = '';
                $record_with_file_found = false;
                $record = $form->getRecord($record_id, false, -1, true);
                foreach ($record as $item) {
                    if ($item->recElementId == $field['reference_id']) {
                        if ($item->recValue != '') {
                            $record_with_file_found = true;
                        }
                        break;
                    }
                }
                if (!$record_with_file_found && empty ($value)) {
                    $msg = trim($field['validation_message']) ? trim($field['validation_message']) : Text::_('COM_CONTENTBUILDER_NG_VALIDATION_VALUE_EMPTY') . ': ' . $field['label'];
                }
            } else {
                $value = trim($value);
                if (empty ($value)) {
                    $msg = trim($field['validation_message']) ? trim($field['validation_message']) : Text::_('COM_CONTENTBUILDER_NG_VALIDATION_VALUE_EMPTY') . ': ' . $field['label'];
                }
            }
        } else {
            $has = '';
            foreach ($value as $item) {
                if ($item != 'cbGroupMark') {
                    $has .= $item;
                }
            }
            if (!$has) {
                $msg = trim($field['validation_message']) ? trim($field['validation_message']) : Text::_('COM_CONTENTBUILDER_NG_VALIDATION_VALUE_EMPTY') . ': ' . $field['label'];
            }
        }
        return $msg;
    }
}

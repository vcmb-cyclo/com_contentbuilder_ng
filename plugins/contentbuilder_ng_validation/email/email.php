<?php
/**
 * @package     ContentBuilder
 * @author      Markus Bopp
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

// No direct access
\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use CB\Component\Contentbuilder_ng\Administrator\Helper\ContentbuilderHelper;

class plgContentbuilder_ng_validationEmail extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onValidate' => 'onValidate'];
    }

    function onValidate($field, $fields, $record_id, $form, $value)
    {

        $lang = Factory::getApplication()->getLanguage();
        $lang->load('plg_contentbuilder_ng_validation_email', JPATH_ADMINISTRATOR);

        $msg = '';

        if (!is_array($value)) {
            if (!ContentbuilderHelper::isEmail($value)) {
                return Text::_('COM_CONTENTBUILDER_NG_VALIDATION_EMAIL_INVALID') . ': ' . $field['label'];
            }
        } else {
            foreach ($value as $val) {
                if (!ContentbuilderHelper::isEmail($val)) {
                    $msg .= $val;
                }
            }
            if ($msg) {
                return Text::_('COM_CONTENTBUILDER_NG_VALIDATION_EMAIL_INVALID') . ': ' . $field['label'] . ' (' . $msg . ')';
            }
        }

        return $msg;
    }
}

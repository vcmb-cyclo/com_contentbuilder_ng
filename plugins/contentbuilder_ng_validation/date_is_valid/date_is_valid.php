<?php
/**
 * @version     6.0
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

class plgContentbuilder_ng_validationDate_is_valid extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return ['onValidate' => 'onValidate'];
    }

    function onValidate($field, $fields, $record_id, $form, $value)
    {

        $lang = Factory::getApplication()->getLanguage();
        $lang->load('plg_contentbuilder_ng_validation_date_is_valid', JPATH_ADMINISTRATOR);

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
            if (!ContentbuilderHelper::isValidDate($val, isset($options->transfer_format) ? $options->transfer_format : 'YYYY-mm-dd')) {
                return Text::_('COM_CONTENTBUILDER_NG_VALIDATION_DATE_IS_VALID') . ': ' . $field['label'] . ($val ? ' (' . $val . ')' : '');
            }
        }

        return '';
    }
}

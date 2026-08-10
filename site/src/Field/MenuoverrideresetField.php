<?php

namespace CB\Component\Contentbuilderng\Site\Field;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

final class MenuoverrideresetField extends FormField
{
    protected $type = 'Menuoverridereset';

    protected function getInput(): string
    {
        $names = [];
        $xml = $this->form?->getXml();

        if ($xml) {
            foreach ((array) $xml->xpath('//fieldset[@name="settings"]/field[@name]') as $field) {
                $name = (string) $field['name'];

                if (!in_array($name, ['form_id', 'forms', 'record_id', 'cb_controller', 'cb_latest', 'cb_menu_reset'], true)) {
                    $names[] = $name;
                }
            }
        }

        $wa = RuntimeContextHelper::getApplication()->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_contentbuilderng');
        $wa->useStyle('com_contentbuilderng.menu-options.css');
        $wa->useScript('com_contentbuilderng.menu-list-options.js');

        return sprintf(
            '<button type="button" class="btn btn-secondary" data-cb-menu-reset-overrides data-cb-menu-reset-names="%s" data-cb-menu-reset-confirm="%s"><span class="icon-undo" aria-hidden="true"></span> %s</button>',
            htmlspecialchars(json_encode($names, JSON_THROW_ON_ERROR), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_RESET_TO_DEFAULT_CONFIRM'), ENT_QUOTES, 'UTF-8'),
            htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_RESET_TO_DEFAULT'), ENT_QUOTES, 'UTF-8')
        );
    }
}

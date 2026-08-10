<?php

namespace CB\Component\Contentbuilderng\Site\Field;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Helper\MenuViewDefaultsHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class MenuinheritField extends ListField
{
    protected $type = 'Menuinherit';

    protected function getOptions(): array
    {
        $fixedDefault = trim((string) ($this->element['data-cb-menu-fixed-default'] ?? ''));

        if ($fixedDefault !== '') {
            $default = (int) $fixedDefault === 1 ? 1 : 0;
        } else {
            $formId = MenuViewDefaultsHelper::getSelectedFormId($this->form);
            $default = MenuViewDefaultsHelper::get($formId)[$this->fieldname] ?? 0;
        }
        $label = Text::sprintf(
            'COM_CONTENTBUILDERNG_USE_DEFAULT_VALUE',
            $default === 1 ? Text::_('JYES') : Text::_('JNO')
        );
        $inherit = HTMLHelper::_('select.option', '-1', $label);

        return array_merge([$inherit], parent::getOptions());
    }
}

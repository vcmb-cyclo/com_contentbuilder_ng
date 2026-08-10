<?php

namespace CB\Component\Contentbuilderng\Site\Field;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\ListLimitHelper;
use CB\Component\Contentbuilderng\Administrator\Field\ListlimitField;
use CB\Component\Contentbuilderng\Site\Helper\MenuViewDefaultsHelper;

final class MenunumberField extends ListlimitField
{
    protected $type = 'Menunumber';

    protected function getInput(): string
    {
        $formId = MenuViewDefaultsHelper::getSelectedFormId($this->form);
        $default = MenuViewDefaultsHelper::get($formId)[$this->fieldname]
            ?? ListLimitHelper::getGlobalDefault();
        $this->element['data-cb-list-limit-mode'] = 'menu';
        $this->element['data-cb-list-limit-inherited'] = (string) $default;
        $this->element['data-cb-menu-inherit-value'] = '';

        if ((string) $this->value === (string) ListLimitHelper::INHERIT) {
            $this->value = '';
        }

        return parent::getInput();
    }
}

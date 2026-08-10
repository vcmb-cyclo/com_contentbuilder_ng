<?php

namespace CB\Component\Contentbuilderng\Site\Field;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Site\Helper\MenuViewDefaultsHelper;
use CB\Component\Contentbuilderng\Site\Helper\PreviewThemeHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

final class MenuthemeField extends ListField
{
    protected $type = 'Menutheme';

    protected function getOptions(): array
    {
        $formId = MenuViewDefaultsHelper::getSelectedFormId($this->form);
        $viewTheme = (string) (MenuViewDefaultsHelper::get($formId)['cb_theme_plugin'] ?? 'thoth');
        $options = [
            HTMLHelper::_('select.option', '', Text::sprintf(
                'COM_CONTENTBUILDERNG_USE_DEFAULT_VALUE',
                self::formatThemeName($viewTheme)
            )),
        ];

        foreach (PreviewThemeHelper::availableThemes() as $theme) {
            $options[] = HTMLHelper::_('select.option', $theme, self::formatThemeName($theme));
        }

        return array_merge($options, parent::getOptions());
    }

    private static function formatThemeName(string $theme): string
    {
        $theme = trim($theme);

        return $theme === '' ? 'Thoth' : ucfirst($theme);
    }
}

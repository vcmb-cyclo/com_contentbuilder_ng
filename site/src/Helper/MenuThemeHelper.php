<?php

namespace CB\Component\Contentbuilderng\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\Input\Input;

final class MenuThemeHelper
{
    public const PARAM = 'cb_theme_plugin';

    public static function resolve(Input $input, string $viewTheme): string
    {
        $menuTheme = trim((string) $input->getCmd(self::PARAM, ''));

        if ($menuTheme !== '' && in_array($menuTheme, PreviewThemeHelper::availableThemes(), true)) {
            return $menuTheme;
        }

        return trim($viewTheme);
    }
}

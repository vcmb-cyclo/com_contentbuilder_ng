<?php
/**
 * @package     ContentBuilder
 * @author      Xavier DANO / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @copyright   Copyright (C) 2026 by XDA+GIL
 * @license     GNU/GPL
 */

namespace CB\Component\Contentbuilder\Administrator\Helper;

\defined('_JEXEC') or die;

final class VendorHelper
{
    private static bool $loaded = false;

    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        $autoload = JPATH_ADMINISTRATOR . '/components/com_contentbuilder/vendor/autoload.php';

        if (!is_file($autoload)) {
            throw new \RuntimeException('Composer autoload not found: ' . $autoload);
        }

        require_once $autoload;
        self::$loaded = true;
    }
}

<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

use Joomla\CMS\Uri\Uri;

final class EmbeddedListHelpService
{
    public static function syntaxUrl(): string
    {
        return Uri::root()
            . 'index.php?option=com_contentbuilderng&task=cblisthelp.display';
    }
}

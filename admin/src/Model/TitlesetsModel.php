<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Administrator\Model;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Service\CbStatsTitleSetManagerService;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

final class TitlesetsModel extends BaseDatabaseModel
{
    /** @return list<array<string, mixed>> */
    public function getItems(): array
    {
        return (new CbStatsTitleSetManagerService(JPATH_SITE))->listFiles();
    }
}

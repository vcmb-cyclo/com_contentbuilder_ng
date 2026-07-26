<?php

namespace CB\Plugin\Content\ContentbuilderngStats\Service;

\defined('_JEXEC') or die;

final class IdSumException extends \InvalidArgumentException
{
    public const TOO_FEW = 1;
    public const TOO_MANY = 2;
    public const INVALID_ID = 3;
    public const DUPLICATE_ID = 4;
    public const CONFLICT = 5;
}

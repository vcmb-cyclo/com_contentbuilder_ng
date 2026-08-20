<?php

declare(strict_types=1);

namespace CB\Plugin\Content\ContentbuilderngStats\Service;

\defined('_JEXEC') or die;

final class CssDimensionService
{
    public static function normalize(string $value): ?string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return null;
        }

        if (preg_match('/^([1-9][0-9]*)(px|%)?$/D', $value, $matches) !== 1) {
            return null;
        }

        $number = (int) $matches[1];
        $unit = $matches[2] ?? 'px';
        if (($unit === '%' && $number > 100) || ($unit === 'px' && $number > 5000)) {
            return null;
        }

        return $number . $unit;
    }
}

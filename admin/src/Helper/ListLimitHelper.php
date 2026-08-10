<?php

namespace CB\Component\Contentbuilderng\Administrator\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Language\Text;

final class ListLimitHelper
{
    public const INHERIT = -1;
    public const ALL = 0;
    public const FACTORY_DEFAULT = 20;
    public const FACTORY_CHOICES = '5,10,20,25,50,100,All';

    public static function getGlobalDefault(): int
    {
        $value = (int) ComponentHelper::getParams('com_contentbuilderng')
            ->get('default_list_limit', self::FACTORY_DEFAULT);

        return $value >= self::ALL ? $value : self::FACTORY_DEFAULT;
    }

    /** @return list<int> */
    public static function getPaginationChoices(): array
    {
        $configured = (string) ComponentHelper::getParams('com_contentbuilderng')
            ->get('pagination_choices', self::FACTORY_CHOICES);

        try {
            return self::parsePaginationChoices($configured);
        } catch (\UnexpectedValueException) {
            return self::parsePaginationChoices(self::FACTORY_CHOICES);
        }
    }

    /** @return list<int> */
    public static function parsePaginationChoices(string $configured): array
    {
        $choices = [];
        $seen = [];

        foreach (explode(',', $configured) as $rawChoice) {
            $choice = preg_replace('/\s+/u', '', trim($rawChoice));

            if ($choice === '') {
                continue;
            }

            if (strcasecmp($choice, 'All') === 0) {
                $value = self::ALL;
            } elseif (ctype_digit($choice) && (int) $choice > self::ALL) {
                $value = (int) $choice;
            } else {
                throw new \UnexpectedValueException('Invalid pagination choice: ' . $rawChoice);
            }

            if (!isset($seen[$value])) {
                $choices[] = $value;
                $seen[$value] = true;
            }
        }

        if ($choices === []) {
            throw new \UnexpectedValueException('Pagination choices cannot be empty.');
        }

        return $choices;
    }

    public static function formatPaginationChoices(array $choices): string
    {
        return implode(',', array_map(
            static fn (int $value): string => $value === self::ALL ? 'All' : (string) $value,
            $choices
        ));
    }

    /** @param list<int> $choices
     *  @return list<int>
     */
    public static function insertCurrentPaginationChoice(array $choices, int $current): array
    {
        if ($current <= self::ALL || in_array($current, $choices, true)) {
            return $choices;
        }

        foreach ($choices as $index => $choice) {
            if ($choice > $current) {
                array_splice($choices, $index, 0, [$current]);

                return $choices;
            }
        }

        $choices[] = $current;

        return $choices;
    }

    public static function normalizeStoredViewValue(mixed $value): int
    {
        if ($value === null || $value === '') {
            return self::INHERIT;
        }

        return max(self::INHERIT, (int) $value);
    }

    public static function resolveViewValue(mixed $value, ?int $globalDefault = null): int
    {
        $stored = self::normalizeStoredViewValue($value);

        return $stored === self::INHERIT
            ? ($globalDefault ?? self::getGlobalDefault())
            : $stored;
    }

    public static function resolveMenuValue(mixed $value, int $viewDefault): int
    {
        if ($value === null || $value === '' || (int) $value < self::ALL) {
            return max(self::ALL, $viewDefault);
        }

        return (int) $value;
    }

    public static function normalizeRuntimeValue(mixed $value, int $fallback): int
    {
        $value = (int) $value;

        return $value >= self::ALL ? $value : max(self::ALL, $fallback);
    }

    public static function registerFieldAssets(HtmlDocument $document): void
    {
        $wa = $document->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_contentbuilderng');
        $wa->useScript('com_contentbuilderng.list-limit-field');
        $wa->useStyle('com_contentbuilderng.list-limit-field');

        $document->addScriptOptions('com_contentbuilderng.listLimitField', [
            'choices' => self::getPaginationChoices(),
        ]);
    }
}

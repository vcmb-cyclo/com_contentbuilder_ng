<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Site\Service;

\defined('_JEXEC') or die;

/**
 * Resolves the {CBList actions="..."} allow-list: which interactive
 * controls a form's list/detail/edit screens render when reached through
 * the CBList content plugin's embedded iframe.
 *
 * This never grants anything by itself. Every call site is expected to
 * combine isAllowed() with the ACL check (or view-configuration flag) that
 * already gates that control for a normal, non-embedded visit — the
 * allow-list can only narrow what ACL already permits, never widen it. An
 * empty allow-list (the tag's `actions=` attribute omitted) means
 * unrestricted: every control ACL already allows is shown, exactly as
 * before this feature existed.
 */
final class EmbeddedListActionFilterService
{
    /**
     * The exhaustive, only-valid set of {CBList actions="..."} values.
     * Keep in sync with docs/en/frontend.md, docs/fr/frontend.md and the
     * plugin's own help text in all three languages.
     */
    public const ACTIONS = [
        'search',
        'state',
        'publish',
        'language',
        'new',
        'edit',
        'delete',
        'export',
        'rating',
        'detail',
        'print',
    ];

    /**
     * Splits on `|`, same convention as {CBStats hide="..."}. `,` and `;`
     * are rejected rather than silently accepted as an alternate separator
     * — both appear inside action names' surrounding French/German prose
     * often enough in copy-pasted examples that silently splitting on them
     * too would be a footgun.
     *
     * @return list<string>
     */
    public static function parseActions(string $rawActions): array
    {
        $rawActions = trim($rawActions);

        if ($rawActions === '') {
            return [];
        }

        if (str_contains($rawActions, ',') || str_contains($rawActions, ';')) {
            throw new \InvalidArgumentException('actions');
        }

        $actions = array_map(
            static fn(string $action): string => mb_strtolower(trim($action), 'UTF-8'),
            explode('|', $rawActions)
        );
        $actions = array_values(array_filter(
            $actions,
            static fn(string $action): bool => $action !== ''
        ));

        return array_values(array_unique($actions));
    }

    public static function isKnownAction(string $action): bool
    {
        return in_array($action, self::ACTIONS, true);
    }

    /**
     * @param list<string> $allowedActions
     */
    public static function isAllowed(string $action, array $allowedActions): bool
    {
        return $allowedActions === [] || in_array($action, $allowedActions, true);
    }

    /**
     * Builds the "which {CBList actions="..."} terms apply here, and are
     * they currently allowed" table shown in the frontend debug panel, for
     * the subset of ACTIONS a given screen actually gates.
     *
     * @param list<string> $relevantActions
     * @param list<string> $allowedActions
     * @return array<string, bool>
     */
    public static function debugState(array $relevantActions, array $allowedActions): array
    {
        $state = [];

        foreach ($relevantActions as $action) {
            $state[$action] = self::isAllowed($action, $allowedActions);
        }

        return $state;
    }
}

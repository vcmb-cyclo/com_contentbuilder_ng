<?php

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$auditChecks = array_values((array) ($displayData['auditChecks'] ?? []));
$references = array_values((array) ($displayData['references'] ?? []));
$errors = array_values(array_filter(
    $auditChecks,
    static fn($check): bool => is_array($check)
        && (string) ($check['status'] ?? '') === 'error'
        && in_array((string) ($check['reference'] ?? ''), $references, true)
));
?>
<?php if ($errors !== []) : ?>
    <div class="alert alert-danger mb-3" role="alert">
        <div class="fw-semibold mb-1">
            <span class="fa-solid fa-circle-exclamation me-1" aria-hidden="true"></span>
            <?php echo Text::_('COM_CONTENTBUILDERNG_AUDIT_STATUS_ERROR'); ?>
        </div>
        <ul class="mb-0 ps-3">
            <?php foreach ($errors as $error) : ?>
                <?php $reference = trim((string) ($error['reference'] ?? '')); ?>
                <li>
                    <?php echo htmlspecialchars((string) ($error['message'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                    <?php if ($reference !== '') : ?>
                        <small class="d-block text-muted" title="<?php echo htmlspecialchars(Text::_('COM_CONTENTBUILDERNG_AUDIT_REFERENCE_TIP'), ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars(Text::sprintf('COM_CONTENTBUILDERNG_AUDIT_REFERENCE', $reference), ENT_QUOTES, 'UTF-8'); ?>
                        </small>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

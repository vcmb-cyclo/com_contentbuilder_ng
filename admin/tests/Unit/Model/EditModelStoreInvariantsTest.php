<?php

declare(strict_types=1);

namespace CB\Component\Contentbuilderng\Tests\Unit\Model;

use PHPUnit\Framework\TestCase;

/**
 * Characterization tests for site/src/Model/EditModel.php ahead of
 * REFACTORING_PLAN.md chapter D (decomposing store(), 1327 lines,
 * uncovered by any behavioural test).
 *
 * These are NOT behavioural tests: store() is not instantiated or invoked
 * here. Doing that for real requires a fake Database that can answer the
 * dozen-odd distinct queries store() issues across #__contentbuilderng_
 * elements/records/users/forms with per-query fixture data, plus fakes for
 * PluginHelper::importPlugin(), the mailer, and the filesystem — the
 * existing test Database stub (admin/tests/bootstrap.php) only special-
 * cases one #__usergroups query and is nowhere near that. Building that
 * harness is real, separately-scoped work, not something to shortcut here.
 *
 * What this file *does* protect, by asserting on the source text's
 * structure rather than on runtime behaviour: the exact ordering and
 * variable-scope contracts REFACTORING_PLAN.md chapter D names as its two
 * "points de vigilance" — the two things a well-intentioned extraction
 * could silently break without any existing test noticing. If a future
 * refactor changes one of these on purpose, update the assertion here in
 * the same commit, with a reason — same discipline as the PHPStan/PSR-12/
 * template-output baselines elsewhere in this suite, and the same
 * source-string-assertion style already established by
 * EditSparseSubmissionTest for this exact file.
 */
final class EditModelStoreInvariantsTest extends TestCase
{
    private string $source;

    protected function setUp(): void
    {
        $path = \dirname(__DIR__, 4) . '/site/src/Model/EditModel.php';
        $contents = file_get_contents($path);
        self::assertIsString($contents, 'Unable to read ' . $path);
        $this->source = $contents;
    }

    public function testStoreChecksTheCsrfTokenBeforeAnythingElse(): void
    {
        $storeStart = strpos($this->source, 'function store()');
        self::assertIsInt($storeStart, 'store() not found');

        $openBrace = strpos($this->source, '{', $storeStart);
        self::assertIsInt($openBrace, 'store() opening brace not found');

        // First non-whitespace content of the method body must be the
        // CSRF check — nothing (no logging, no state mutation, no early
        // data access) runs before it.
        $firstStatement = ltrim(substr($this->source, $openBrace + 1, 200));

        self::assertStringStartsWith(
            "if (!\\Joomla\\CMS\\Session\\Session::checkToken('post')) {",
            $firstStatement,
            'store() must check the CSRF token as its very first statement — found this instead: '
            . substr($firstStatement, 0, 80)
        );
    }

    /**
     * The plan's own words: "Le traitement des téléversements écrit sur
     * disque avant la validation des champs, puis supprime en cas
     * d'échec. Cet ordre doit être préservé ou explicitement changé,
     * jamais changé par accident."
     */
    public function testUploadWritesHappenBeforeValidationWhichCanDeleteThem(): void
    {
        $uploadWrite = strpos($this->source, 'File::upload($src, $dest');
        self::assertIsInt($uploadWrite, 'File::upload() call not found');

        $customValidateCall = strpos($this->source, 'self::customValidate(trim($the_upload_fields[$id]', $uploadWrite);
        self::assertIsInt($customValidateCall, 'customValidate() call for upload fields not found after the upload write');

        $builtinValidateCall = strpos($this->source, '$this->validateField(', $customValidateCall);
        self::assertIsInt($builtinValidateCall, 'validateField() call not found after customValidate()');

        $deleteOnFailure = strpos($this->source, 'File::delete($values[$id]);', $builtinValidateCall);
        self::assertIsInt($deleteOnFailure, 'File::delete() on validation failure not found after validateField()');

        self::assertTrue(
            $uploadWrite < $customValidateCall
            && $customValidateCall < $builtinValidateCall
            && $builtinValidateCall < $deleteOnFailure,
            'Expected order: disk write -> custom validation -> built-in validation -> delete on failure. '
            . 'Positions found: ' . implode(' < ', [$uploadWrite, $customValidateCall, $builtinValidateCall, $deleteOnFailure])
        );
    }

    /**
     * customValidate()/customAction() eval() admin-authored PHP with only
     * the parameters below in scope (plus $msg, pre-declared). Any
     * extraction of their callers must keep supplying exactly this set
     * from equivalent sources, or existing custom validation/action
     * scripts stored in the database will break at runtime with no error
     * until a form author's script references an undefined variable.
     */
    public function testCustomValidateAndCustomActionKeepTheirExactParameterContract(): void
    {
        self::assertMatchesRegularExpression(
            '/public static function customValidate\(\s*'
            . 'string \$code,\s*\$field,\s*\$fields,\s*\$record_id,\s*\$form,\s*\$value\s*'
            . '\)\s*\{\s*\$msg = \'\';\s*eval\(\$code\);\s*return \$msg;\s*\}/',
            $this->source,
            'customValidate() signature or eval() body changed — the exact set of '
            . 'variables visible to a stored custom_validation_script is part of its contract'
        );

        self::assertMatchesRegularExpression(
            '/public static function customAction\(\s*'
            . 'string \$code,\s*\$record_id,\s*\$article_id,\s*\$form,\s*\$field,\s*\$fields,\s*array \$values\s*'
            . '\)\s*\{\s*\$msg = \'\';\s*eval\(\$code\);\s*return \$msg;\s*\}/',
            $this->source,
            'customAction() signature or eval() body changed — the exact set of '
            . 'variables visible to a stored custom_action_script is part of its contract'
        );
    }
}

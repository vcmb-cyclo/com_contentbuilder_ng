<?php

/**
 * @version     6.0
 * @package     ContentBuilder NG
 * @author      Xavier DANO / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use CB\Component\Contentbuilder_ng\Administrator\Helper\Logger;

class plgContentbuilder_ng_themesJoomla6 extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentTemplateJavascript' => 'onContentTemplateJavascript',
            'onEditableTemplateJavascript' => 'onEditableTemplateJavascript',
            'onListViewJavascript' => 'onListViewJavascript',
            'onContentTemplateCss' => 'onContentTemplateCss',
            'onEditableTemplateCss' => 'onEditableTemplateCss',
            'onListViewCss' => 'onListViewCss',
            'onContentTemplateSample' => 'onContentTemplateSample',
            'onEditableTemplateSample' => 'onEditableTemplateSample',
        ];
    }
    /**
     * Appends a value to the event result payload.
     */
    private function pushEventResult(Event $event, string $value): void
    {
        $results = $event->getArgument('result') ?: [];
        if (!is_array($results)) {
            $results = [$results];
        }
        $results[] = $value;
        $event->setArgument('result', $results);
    }

    /* =========================
     * CSS / JS events
     * ========================= */

    public function onContentTemplateJavascript($event = null)
    {
        $out = '';

        // Event dispatch mode.
        if ($event instanceof Event) {
            $this->pushEventResult($event, $out);
            return;
        }

        // Direct return mode.
        return $out;
    }

    public function onEditableTemplateJavascript($event = null)
    {
        $out = '';

        if ($event instanceof Event) {
            $this->pushEventResult($event, $out);
            return;
        }

        return $out;
    }

    public function onListViewJavascript($event = null)
    {
        $out = '';

        if ($event instanceof Event) {
            $this->pushEventResult($event, $out);
            return;
        }

        return $out;
    }

    public function onContentTemplateCss($event = null)
    {
        $out = <<<'CSS'
.cbEditableWrapper {
    max-width: 1120px;
    margin: 1rem auto 2rem;
    padding: 1.1rem 1.25rem 1.35rem;
    border: 1px solid rgba(36, 61, 86, 0.12);
    border-radius: 1rem;
    background:
        radial-gradient(circle at top right, rgba(13, 110, 253, 0.08), transparent 38%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    box-shadow: 0 0.9rem 2rem rgba(16, 32, 56, 0.08);
}

.cbEditableWrapper > h1.display-6 {
    margin-bottom: 1rem !important;
    font-weight: 700;
    letter-spacing: 0.01em;
}

.cbEditableWrapper > h1.display-6::after {
    content: "";
    display: block;
    width: 4.5rem;
    height: 0.24rem;
    margin-top: 0.55rem;
    border-radius: 999px;
    background: linear-gradient(90deg, #0d6efd 0%, #3f8cff 100%);
}

.cbEditableWrapper .cbToolBar {
    padding: 0.65rem;
    border: 1px solid rgba(45, 73, 104, 0.14);
    border-radius: 0.9rem;
    background: rgba(255, 255, 255, 0.85);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

.cbEditableWrapper .cbColumnHeader {
    grid-template-columns: minmax(170px, 28%) minmax(0, 1fr);
    align-items: center;
    gap: 0.75rem;
    margin: 0.15rem 0 0.8rem;
    padding: 0.45rem 0.72rem;
    border: 1px solid rgba(36, 61, 86, 0.16);
    border-radius: 0.7rem;
    background: #eef4ff;
    color: #2a3f5e;
    font-size: 0.82rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}

.cbEditableWrapper .cbColumnHeader .cbColumnHeaderLabel,
.cbEditableWrapper .cbColumnHeader .cbColumnHeaderValue {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.cbEditableWrapper .cbToolBar .cbButton.btn {
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.01em;
    padding-inline: 0.95rem;
    box-shadow: 0 0.32rem 0.85rem rgba(16, 32, 56, 0.12);
}

.cbEditableWrapper .cbToolBar .cbSaveButton.btn-primary,
.cbEditableWrapper .cbToolBar .cbArticleSettingsButton.btn-primary {
    border-color: #0a58ca;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.cbEditableWrapper .cbToolBar .cbDeleteButton.btn-primary {
    border-color: #bb2d3b;
    background: linear-gradient(135deg, #dc3545 0%, #b32635 100%);
}

.cbEditableWrapper .created-by {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin: 0 0.45rem 0.3rem 0;
    padding: 0.22rem 0.62rem;
    border-radius: 999px;
    border: 1px solid rgba(28, 51, 78, 0.12);
    background: #eef4ff;
    color: #2d3e59;
}

.cbEditableWrapper .alert.alert-warning {
    border: 1px solid rgba(189, 116, 0, 0.34);
    border-left-width: 0.35rem;
    border-radius: 0.8rem;
    background: linear-gradient(90deg, rgba(255, 244, 222, 0.94) 0%, rgba(255, 249, 237, 0.96) 100%);
}

.cbEditableWrapper #cbArticleOptions {
    margin-bottom: 1rem;
    padding: 0.25rem;
    border-radius: 0.95rem;
    border: 1px solid rgba(36, 61, 86, 0.1);
    background: rgba(255, 255, 255, 0.72);
}

.cbEditableWrapper fieldset {
    border: 1px solid rgba(36, 61, 86, 0.14) !important;
    border-radius: 0.85rem !important;
    background: #ffffff;
    box-shadow: 0 0.32rem 0.85rem rgba(16, 32, 56, 0.05);
}

.cbEditableWrapper .form-label,
.cbEditableWrapper label {
    font-weight: 600;
    color: #243d56;
}

.cbEditableWrapper :is(
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    input[type="datetime-local"],
    input[type="time"],
    input[type="url"],
    input[type="password"],
    textarea,
    select
) {
    border: 1px solid rgba(36, 61, 86, 0.2);
    border-radius: 0.62rem;
    background-color: #ffffff;
    transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
}

.cbEditableWrapper :is(
    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="date"],
    input[type="datetime-local"],
    input[type="time"],
    input[type="url"],
    input[type="password"],
    textarea,
    select
):focus {
    border-color: rgba(13, 110, 253, 0.55);
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.14);
    outline: 0;
}

.cbEditableWrapper .cbSelectField .form-select {
    display: inline-block;
    width: auto;
    max-width: 100%;
}

.cbEditableWrapper a {
    text-underline-offset: 0.15em;
}

.cbDetailsWrapper {
    max-width: 1120px;
    margin: 1rem auto 2rem;
    padding: 1.1rem 1.25rem 1.35rem;
    border: 1px solid rgba(36, 61, 86, 0.12);
    border-radius: 1rem;
    background:
        radial-gradient(circle at top right, rgba(13, 110, 253, 0.08), transparent 38%),
        linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    box-shadow: 0 0.9rem 2rem rgba(16, 32, 56, 0.08);
}

.cbDetailsWrapper > h1.display-6 {
    margin-bottom: 1rem !important;
    font-weight: 700;
    letter-spacing: 0.01em;
}

.cbDetailsWrapper > h1.display-6::after {
    content: "";
    display: block;
    width: 4.5rem;
    height: 0.24rem;
    margin-top: 0.55rem;
    border-radius: 999px;
    background: linear-gradient(90deg, #0d6efd 0%, #3f8cff 100%);
}

.cbDetailsWrapper .cbToolBar {
    padding: 0.35rem 0;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}

.cbDetailsWrapper .cbToolBar .cbButton.btn {
    border-radius: 999px;
    font-weight: 600;
    letter-spacing: 0.01em;
    padding-inline: 0.95rem;
    box-shadow: 0 0.32rem 0.85rem rgba(16, 32, 56, 0.12);
}

.cbDetailsWrapper .cbToolBar .cbEditButton.btn-primary {
    border-color: #0a58ca;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}

.cbDetailsWrapper .cbToolBar .cbDeleteButton.btn-primary {
    border-color: #bb2d3b;
    background: linear-gradient(135deg, #dc3545 0%, #b32635 100%);
}

.cbDetailsWrapper .created-by {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    margin: 0 0.45rem 0.3rem 0;
    padding: 0.22rem 0.62rem;
    border-radius: 999px;
    border: 1px solid rgba(28, 51, 78, 0.12);
    background: #eef4ff;
    color: #2d3e59;
}

.cbDetailsWrapper .alert.alert-warning {
    border: 1px solid rgba(189, 116, 0, 0.34);
    border-left-width: 0.35rem;
    border-radius: 0.8rem;
    background: linear-gradient(90deg, rgba(255, 244, 222, 0.94) 0%, rgba(255, 249, 237, 0.96) 100%);
}

.cbDetailsWrapper .cbDetailsBody {
    margin: 0.55rem 0 0.75rem;
    padding: 1rem 1rem 0.6rem;
    border: 1px solid rgba(36, 61, 86, 0.14);
    border-radius: 0.85rem;
    background: #ffffff;
    box-shadow: 0 0.32rem 0.85rem rgba(16, 32, 56, 0.05);
}

.cbDetailsWrapper .cbDetailsBody ul.category.list-striped.list-condensed {
    margin: 0;
    padding: 0;
    list-style: none;
    display: grid;
    gap: 0.58rem;
}

.cbDetailsWrapper .cbDetailsBody ul.category.list-striped.list-condensed > li {
    margin: 0;
    padding: 0.72rem 0.82rem;
    border: 1px solid rgba(36, 61, 86, 0.14);
    border-radius: 0.72rem;
    background: linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
    display: grid;
    grid-template-columns: minmax(190px, 31%) 1fr;
    gap: 0.72rem;
    align-items: start;
    box-shadow: 0 0.24rem 0.62rem rgba(16, 32, 56, 0.05);
}

.cbDetailsWrapper .cbDetailsBody ul.category.list-striped.list-condensed > li:nth-child(odd) {
    border-left: 0.22rem solid rgba(13, 110, 253, 0.4);
}

.cbDetailsWrapper .cbDetailsBody ul.category.list-striped.list-condensed > li strong.list-title {
    margin: 0;
    color: #2b4a70;
    font-size: 0.79rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    line-height: 1.35;
}

.cbDetailsWrapper .cbDetailsBody ul.category.list-striped.list-condensed > li > div {
    margin: 0;
    color: #162f4d;
    font-size: 0.97rem;
    line-height: 1.55;
    overflow-wrap: anywhere;
}

.cbDetailsWrapper .cbDetailsBody > :last-child {
    margin-bottom: 0;
}

.cbDetailsWrapper a {
    text-underline-offset: 0.15em;
}

.cbPrintBar .btn {
    border-radius: 999px;
    box-shadow: 0 0.2rem 0.6rem rgba(16, 32, 56, 0.08);
}

@media (max-width: 767.98px) {
    .cbEditableWrapper {
        margin-top: 0.6rem;
        padding: 0.9rem 0.8rem 1rem;
        border-radius: 0.8rem;
    }

    .cbEditableWrapper .cbToolBar {
        padding: 0.5rem;
    }

    .cbEditableWrapper .cbToolBar .cbButton.btn {
        width: 100%;
        justify-content: center;
    }

    .cbEditableWrapper .cbColumnHeader {
        margin-bottom: 0.6rem;
    }

    .cbDetailsWrapper {
        margin-top: 0.6rem;
        padding: 0.9rem 0.8rem 1rem;
        border-radius: 0.8rem;
    }

    .cbDetailsWrapper .cbToolBar {
        padding: 0.35rem 0;
    }

    .cbDetailsWrapper .cbToolBar .cbButton.btn {
        width: 100%;
        justify-content: center;
    }

    .cbDetailsWrapper .cbDetailsBody {
        padding: 0.8rem 0.75rem 0.4rem;
    }

    .cbDetailsWrapper .cbDetailsBody ul.category.list-striped.list-condensed > li {
        grid-template-columns: 1fr;
        gap: 0.42rem;
        padding: 0.62rem 0.66rem;
    }

    .cbDetailsWrapper .cbDetailsBody ul.category.list-striped.list-condensed > li strong.list-title {
        font-size: 0.75rem;
    }
}
CSS;

        if ($event instanceof Event) {
            $this->pushEventResult($event, $out);
            return;
        }

        return $out;
    }

    public function onEditableTemplateCss($event = null)
    {
        // Comme ton original: même CSS
        return $this->onContentTemplateCss($event);
    }

    public function onListViewCss($event = null)
    {
        $out = '';

        if ($event instanceof Event) {
            $this->pushEventResult($event, $out);
            return;
        }

        return $out;
    }

    /* =========================
     * Template samples
     * ========================= */

    public function onContentTemplateSample($arg0, $arg1 = null)
    {
        // Event dispatch mode: dispatch(new Event('onContentTemplateSample', [$id, $form]))
        if ($arg0 instanceof Event) {
            $event = $arg0;
            $args  = $event->getArguments();

            $contentbuilder_ng_form_id = (int) ($args[0] ?? 0);
            $form = $args[1] ?? null;

            $out = $this->buildContentTemplateSample($contentbuilder_ng_form_id, $form);
            $this->pushEventResult($event, $out);
            return;
        }

        // Direct call mode: onContentTemplateSample($id, $form)
        $contentbuilder_ng_form_id = (int) $arg0;
        $form = $arg1;

        return $this->buildContentTemplateSample($contentbuilder_ng_form_id, $form);
    }

    private function buildContentTemplateSample(int $contentbuilder_ng_form_id, $form): string
    {
        if (!$contentbuilder_ng_form_id || !is_object($form)) {
            return '';
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $elementTypes = $this->fetchElementTypes($db, $contentbuilder_ng_form_id, false);

        $out = '<ul class="list-group list-group-flush">' . "\n";
        $names = $form->getElementNames();

        foreach ($names as $reference_id => $name) {
            $type = $elementTypes[$reference_id] ?? null;

            if ($type !== null && $type !== 'hidden') {
                $out .= '{hide-if-empty ' . $name . '}' . "\n\n";
                $out .= '<li class="list-group-item"><div class="row g-2 align-items-start"><div class="col-3"><label class="form-label mb-0">{' . $name . ':label}</label></div><div class="col"><div class="form-control-plaintext py-0">{' . $name . ':value}</div></div></div></li>' . "\n\n";
                $out .= '{/hide}' . "\n\n";
            }
        }

        $out .= '</ul>' . "\n";
        return $out;
    }

    public function onEditableTemplateSample($arg0, $arg1 = null)
    {
        if ($arg0 instanceof Event) {
            $event = $arg0;
            $args  = $event->getArguments();

            $contentbuilder_ng_form_id = (int) ($args[0] ?? 0);
            $form = $args[1] ?? null;

            $out = $this->buildEditableTemplateSample($contentbuilder_ng_form_id, $form);
            $this->pushEventResult($event, $out);
            return;
        }

        $contentbuilder_ng_form_id = (int) $arg0;
        $form = $arg1;

        return $this->buildEditableTemplateSample($contentbuilder_ng_form_id, $form);
    }

    private function buildEditableTemplateSample(int $contentbuilder_ng_form_id, $form): string
    {
        if (!$contentbuilder_ng_form_id || !is_object($form)) {
            return '';
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $elementTypes = $this->fetchElementTypes($db, $contentbuilder_ng_form_id, true);
        if ($elementTypes === []) {
            $msg = 'No editable elements configured; generated editable sample uses all elements.';
            Factory::getApplication()->enqueueMessage($msg, 'warning');
            Log::add($msg, Log::WARNING, 'com_contentbuilder_ng');
            $elementTypes = $this->fetchElementTypes($db, $contentbuilder_ng_form_id, false);
        }

        $out = "\n";
        $names = $form->getElementNames();
        $hidden = [];

        foreach ($names as $reference_id => $name) {
            $type = $elementTypes[$reference_id] ?? null;

            if ($type === null) {
                continue;
            }

            if ($type !== 'hidden') {
                if ($type === 'checkboxgroup') {
                    $out .= '<div class="mb-3"><div class="form-label">{' . $name . ':label}</div><div>{' . $name . ':item}</div></div>';
                } elseif ($type === 'radiogroup') {
                    $out .= '<div class="mb-3"><div class="form-label">{' . $name . ':label}</div><div>{' . $name . ':item}</div></div>';
                } else {
                    $out .= '<div class="mb-3"><label class="form-label">{' . $name . ':label}</label><div>{' . $name . ':item}</div></div>' . "\n";
                }
            } else {
                $hidden[] = '{' . $name . ':item}' . "\n";
            }
        }

        foreach ($hidden as $hid) {
            $out .= $hid;
        }

        return $out;
    }

    private function fetchElementTypes(DatabaseInterface $db, int $contentbuilder_ng_form_id, bool $editableOnly): array
    {
        $where = "published = 1 AND form_id = " . (int) $contentbuilder_ng_form_id;

        if ($editableOnly) {
            $where .= " AND editable = 1";
        }

        $db->setQuery(
            "SELECT reference_id, `type`
             FROM #__contentbuilder_ng_elements
             WHERE " . $where
        );

        $rows = $db->loadAssocList();
        if (!is_array($rows) || $rows === []) {
            return [];
        }

        $elementTypes = [];
        foreach ($rows as $row) {
            if (!isset($row['reference_id'])) {
                continue;
            }
            $elementTypes[$row['reference_id']] = $row['type'] ?? '';
        }

        return $elementTypes;
    }
}

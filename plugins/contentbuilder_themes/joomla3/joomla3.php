<?php

/**
 * @package     ContentBuilder
 * @author      Markus Bopp / XDA+GIL
 * @link        https://breezingforms.vcmb.fr
 * @license     GNU/GPL
 */


defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\Event;

class PlgContentbuilder_themesJoomla3 extends CMSPlugin
{
    /**
     * Helper: pousse un résultat dans $event->result en mode Joomla 4/5/6.
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

        // Joomla 4/5/6 event dispatcher (ton code fait dispatch + getArgument('result'))
        if ($event instanceof Event) {
            $this->pushEventResult($event, $out);
            return;
        }

        // Legacy (retour direct)
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
        $out = '';

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
        // Mode Joomla 4/5/6 : dispatch(new Event('onContentTemplateSample', array($id,$form)))
        if ($arg0 instanceof Event) {
            $event = $arg0;
            $args  = $event->getArguments();

            $contentbuilder_form_id = (int) ($args[0] ?? 0);
            $form = $args[1] ?? null;

            $out = $this->buildContentTemplateSample($contentbuilder_form_id, $form);
            $this->pushEventResult($event, $out);
            return;
        }

        // Mode legacy : onContentTemplateSample($id, $form)
        $contentbuilder_form_id = (int) $arg0;
        $form = $arg1;

        return $this->buildContentTemplateSample($contentbuilder_form_id, $form);
    }

    private function buildContentTemplateSample(int $contentbuilder_form_id, $form): string
    {
        if (!$contentbuilder_form_id || !is_object($form)) {
            return '';
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $elementTypes = $this->fetchElementTypes($db, $contentbuilder_form_id, false);

        $out = '<ul class="category list-striped list-condensed">' . "\n";
        $names = $form->getElementNames();

        foreach ($names as $reference_id => $name) {
            $type = $elementTypes[$reference_id] ?? null;

            if ($type !== null && $type !== 'hidden') {
                $out .= '{hide-if-empty ' . $name . '}' . "\n\n";
                $out .= '<li class="cat-list-row0" ><strong class="list-title">{' . $name . ':label}</strong><div>{' . $name . ':value}</div></li>' . "\n\n";
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

            $contentbuilder_form_id = (int) ($args[0] ?? 0);
            $form = $args[1] ?? null;

            $out = $this->buildEditableTemplateSample($contentbuilder_form_id, $form);
            $this->pushEventResult($event, $out);
            return;
        }

        $contentbuilder_form_id = (int) $arg0;
        $form = $arg1;

        return $this->buildEditableTemplateSample($contentbuilder_form_id, $form);
    }

    private function buildEditableTemplateSample(int $contentbuilder_form_id, $form): string
    {
        if (!$contentbuilder_form_id || !is_object($form)) {
            return '';
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);
        $elementTypes = $this->fetchElementTypes($db, $contentbuilder_form_id, true);

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
                    $out .= '<div class="mb-3 d-flex flex-wrap align-items-center gap-2"><div class="form-label mb-0">{' . $name . ':label}</div> <div class="flex-grow-1"><fieldset class="checkbox">{' . $name . ':item}</fieldset></div></div>';
                } elseif ($type === 'radiogroup') {
                    $out .= '<div class="mb-3 d-flex flex-wrap align-items-center gap-2"><div class="form-label mb-0">{' . $name . ':label}</div> <div class="flex-grow-1"><fieldset class="radio">{' . $name . ':item}</fieldset></div></div>';
                } else {
                    $out .= '<div class="mb-3 d-flex flex-wrap align-items-center gap-2"><div class="form-label mb-0">{' . $name . ':label}</div> 
                            <div class="flex-grow-1">{' . $name . ':item}</div></div>' . "\n";
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

    private function fetchElementTypes(DatabaseInterface $db, int $contentbuilder_form_id, bool $editableOnly): array
    {
        $where = "published = 1 AND form_id = " . (int) $contentbuilder_form_id;

        if ($editableOnly) {
            $where .= " AND editable = 1";
        }

        $db->setQuery(
            "SELECT reference_id, `type`
             FROM #__contentbuilder_elements
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

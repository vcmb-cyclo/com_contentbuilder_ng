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

            $contentbuilder_ng_form_id = (int) ($args[0] ?? 0);
            $form = $args[1] ?? null;

            $out = $this->buildContentTemplateSample($contentbuilder_ng_form_id, $form);
            $this->pushEventResult($event, $out);
            return;
        }

        // Mode legacy : onContentTemplateSample($id, $form)
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

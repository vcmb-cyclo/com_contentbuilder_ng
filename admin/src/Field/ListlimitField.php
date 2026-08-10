<?php

namespace CB\Component\Contentbuilderng\Administrator\Field;

\defined('_JEXEC') or die;

use CB\Component\Contentbuilderng\Administrator\Helper\ListLimitHelper;
use CB\Component\Contentbuilderng\Administrator\Helper\RuntimeContextHelper;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

class ListlimitField extends FormField
{
    protected $type = 'Listlimit';

    protected function getInput(): string
    {
        $document = RuntimeContextHelper::getApplication()->getDocument();

        if ($document instanceof HtmlDocument) {
            ListLimitHelper::registerFieldAssets($document);
        }

        $mode = (string) ($this->element['data-cb-list-limit-mode'] ?? 'global');
        $inherited = (int) ($this->element['data-cb-list-limit-inherited'] ?? ListLimitHelper::FACTORY_DEFAULT);
        $inheritValue = $mode === 'view' ? (string) ListLimitHelper::INHERIT : '';
        $storedValue = $this->value;

        if ($storedValue === null || $storedValue === '' || (int) $storedValue < ListLimitHelper::ALL) {
            $storedValue = $inheritValue;
        } else {
            $storedValue = (string) max(ListLimitHelper::ALL, (int) $storedValue);
        }

        $allLabel = Text::_('JALL');
        $inheritedLabel = $inherited === ListLimitHelper::ALL ? $allLabel : (string) $inherited;
        $defaultLabel = $mode === 'menu'
            ? Text::sprintf('COM_CONTENTBUILDERNG_USE_DEFAULT_VALUE', $inheritedLabel)
            : Text::sprintf('COM_CONTENTBUILDERNG_LIST_LIMIT_DEFAULT_VALUE', $inheritedLabel);
        $choices = ListLimitHelper::getPaginationChoices();
        $displayValue = $this->getDisplayValue((string) $storedValue, $mode, $defaultLabel, $allLabel, $choices);
        $buttonLabel = Text::_('COM_CONTENTBUILDERNG_LIST_LIMIT_OPEN_CHOICES');
        $disabled = $this->disabled ? ' disabled' : '';
        $readonly = $this->readonly ? ' readonly' : '';
        $required = $this->required ? ' required' : '';

        $items = '';
        if ($mode !== 'global') {
            $items .= $this->renderChoice($inheritValue, $defaultLabel);
        }

        foreach ($choices as $choice) {
            $items .= $this->renderChoice((string) $choice, $choice === ListLimitHelper::ALL ? $allLabel : (string) $choice);
        }

        return sprintf(
            '<div class="input-group cb-list-limit-control" data-cb-list-limit-control data-mode="%s" data-inherit-value="%s" data-inherited="%d" data-default-label="%s" data-custom-format="%s" data-all-label="%s">'
            . '<input type="text" class="form-control" id="%s" value="%s" inputmode="numeric" autocomplete="off"%s%s%s>'
            . '<button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="%s"%s></button>'
            . '<ul class="dropdown-menu dropdown-menu-end">%s</ul>'
            . '<input type="hidden" id="%s-value" name="%s" value="%s" data-cb-list-limit-storage%s>'
            . '</div><div class="form-text" data-cb-list-limit-warning hidden>%s</div>',
            $this->escape($mode),
            $this->escape($inheritValue),
            $inherited,
            $this->escape($defaultLabel),
            $this->escape(Text::_('COM_CONTENTBUILDERNG_LIST_LIMIT_CUSTOM_VALUE')),
            $this->escape($allLabel),
            $this->escape($this->id),
            $this->escape($displayValue),
            $disabled,
            $readonly,
            $required,
            $this->escape($buttonLabel),
            $disabled,
            $items,
            $this->escape($this->id),
            $this->escape($this->name),
            $this->escape((string) $storedValue),
            $this->getStorageDataAttributes(),
            $this->escape(Text::_('COM_CONTENTBUILDERNG_LIST_LIMIT_ALL_WARNING'))
        );
    }

    private function getDisplayValue(
        string $storedValue,
        string $mode,
        string $defaultLabel,
        string $allLabel,
        array $choices
    ): string {
        if ($mode !== 'global' && ($storedValue === '' || (int) $storedValue < ListLimitHelper::ALL)) {
            return $defaultLabel;
        }

        $value = (int) $storedValue;
        if ($value === ListLimitHelper::ALL) {
            return $allLabel;
        }

        return in_array($value, $choices, true)
            ? (string) $value
            : Text::sprintf('COM_CONTENTBUILDERNG_LIST_LIMIT_CUSTOM_VALUE', $value);
    }

    private function renderChoice(string $value, string $label): string
    {
        return sprintf(
            '<li><button type="button" class="dropdown-item" data-cb-list-limit-choice="%s">%s</button></li>',
            $this->escape($value),
            $this->escape($label)
        );
    }

    private function getStorageDataAttributes(): string
    {
        $attributes = ['data-cb-list-limit' => 'true'];

        foreach ($this->element->attributes() as $name => $value) {
            $name = (string) $name;
            if (str_starts_with($name, 'data-')) {
                $attributes[$name] = (string) $value;
            }
        }

        $html = '';
        foreach ($attributes as $name => $value) {
            $html .= sprintf(' %s="%s"', $this->escape($name), $this->escape($value));
        }

        return $html;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

(function () {
    'use strict';

    const config = window.Joomla && typeof window.Joomla.getOptions === 'function'
        ? window.Joomla.getOptions('com_contentbuilderng.listLimitField', {})
        : {};
    const choices = Array.isArray(config.choices)
        ? config.choices.map(Number).filter((value) => Number.isInteger(value) && value >= 0)
        : [];

    function format(pattern, value) {
        return String(pattern).includes('%s')
            ? String(pattern).replace('%s', String(value))
            : `${pattern} ${value}`;
    }

    function fields(control) {
        return {
            display: control.querySelector('input[type="text"]'),
            storage: control.querySelector('[data-cb-list-limit-storage]'),
            warning: control.parentElement && control.parentElement.querySelector('[data-cb-list-limit-warning]'),
        };
    }

    function displayValue(control) {
        const { display, storage, warning } = fields(control);
        if (!display || !storage) {
            return;
        }

        const mode = String(control.dataset.mode || 'global');
        const inheritValue = String(control.dataset.inheritValue || '');
        const stored = String(storage.value);
        const numeric = Number.parseInt(stored, 10);

        if (mode !== 'global' && (stored === inheritValue || numeric < 0)) {
            display.value = String(control.dataset.defaultLabel || '');
        } else if (numeric === 0) {
            display.value = String(control.dataset.allLabel || 'All');
        } else if (choices.includes(numeric)) {
            display.value = String(numeric);
        } else {
            display.value = format(control.dataset.customFormat || 'Custom (%s)', numeric);
        }

        if (warning) {
            warning.hidden = numeric !== 0 || (mode !== 'global' && stored === inheritValue);
        }
    }

    function updateStorage(control, value) {
        const { storage } = fields(control);
        if (!storage) {
            return;
        }

        storage.value = String(value);
        storage.dispatchEvent(new Event('input', { bubbles: true }));
        storage.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function commitTypedValue(control) {
        const { display, storage } = fields(control);
        if (!display || !storage) {
            return;
        }

        const typed = display.value.trim();
        const allLabel = String(control.dataset.allLabel || 'All');
        const defaultLabel = String(control.dataset.defaultLabel || '');
        const customMatch = typed.match(/^\D*\(\s*(\d+)\s*\)$/u);

        if (/^\d+$/u.test(typed)) {
            updateStorage(control, Number.parseInt(typed, 10));
        } else if (typed.localeCompare(allLabel, undefined, { sensitivity: 'accent' }) === 0) {
            updateStorage(control, 0);
        } else if (defaultLabel !== '' && typed === defaultLabel) {
            updateStorage(control, control.dataset.inheritValue || '');
        } else if (customMatch) {
            updateStorage(control, Number.parseInt(customMatch[1], 10));
        } else {
            displayValue(control);
        }
    }

    function initialise(control) {
        if (control.dataset.cbListLimitReady === 'true') {
            return;
        }

        const { display, storage } = fields(control);
        if (!display || !storage) {
            return;
        }

        control.dataset.cbListLimitReady = 'true';
        control.querySelectorAll('[data-cb-list-limit-choice]').forEach((item) => {
            item.addEventListener('click', () => updateStorage(control, item.dataset.cbListLimitChoice || ''));
        });
        display.addEventListener('focus', () => {
            const numeric = Number.parseInt(storage.value, 10);
            if (Number.isInteger(numeric) && numeric > 0 && !choices.includes(numeric)) {
                display.value = String(numeric);
                display.select();
            }
        });
        display.addEventListener('change', () => commitTypedValue(control));
        display.addEventListener('blur', () => commitTypedValue(control));
        storage.addEventListener('input', () => displayValue(control));
        storage.addEventListener('change', () => displayValue(control));
        displayValue(control);
    }

    function initialiseAll(root) {
        root.querySelectorAll('[data-cb-list-limit-control]').forEach(initialise);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initialiseAll(document), { once: true });
    } else {
        initialiseAll(document);
    }
}());

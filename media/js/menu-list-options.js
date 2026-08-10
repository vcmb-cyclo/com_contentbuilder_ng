(function () {
    'use strict';

    const config = window.Joomla && typeof window.Joomla.getOptions === 'function'
        ? window.Joomla.getOptions('com_contentbuilderng.menuListOptions', {})
        : {};
    const defaultsByForm = config.defaultsByForm || {};
    const useDefaultFormat = String(config.useDefaultFormat || 'Use Default (%s)');
    let resetting = false;

    function formatDefault(value) {
        return useDefaultFormat.includes('%s')
            ? useDefaultFormat.replace('%s', String(value))
            : `${useDefaultFormat} ${value}`;
    }

    function updateInheritedValues(formId) {
        const defaults = defaultsByForm[String(formId)] || {};

        document.querySelectorAll('[data-cb-menu-default-key]').forEach((field) => {
            const key = String(field.dataset.cbMenuDefaultKey || '');
            let value = defaults[key] ?? (key === 'cb_list_limit' ? config.globalListLimit : '');

            if (field.tagName === 'SELECT') {
                const inheritValue = String(field.dataset.cbMenuInheritValue ?? '-1');
                const option = Array.from(field.options).find((item) => String(item.value) === inheritValue);

                if (option) {
                    if (value === 1) {
                        value = config.yesLabel || 'Yes';
                    } else if (value === 0) {
                        value = config.noLabel || 'No';
                    }

                    option.textContent = formatDefault(value);
                }
            } else if (field.type === 'number') {
                field.placeholder = formatDefault(value);
            } else if (field.matches('[data-cb-list-limit-storage]')) {
                const inheritedValue = Number(value);
                const inheritedLabel = inheritedValue === 0 ? (config.allLabel || 'All') : value;
                field.closest('[data-cb-list-limit-control]')?.setAttribute('data-inherited', String(inheritedValue));
                field.closest('[data-cb-list-limit-control]')?.setAttribute('data-default-label', formatDefault(inheritedLabel));
                dispatchChange(field);
            }
        });
    }

    function dispatchChange(field) {
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function resetValue(field) {
        if (field.tagName === 'SELECT' && field.dataset.cbMenuInheritValue !== undefined) {
            field.value = field.dataset.cbMenuInheritValue;
        } else {
            field.value = field.dataset.cbMenuResetValue || '';
        }

        dispatchChange(field);

        if (field.name) {
            field.dataset.cbMenuOriginalName = field.name;
            field.removeAttribute('name');
        }
    }

    function resetOverrides(button) {
        const confirmText = String(button.dataset.cbMenuResetConfirm || '');

        if (confirmText && !window.confirm(confirmText)) {
            return;
        }

        let names = [];

        try {
            names = JSON.parse(button.dataset.cbMenuResetNames || '[]');
        } catch (error) {
            names = [];
        }

        resetting = true;
        names.forEach((name) => {
            const selector = [
                `[name="jform[params][${window.CSS.escape(name)}]"]`,
                `[name="jform[params][settings][${window.CSS.escape(name)}]"]`,
            ].join(',');

            document.querySelectorAll(selector).forEach(resetValue);
        });

        document.querySelectorAll('[data-cb-menu-filter-fields] input').forEach((field) => {
            field.value = '';
            dispatchChange(field);
        });

        if (typeof window.cb_value === 'object') {
            window.cb_value = {};
        }

        if (typeof window.cb_value_order === 'object') {
            window.cb_value_order = {};
        }
        resetting = false;
    }

    function restoreSubmittedName(event) {
        const field = event.target;

        if (resetting || !field.dataset || !field.dataset.cbMenuOriginalName) {
            return;
        }

        field.name = field.dataset.cbMenuOriginalName;
        delete field.dataset.cbMenuOriginalName;
    }

    function init() {
        const viewSelector = document.querySelector('[data-cb-menu-view-selector]');

        if (viewSelector) {
            updateInheritedValues(viewSelector.value);
            viewSelector.addEventListener('change', () => {
                updateInheritedValues(viewSelector.value);

                if (typeof window.contentbuilderng_setFormId === 'function') {
                    window.contentbuilderng_setFormId(viewSelector.value);
                }
            });
        }

        document.querySelectorAll('[data-cb-menu-reset-overrides]').forEach((button) => {
            button.addEventListener('click', () => resetOverrides(button));
        });
        document.addEventListener('input', restoreSubmittedName, true);
        document.addEventListener('change', restoreSubmittedName, true);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
}());

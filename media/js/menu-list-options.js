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

        document.querySelectorAll('[data-cb-view-default-key]').forEach((field) => {
            const key = String(field.dataset.cbViewDefaultKey || '');
            const option = Array.from(field.options || []).find((item) => String(item.value) === 'default');

            if (!option || defaults[key] === undefined) {
                return;
            }

            const value = Number(defaults[key]) === 1
                ? (config.yesLabel || 'Yes')
                : (config.noLabel || 'No');
            option.textContent = formatDefault(value);
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

    function initialiseListBuilder(root) {
        if (root.dataset.cbListBuilderReady === '1') {
            return;
        }

        const storage = document.getElementById(String(root.dataset.cbConfigInput || ''));

        if (!storage) {
            return;
        }

        root.dataset.cbListBuilderReady = '1';
        let state = {};
        let writingStorage = false;

        const readState = () => {
            try {
                const parsed = JSON.parse(storage.value || '{}');
                state = parsed && typeof parsed === 'object' && !Array.isArray(parsed) ? parsed : {};
            } catch (error) {
                state = {};
            }
        };

        const setPath = (path, value) => {
            const parts = path.split('.');
            let target = state;

            while (parts.length > 1) {
                const part = parts.shift();
                target[part] = target[part] && typeof target[part] === 'object' ? target[part] : {};
                target = target[part];
            }

            target[parts[0]] = value;
        };

        const getPath = (path) => {
            let value = state;
            path.split('.').forEach((part) => {
                value = value && typeof value === 'object' ? value[part] : undefined;
            });

            return value;
        };

        const setCapabilityState = (field, disabled, available) => {
            field.disabled = disabled;
            const cell = field.closest('.cb-menu-capability-cell');

            if (!cell) {
                return;
            }

            cell.classList.toggle('is-inherited', disabled && available);
            cell.classList.toggle('is-unavailable', disabled && !available);
            cell.title = disabled
                ? String(available
                    ? root.dataset.cbInheritedControlTitle || ''
                    : root.dataset.cbUnavailableControlTitle || '')
                : '';
        };

        const refresh = () => {
            root.querySelectorAll('[data-cb-show-when]').forEach((element) => {
                const [path, value] = element.dataset.cbShowWhen.split(':');
                element.hidden = String(getPath(path) ?? '') !== value;
            });

            const customColumns = state.columnsMode === 'custom';
            root.querySelectorAll('[data-cb-column]').forEach((field) => {
                const row = field.closest('tr');
                const locallyPublished = row?.querySelector('[data-cb-published-field]')?.checked !== false;
                const available = row?.dataset.canList === '1' && locallyPublished;
                setCapabilityState(field, !customColumns || !available, available);
            });
            root.querySelectorAll('[data-cb-search-field]').forEach((field) => {
                const row = field.closest('tr');
                const locallyPublished = row?.querySelector('[data-cb-published-field]')?.checked !== false;
                const available = row?.dataset.canSearch === '1' && locallyPublished;
                setCapabilityState(field, !customColumns || !available, available);
            });
            root.querySelectorAll('[data-cb-link-field]').forEach((field) => {
                const row = field.closest('tr');
                const locallyPublished = row?.querySelector('[data-cb-published-field]')?.checked !== false;
                const available = row?.dataset.canLink === '1' && locallyPublished;
                setCapabilityState(field, !customColumns || !available, available);
            });
            ['detail', 'edit'].forEach((capability) => {
                root.querySelectorAll(`[data-cb-${capability}-field]`).forEach((field) => {
                    const row = field.closest('tr');
                    const locallyPublished = row?.querySelector('[data-cb-published-field]')?.checked !== false;
                    const available = row?.dataset[`can${capability[0].toUpperCase()}${capability.slice(1)}`] === '1'
                        && locallyPublished;
                    setCapabilityState(field, !customColumns || !available, available);
                });
            });
            root.querySelectorAll('[data-cb-published-field]').forEach((field) => {
                const available = field.closest('tr')?.dataset.canPublished === '1';
                setCapabilityState(field, !customColumns || !available, available);
            });
            root.querySelectorAll('[data-cb-filter]').forEach((field) => {
                const row = field.closest('tr');
                field.disabled = row?.dataset.canPublished !== '1'
                    || (customColumns && row?.querySelector('[data-cb-published-field]')?.checked === false);
            });
            root.querySelectorAll('.form-select-color-state').forEach((field) => {
                field.classList.toggle('form-select-success', field.value === 'yes' || field.value === '1');
                field.classList.toggle('form-select-danger', field.value === 'no' || field.value === '0' || field.value === 'disabled');
            });
        };

        const sync = () => {
            root.querySelectorAll('[data-cb-key]').forEach((field) => {
                setPath(field.dataset.cbKey, field.type === 'number' ? Number(field.value || 0) : field.value);
            });
            state.searchFields = Array.from(root.querySelectorAll('[data-cb-search-field]:checked')).map((field) => field.value);
            state.linkFields = Array.from(root.querySelectorAll('[data-cb-link-field]:checked')).map((field) => field.value);
            state.detailFields = Array.from(root.querySelectorAll('[data-cb-detail-field]:checked')).map((field) => field.value);
            state.editFields = Array.from(root.querySelectorAll('[data-cb-edit-field]:checked')).map((field) => field.value);
            state.publishedFields = Array.from(root.querySelectorAll('[data-cb-published-field]:checked')).map((field) => field.value);
            state.columns = Array.from(root.querySelectorAll('[data-cb-column]:checked')).map((field) => field.value);
            state.filters = {};
            root.querySelectorAll('[data-cb-column-row]').forEach((row) => {
                const filter = row.querySelector('[data-cb-filter]');
                const value = filter ? filter.value.trim() : '';
                if (value) {
                    state.filters[row.dataset.reference] = value;
                }
            });
            state.sort = [];
            for (let index = 0; index < 3; index += 1) {
                const field = root.querySelector(`[data-cb-sort-field="${index}"]`);
                const direction = root.querySelector(`[data-cb-sort-dir="${index}"]`);
                if (field && direction && field.value) {
                    state.sort.push({ field: field.value, dir: direction.value });
                }
            }

            writingStorage = true;
            storage.value = JSON.stringify(state);
            storage.dispatchEvent(new Event('input', { bubbles: true }));
            storage.dispatchEvent(new Event('change', { bubbles: true }));
            writingStorage = false;
            refresh();
        };

        const applyState = () => {
            root.querySelectorAll('[data-cb-key]').forEach((field) => {
                const value = getPath(field.dataset.cbKey);
                if (value !== undefined) {
                    field.value = String(value);
                }
            });

            const hasSearchFields = Array.isArray(state.searchFields);
            const searchFields = new Set(hasSearchFields ? state.searchFields.map(String) : []);
            root.querySelectorAll('[data-cb-search-field]').forEach((field) => {
                field.checked = field.closest('tr')?.dataset.canSearch === '1' && (state.columnsMode === 'custom'
                    ? (hasSearchFields ? searchFields.has(String(field.value)) : field.dataset.viewDefault === '1')
                    : field.dataset.viewDefault === '1');
            });

            const hasLinkFields = Array.isArray(state.linkFields);
            const linkFields = new Set(hasLinkFields ? state.linkFields.map(String) : []);
            root.querySelectorAll('[data-cb-link-field]').forEach((field) => {
                field.checked = field.closest('tr')?.dataset.canLink === '1' && (state.columnsMode === 'custom'
                    ? (hasLinkFields ? linkFields.has(String(field.value)) : field.dataset.viewDefault === '1')
                    : field.dataset.viewDefault === '1');
            });

            ['detail', 'edit', 'published'].forEach((capability) => {
                const stateKey = `${capability}Fields`;
                const hasFields = Array.isArray(state[stateKey]);
                const fields = new Set(hasFields ? state[stateKey].map(String) : []);
                root.querySelectorAll(`[data-cb-${capability}-field]`).forEach((field) => {
                    const row = field.closest('tr');
                    const allowed = row?.dataset[`can${capability[0].toUpperCase()}${capability.slice(1)}`] === '1';
                    field.checked = allowed && (state.columnsMode === 'custom'
                        ? (hasFields ? fields.has(String(field.value)) : field.dataset.viewDefault === '1')
                        : field.dataset.viewDefault === '1');
                });
            });

            const hasColumns = Array.isArray(state.columns);
            const columns = new Set(hasColumns ? state.columns.map(String) : []);
            root.querySelectorAll('[data-cb-column]').forEach((field) => {
                field.checked = field.closest('tr')?.dataset.canList === '1' && (state.columnsMode === 'custom'
                    ? (hasColumns ? columns.has(String(field.value)) : field.dataset.viewDefault === '1')
                    : field.dataset.viewDefault === '1');
            });

            const filters = state.filters && typeof state.filters === 'object' ? state.filters : {};
            root.querySelectorAll('[data-cb-column-row]').forEach((row) => {
                const filter = row.querySelector('[data-cb-filter]');
                if (filter) {
                    filter.value = String(filters[row.dataset.reference] || '');
                }
            });
            refresh();
        };

        readState();
        applyState();
        root.addEventListener('input', sync);
        root.addEventListener('change', sync);
        root.addEventListener('click', (event) => {
            const button = event.target.closest('[data-cb-move]');
            if (!button || state.columnsMode !== 'custom') {
                return;
            }

            const row = button.closest('tr');
            const sibling = button.dataset.cbMove === 'up' ? row.previousElementSibling : row.nextElementSibling;
            if (!sibling) {
                return;
            }
            if (button.dataset.cbMove === 'up') {
                row.parentNode.insertBefore(row, sibling);
            } else {
                row.parentNode.insertBefore(sibling, row);
            }
            sync();
        });

        const search = root.querySelector('[data-cb-field-search]');
        if (search) {
            search.addEventListener('input', () => {
                const query = search.value.trim().toLocaleLowerCase();
                root.querySelectorAll('[data-cb-column-row]').forEach((row) => {
                    row.hidden = query !== '' && !row.dataset.label.includes(query);
                });
            });
        }

        storage.addEventListener('change', () => {
            if (!writingStorage) {
                readState();
                applyState();
            }
        });
        storage.closest('form')?.addEventListener('submit', () => {
            if (!storage.dataset.cbMenuOriginalName) {
                sync();
            }
        }, true);
    }

    function initListBuilders() {
        document.querySelectorAll('[data-cb-new-list-builder]').forEach(initialiseListBuilder);
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
        initListBuilders();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init, { once: true });
    } else {
        init();
    }
}());

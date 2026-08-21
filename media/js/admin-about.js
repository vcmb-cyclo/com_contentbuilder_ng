(function () {
    'use strict';

    function requestFailedMessage() {
        if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
            return window.Joomla.Text._('COM_CONTENTBUILDERNG_ABOUT_AUDIT_AJAX_REQUEST_FAILED');
        }

        return 'The audit action could not be completed.';
    }

    function refreshFailedMessage() {
        if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
            return window.Joomla.Text._('COM_CONTENTBUILDERNG_ABOUT_AUDIT_REFRESH_FAILED');
        }

        return 'The audit display could not be refreshed.';
    }

    function renderMessage(type, message) {
        if (window.Joomla && typeof window.Joomla.renderMessages === 'function') {
            var messages = {};
            messages[type] = [message];
            window.Joomla.renderMessages(messages);
            return;
        }

        window.alert(message);
    }

    function initializeTooltips() {
        if (!window.bootstrap || typeof window.bootstrap.Tooltip !== 'function') {
            return;
        }

        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
            if (!window.bootstrap.Tooltip.getInstance(element)) {
                new window.bootstrap.Tooltip(element);
            }
        });
    }

    function initializeEasterEgg() {
        var hotspot = document.querySelector('[data-cb-easter-egg-hotspot]');
        var overlay = document.querySelector('[data-cb-easter-egg]');
        var image = overlay ? overlay.querySelector('[data-cb-easter-egg-image]') : null;
        var closeButton = overlay ? overlay.querySelector('[data-cb-easter-egg-close]') : null;
        var clickCount = 0;
        var resetTimer = 0;

        if (!hotspot || !overlay || !image || !closeButton) {
            return;
        }

        var close = function () {
            overlay.hidden = true;
            overlay.setAttribute('aria-hidden', 'true');
            image.removeAttribute('src');
            document.body.classList.remove('cb-easter-egg-open');
        };

        var open = function () {
            var source = String(image.dataset.src || '');
            image.src = source + (source.indexOf('?') === -1 ? '?' : '&') + 'play=' + Date.now();
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
            document.body.classList.add('cb-easter-egg-open');
            closeButton.focus();
        };

        hotspot.addEventListener('click', function () {
            clickCount += 1;
            window.clearTimeout(resetTimer);
            resetTimer = window.setTimeout(function () {
                clickCount = 0;
            }, 3000);

            if (clickCount < 5) {
                return;
            }

            clickCount = 0;
            window.clearTimeout(resetTimer);
            open();
        });

        closeButton.addEventListener('click', close);
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                close();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !overlay.hidden) {
                close();
            }
        });
    }

    function refreshAuditSection() {
        var auditSection = document.getElementById('cb-audit-section');

        if (!auditSection) {
            return Promise.resolve();
        }

        return fetch(window.location.href, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error(refreshFailedMessage());
                }

                return response.text();
            })
            .then(function (html) {
                var parsedDocument = new DOMParser().parseFromString(html, 'text/html');
                var refreshedAuditSection = parsedDocument.getElementById('cb-audit-section');

                if (!refreshedAuditSection) {
                    throw new Error(refreshFailedMessage());
                }

                auditSection.replaceWith(refreshedAuditSection);
                initializeTooltips();
            });
    }

    function removeCompletedAction(button) {
        var item = button.closest('li');

        if (item && button.dataset.cbAuditAjaxTask === 'about.deleteStaleInstallerTemp') {
            item.remove();
            return;
        }

        var warning = button.closest('.cb-audit-warning-alert');
        if (warning) {
            warning.remove();
        }
    }

    function executeAuditAction(button) {
        var form = document.getElementById('adminForm');
        var field = button.dataset.cbAuditAjaxField || '';
        var value = button.dataset.cbAuditAjaxValue || '';
        var task = button.dataset.cbAuditAjaxTask || '';
        var checkboxSelector = button.dataset.cbAuditAjaxCheckboxSelector || '';

        if (!form || field === '' || task === '') {
            return;
        }

        var formData = new FormData(form);
        formData.set('task', task);
        if (checkboxSelector !== '') {
            formData.delete(field);
            formData.delete(field + '[]');
            document.querySelectorAll(checkboxSelector + ':checked').forEach(function (checkbox) {
                formData.append(field + '[]', checkbox.value);
            });
        } else {
            formData.set(field, value);
        }
        formData.set('cb_ajax', '1');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        fetch(form.action + (form.action.indexOf('?') === -1 ? '?' : '&') + 'cb_ajax=1', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    return {response: response, payload: payload};
                });
            })
            .then(function (result) {
                var payload = result.payload || {};
                var success = payload.success === true || payload.ok === true;
                var message = payload.message || requestFailedMessage();

                if (!result.response.ok || !success) {
                    throw new Error(message);
                }

                renderMessage('success', message);
                return refreshAuditSection().catch(function () {
                    removeCompletedAction(button);
                    renderMessage('warning', refreshFailedMessage());
                });
            })
            .catch(function (error) {
                renderMessage('error', error.message || requestFailedMessage());
                button.disabled = false;
                button.removeAttribute('aria-busy');
            });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-cb-audit-ajax-task]');

        if (!button) {
            return;
        }

        event.preventDefault();
        executeAuditAction(button);
    });

    document.addEventListener('change', function (event) {
        var target = event.target;

        if (!(target instanceof HTMLInputElement)) {
            return;
        }

        var group = target.dataset.cbSelectAll || target.dataset.cbSelectItem || '';

        if (group === '') {
            return;
        }

        var masterSelector = '[data-cb-select-all="' + group + '"]';
        var itemSelector = '[data-cb-select-item="' + group + '"]';

        if (target.matches(masterSelector)) {
            document.querySelectorAll(itemSelector).forEach(function (checkbox) {
                checkbox.checked = target.checked;
            });
        }

        var masters = document.querySelectorAll(masterSelector);
        var items = document.querySelectorAll(itemSelector);
        var checkedCount = document.querySelectorAll(itemSelector + ':checked').length;

        masters.forEach(function (master) {
            master.checked = items.length > 0 && checkedCount === items.length;
            master.indeterminate = checkedCount > 0 && checkedCount < items.length;
        });
    });

    initializeTooltips();
    initializeEasterEgg();
}());

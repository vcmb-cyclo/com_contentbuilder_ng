(function () {
    'use strict';

    function requestFailedMessage() {
        if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
            return window.Joomla.Text._('COM_CONTENTBUILDERNG_ABOUT_AUDIT_AJAX_REQUEST_FAILED');
        }

        return 'The audit action could not be completed.';
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

        if (!form || field === '' || task === '') {
            return;
        }

        var formData = new FormData(form);
        formData.set('task', task);
        formData.set(field, value);
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
                removeCompletedAction(button);
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

    initializeTooltips();
}());

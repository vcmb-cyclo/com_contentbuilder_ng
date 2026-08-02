(function () {
    'use strict';

    function localizedText(key, fallback) {
        if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
            return window.Joomla.Text._(key);
        }

        return fallback;
    }

    function scrollMessagesIntoView() {
        // The Audit tab sits deep in a long tabbed edit form; Joomla's own
        // renderMessages() only injects into #system-message-container, it
        // never scrolls to it, so a repair result following an AJAX click
        // down the page rendered off-screen above the current scroll
        // position and went unnoticed.
        var messageContainer = document.getElementById('system-message-container');

        if (messageContainer && typeof messageContainer.scrollIntoView === 'function') {
            messageContainer.scrollIntoView({behavior: 'smooth', block: 'start'});
        }
    }

    function renderMessage(type, message) {
        if (window.Joomla && typeof window.Joomla.renderMessages === 'function') {
            var messages = {};
            messages[type] = [message];
            window.Joomla.renderMessages(messages);
            scrollMessagesIntoView();
            return;
        }

        window.alert(message);
    }

    function refreshAudit() {
        var currentAudit = document.querySelector('[data-cb-form-audit-panel]');

        if (!currentAudit) {
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
                    throw new Error(localizedText(
                        'COM_CONTENTBUILDERNG_AUDIT_REFRESH_FAILED',
                        'The audit display could not be refreshed.'
                    ));
                }

                return response.text();
            })
            .then(function (html) {
                var parsedDocument = new DOMParser().parseFromString(html, 'text/html');
                var refreshedAudit = parsedDocument.querySelector('[data-cb-form-audit-panel]');

                if (!refreshedAudit) {
                    throw new Error(localizedText(
                        'COM_CONTENTBUILDERNG_AUDIT_REFRESH_FAILED',
                        'The audit display could not be refreshed.'
                    ));
                }

                currentAudit.replaceWith(refreshedAudit);
            });
    }

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('[data-cb-form-audit-repair]');

        if (!form) {
            return;
        }

        event.preventDefault();

        var button = event.submitter || form.querySelector('button[type="submit"]');
        var formData = new FormData(form);
        formData.set('cb_ajax', '1');

        if (button) {
            formData.set('theme_plugin', button.value || 'thoth');
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    return {response: response, payload: payload || {}};
                });
            })
            .then(function (result) {
                var payload = result.payload;
                var success = payload.success === true || payload.ok === true;
                var message = payload.message || localizedText(
                    'COM_CONTENTBUILDERNG_AUDIT_AJAX_REQUEST_FAILED',
                    'The audit action could not be completed.'
                );

                if (!result.response.ok || !success) {
                    throw new Error(message);
                }

                return refreshAudit().then(function () {
                    renderMessage('success', message);
                });
            })
            .catch(function (error) {
                renderMessage('error', error.message || localizedText(
                    'COM_CONTENTBUILDERNG_AUDIT_AJAX_REQUEST_FAILED',
                    'The audit action could not be completed.'
                ));

                if (button) {
                    button.disabled = false;
                    button.removeAttribute('aria-busy');
                }
            });
    });

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-cb-form-audit-repair-button]');

        if (!button) {
            return;
        }

        var form = document.getElementById('adminForm');

        if (!form) {
            return;
        }

        event.preventDefault();

        var formData = new FormData(form);
        formData.set('task', button.dataset.cbFormAuditTask || 'form.repairEditableTemplate');
        formData.set('id', button.dataset.cbFormAuditId || '0');

        if (button.dataset.cbFormAuditField) {
            formData.set('field_name', button.dataset.cbFormAuditField);
        }

        if (button.dataset.cbFormAuditTheme) {
            formData.set('theme_plugin', button.dataset.cbFormAuditTheme);
        }

        formData.set('cb_ajax', '1');
        button.disabled = true;
        button.setAttribute('aria-busy', 'true');

        fetch(form.action, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json().then(function (payload) {
                    return {response: response, payload: payload || {}};
                });
            })
            .then(function (result) {
                var payload = result.payload;
                var success = payload.success === true || payload.ok === true;
                var message = payload.message || localizedText(
                    'COM_CONTENTBUILDERNG_AUDIT_AJAX_REQUEST_FAILED',
                    'The audit action could not be completed.'
                );

                if (!result.response.ok || !success) {
                    throw new Error(message);
                }

                return refreshAudit().then(function () {
                    renderMessage('success', message);
                });
            })
            .catch(function (error) {
                renderMessage('error', error.message || localizedText(
                    'COM_CONTENTBUILDERNG_AUDIT_AJAX_REQUEST_FAILED',
                    'The audit action could not be completed.'
                ));
                button.disabled = false;
                button.removeAttribute('aria-busy');
            });
    });
}());

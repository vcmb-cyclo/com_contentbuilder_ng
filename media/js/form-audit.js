(function () {
    'use strict';

    function localizedText(key, fallback) {
        if (window.Joomla && window.Joomla.Text && typeof window.Joomla.Text._ === 'function') {
            return window.Joomla.Text._(key);
        }

        return fallback;
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

    function refreshAudit() {
        var currentAudit = document.querySelector('.p-3');

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
                var refreshedAudit = parsedDocument.querySelector('.p-3');

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
}());

(() => {
    'use strict';

    const observers = new WeakMap();

    const resizeFrame = (frame) => {
        try {
            const document = frame.contentDocument;
            const body = document?.body;
            const root = document?.documentElement;

            if (!body || !root) {
                return;
            }

            const errorsOnly = document.querySelector('[data-cblist-errors-only]');
            if (errorsOnly) {
                const bodyStyle = document.defaultView?.getComputedStyle(body);
                const bottomMargin = Number.parseFloat(bodyStyle?.marginBottom || '0');
                const height = Math.max(
                    1,
                    Math.ceil(errorsOnly.getBoundingClientRect().bottom + bottomMargin)
                );

                if (Math.abs(frame.getBoundingClientRect().height - height) > 1) {
                    frame.style.height = `${height}px`;
                }

                return;
            }

            const minimum = Number.parseInt(frame.dataset.cblistMinHeight || '240', 10);
            const height = Math.max(
                Number.isFinite(minimum) ? minimum : 240,
                body.scrollHeight,
                body.offsetHeight,
                root.scrollHeight,
                root.offsetHeight
            );

            if (Math.abs(frame.getBoundingClientRect().height - height) > 1) {
                frame.style.height = `${height}px`;
            }
        } catch {
            // The list normally uses the same origin. Keep the configured
            // fallback height if a site-level policy prevents frame access.
        }
    };

    const observeFrame = (frame) => {
        observers.get(frame)?.disconnect();
        resizeFrame(frame);

        try {
            const root = frame.contentDocument?.documentElement;
            if (!root || typeof ResizeObserver === 'undefined') {
                return;
            }

            const observer = new ResizeObserver(() => resizeFrame(frame));
            observer.observe(root);
            observers.set(frame, observer);
        } catch {
            // Keep the configured fallback height.
        }
    };

    const initialise = (root = document) => {
        root.querySelectorAll('.cblist-embed__frame').forEach((frame) => {
            if (frame.dataset.cblistInitialised === '1') {
                return;
            }

            frame.dataset.cblistInitialised = '1';
            frame.addEventListener('load', () => observeFrame(frame));

            if (frame.contentDocument?.readyState === 'complete') {
                observeFrame(frame);
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initialise());
    } else {
        initialise();
    }

    document.addEventListener('joomla:updated', (event) => {
        initialise(event.target instanceof Element ? event.target : document);
    });
})();

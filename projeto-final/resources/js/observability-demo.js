const demo = document.querySelector('[data-observability-demo]');

if (demo) {
    const output = demo.querySelector('[data-output]');
    const requestId = () => crypto.randomUUID();

    const showResult = (title, response, body, duration) => {
        output.textContent = JSON.stringify({
            flow: title,
            status: response.status,
            request_id: response.headers.get('X-Request-ID'),
            duration_ms: Math.round(duration),
            response: body,
        }, null, 2);
    };

    demo.querySelectorAll('[data-request]').forEach(button => {
        button.addEventListener('click', async () => {
            button.disabled = true;
            output.textContent = `Executando ${button.dataset.label}...`;
            const startedAt = performance.now();

            try {
                const response = await fetch(button.dataset.url, {
                    method: button.dataset.method ?? 'GET',
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'application/json',
                        'X-Request-ID': requestId(),
                    },
                    body: button.dataset.body,
                });
                const body = await response.json();
                showResult(button.dataset.label, response, body, performance.now() - startedAt);
            } catch (error) {
                output.textContent = JSON.stringify({ error: error.message }, null, 2);
            } finally {
                button.disabled = false;
            }
        });
    });
}

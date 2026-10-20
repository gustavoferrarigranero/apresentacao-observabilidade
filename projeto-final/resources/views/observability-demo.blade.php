<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratório de observabilidade</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="mx-auto max-w-5xl px-6 py-12" data-observability-demo>
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-400">ClinicFlow</p>
        <h1 class="mt-3 text-4xl font-semibold">Laboratório de observabilidade</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-300">
            Execute os cenários da apresentação para comparar queries, correlacionar requests, registrar falhas e acompanhar jobs.
        </p>

        <section class="mt-10 grid gap-4 md:grid-cols-2">
            <button class="rounded-xl bg-rose-500 px-5 py-4 text-left font-semibold text-white" data-request data-label="Agenda com N+1" data-url="{{ url('/api/clinics/'.$clinic->id.'/schedule/slow') }}">
                Executar agenda lenta
            </button>
            <button class="rounded-xl bg-emerald-500 px-5 py-4 text-left font-semibold text-slate-950" data-request data-label="Agenda otimizada" data-url="{{ url('/api/clinics/'.$clinic->id.'/schedule') }}">
                Executar agenda otimizada
            </button>
            <button class="rounded-xl bg-amber-400 px-5 py-4 text-left font-semibold text-slate-950" data-request data-label="Falha estruturada de webhook" data-method="POST" data-url="{{ url('/api/clinics/'.$clinic->id.'/webhook-failures') }}" data-body='{"delivery_id":"demo-delivery","provider":"clinic-partner","status_code":504,"duration_ms":4800}'>
                Registrar falha de webhook
            </button>
            <button class="rounded-xl bg-cyan-400 px-5 py-4 text-left font-semibold text-slate-950" data-request data-label="Jobs de lembrete" data-method="POST" data-url="{{ url('/api/observability/reminders/dispatch') }}">
                Despachar jobs por clínica
            </button>
        </section>

        <section class="mt-8 rounded-xl border border-slate-800 bg-slate-900 p-6">
            <h2 class="text-xl font-semibold">Resultado do último fluxo</h2>
            <pre class="mt-4 overflow-auto whitespace-pre-wrap text-sm text-cyan-200" data-output>Escolha um cenário para começar.</pre>
        </section>

        <nav class="mt-8 flex gap-6 text-cyan-300">
            <a class="underline" href="{{ url('/telescope') }}" target="_blank">Abrir Telescope</a>
            <a class="underline" href="{{ url('/pulse') }}" target="_blank">Abrir Pulse</a>
        </nav>

        <p class="mt-6 text-sm text-slate-400">
            Clínica de teste: #{{ $clinic->id }} — {{ $clinic->name }}. Execute o worker da fila para concluir os jobs de lembrete.
        </p>
    </main>
</body>
</html>

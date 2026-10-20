<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laboratório de observabilidade</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100">
    <main class="mx-auto max-w-5xl px-6 py-12">
        <p class="text-sm font-semibold uppercase tracking-[0.25em] text-cyan-400">ClinicFlow</p>
        <h1 class="mt-3 text-4xl font-semibold">Laboratório de observabilidade</h1>
        <p class="mt-4 max-w-3xl text-lg text-slate-300">
            A stack está pronta. Complete cada artefato durante a aula e transforme os cenários abaixo em fluxos executáveis.
        </p>

        <section class="mt-10 grid gap-4 md:grid-cols-2">
            <article class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <p class="text-sm text-cyan-400">Etapa 1</p>
                <h2 class="mt-2 text-xl font-semibold">Correlação de requests</h2>
                <p class="mt-3 text-slate-300">Complete o middleware `CorrelateRequest` e devolva `X-Request-ID` na resposta.</p>
            </article>
            <article class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <p class="text-sm text-cyan-400">Etapa 2</p>
                <h2 class="mt-2 text-xl font-semibold">N+1 e eager loading</h2>
                <p class="mt-3 text-slate-300">Implemente as versões lenta e otimizada da agenda e compare as queries.</p>
            </article>
            <article class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <p class="text-sm text-cyan-400">Etapa 3</p>
                <h2 class="mt-2 text-xl font-semibold">Log estruturado</h2>
                <p class="mt-3 text-slate-300">Registre a falha do webhook com identificadores operacionais e sem dados sensíveis.</p>
            </article>
            <article class="rounded-xl border border-slate-800 bg-slate-900 p-6">
                <p class="text-sm text-cyan-400">Etapa 4</p>
                <h2 class="mt-2 text-xl font-semibold">Jobs por clínica</h2>
                <p class="mt-3 text-slate-300">Implemente chunking, fila dedicada, retry, rate limit, correlação e idempotência.</p>
            </article>
        </section>

        <nav class="mt-8 flex gap-6 text-cyan-300">
            <a class="underline" href="{{ url('/telescope') }}" target="_blank">Abrir Telescope</a>
            <a class="underline" href="{{ url('/pulse') }}" target="_blank">Abrir Pulse</a>
        </nav>
    </main>
</body>
</html>

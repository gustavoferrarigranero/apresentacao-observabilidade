# Roteiro de aula e testes — Monitorização e Observabilidade

Este projeto implementa o resultado final da aula: Telescope e Pulse configurados, correlação por `request_id`, logs estruturados, comparação mensurável de N+1 e processamento de lembretes por clínica com proteção contra duplicidade.

## Preparação

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan test
```

Mantenha estes processos em terminais separados:

```bash
php artisan serve
php artisan queue:work --queue=reminders --tries=3
php artisan schedule:work
```

Abra `http://127.0.0.1:8000/demo/observability` para executar todos os cenários por uma única interface. Cada ação mostra status HTTP, duração, `request_id` e resposta recebida.

## Telescope e Pulse

Abra:

- `http://127.0.0.1:8000/telescope`
- `http://127.0.0.1:8000/pulse`

O Telescope permite inspecionar cada request, suas queries, logs e jobs. O Pulse mostra tendências de rotas lentas, queries e filas. Gere tráfego antes de interpretar o dashboard.

## Experimento 1 — Correlação do request

```bash
curl -i \
  -H 'Accept: application/json' \
  -H 'X-Request-ID: aula-observabilidade-001' \
  http://127.0.0.1:8000/api/clinics/1/schedule
```

Confirme:

- resposta com `X-Request-ID: aula-observabilidade-001`;
- log `clinic_schedule_loaded` com `request_id`, `clinic_id` e total de agendamentos;
- request localizável no Telescope.

## Experimento 2 — N+1 antes e depois

Execute a versão lenta:

```bash
curl -s \
  -H 'X-Request-ID: aula-nmais1-lento' \
  http://127.0.0.1:8000/api/clinics/1/schedule/slow > /dev/null
```

Execute a versão otimizada:

```bash
curl -s \
  -H 'X-Request-ID: aula-nmais1-otimizado' \
  http://127.0.0.1:8000/api/clinics/1/schedule > /dev/null
```

No Telescope, compare as queries dos dois requests. A rota lenta consulta pacientes durante a serialização. A rota final usa eager loading e mantém a leitura em até três queries, protegida por teste automatizado.

A rota lenta existe somente nos ambientes `local` e `testing` e adiciona uma espera controlada por registro. Isso mantém a degradação visível mesmo com SQLite local, sem contaminar a implementação otimizada.

## Experimento 3 — Falha de webhook com log estruturado

```bash
curl -i -X POST \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'X-Request-ID: aula-webhook-001' \
  -d '{"delivery_id":"delivery-10","provider":"clinic-partner","status_code":504,"duration_ms":4800}' \
  http://127.0.0.1:8000/api/clinics/1/webhook-failures
```

Localize `webhook_delivery_failed`. O registro deve conter somente identificadores operacionais, status e duração. Tokens, credenciais, dados pessoais e payloads completos ficam fora do log.

## Experimento 4 — Job pesado dividido por clínica

```bash
php artisan reminders:dispatch --request-id=00000000-0000-4000-8000-000000000011
php artisan queue:work --queue=reminders --stop-when-empty --tries=3
```

Confirme:

- um job `SendClinicReminders` por clínica;
- fila `reminders`;
- `request_id` preservado nos logs do processamento;
- timeout de 30 segundos e três tentativas;
- rate limit por clínica;
- uma única linha em `reminder_deliveries` por agendamento e provedor, mesmo após repetir o comando.

## Testes automatizados

```bash
php artisan test --filter=ObservabilityFlowTest
php artisan test
vendor/bin/pint --test
npm run build
```

Os testes verificam correlação, log estruturado, redução de queries, divisão dos jobs, fila correta, idempotência, política operacional do job e registro dos dashboards.

## Validação completa em uma execução

```bash
php artisan migrate:fresh --seed
php artisan test
npm run build
php artisan schedule:list
```

Depois, use os quatro botões do laboratório e confira os mesmos requests no Telescope e as tendências no Pulse. O botão de jobs exige que `queue:work` esteja ativo.

## Encerramento didático

Para cada problema, peça aos alunos que registrem:

1. sintoma observado;
2. evidência encontrada;
3. causa identificada;
4. alteração aplicada;
5. medida antes e depois;
6. teste que impede a regressão.

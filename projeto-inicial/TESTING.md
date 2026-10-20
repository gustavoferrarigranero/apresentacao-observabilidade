# Roteiro de implementação — Monitorização e Observabilidade

Este projeto contém o domínio ClinicFlow, Telescope, Pulse, banco SQLite, filas e os artefatos básicos da aula. A solução de observabilidade foi deixada em branco para ser construída com os alunos, sem gastar tempo criando arquivos repetitivos.

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

Inicie a aplicação e os processos de observabilidade:

```bash
php artisan serve
php artisan queue:work --queue=reminders
php artisan schedule:work
```

Abra `http://127.0.0.1:8000/demo/observability` para acompanhar as etapas. Telescope e Pulse já estão disponíveis nos links da página.

## Artefatos preparados

- middleware `CorrelateRequest`;
- controllers `SlowClinicScheduleController`, `ClinicScheduleController`, `WebhookDeliveryController` e `DispatchRemindersController`;
- controller preparado para a versão final do laboratório;
- job `SendClinicReminders`;
- command `DispatchReminders`;
- services `DispatchClinicReminders` e `LogReminderProvider`;
- contrato `ReminderProvider`;
- model e migration de `ReminderDelivery`;
- view e módulo JavaScript do laboratório;
- testes de prontidão da stack.

## Etapa 1 — Telescope e Pulse

1. Acesse os dois dashboards e identifique requests, queries, jobs e tendências.
2. Revise o filtro do `TelescopeServiceProvider` e mantenha dados sensíveis fora da captura.
3. Gere tráfego na API para alimentar os dashboards.

## Etapa 2 — Correlação e logs estruturados

1. Complete `CorrelateRequest` para aceitar ou gerar `X-Request-ID`.
2. Adicione o identificador ao `Context` e ao header da resposta.
3. Complete `WebhookDeliveryController` com campos estáveis de log.
4. Não registre tokens, credenciais, dados pessoais nem payloads completos.

## Etapa 3 — Diagnóstico de N+1

1. Implemente a rota lenta carregando o paciente dentro do loop.
2. Meça as queries no Telescope.
3. Implemente a versão otimizada com eager loading e colunas explícitas.
4. Crie um teste que compare a quantidade de queries antes e depois.

## Etapa 4 — Job pesado

1. Complete `DispatchClinicReminders` com `chunkById` por clínica.
2. Complete `SendClinicReminders` com fila `reminders`, timeout, tentativas e rate limit.
3. Propague o `request_id` para o job.
4. Use `ReminderDelivery` para impedir envios duplicados.
5. Execute `php artisan reminders:dispatch` e processe a fila.

## Validação final

```bash
vendor/bin/pint --test
php artisan migrate:fresh --seed
php artisan test
npm run build
php artisan route:list --path=telescope
php artisan route:list --path=pulse
```

Compare com o projeto final somente depois de concluir a implementação em aula.

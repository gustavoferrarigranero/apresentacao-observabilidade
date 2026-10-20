# Roteiro de aula — Monitorização e observabilidade

## Resultado esperado

Rastrear uma requisição por correlation ID, observar consultas e latência e diagnosticar um fluxo com logs estruturados, Telescope e Pulse.

## Preparação

Em `projeto-inicial`, execute `php artisan migrate:fresh --seed`, `php artisan test` e `php artisan serve`. Acesse `/telescope` e `/pulse` antes de iniciar o exercício.

## Desenvolvimento guiado

1. Crie middleware que aceite ou gere `X-Correlation-ID`.
2. Compartilhe o identificador no contexto de log e na resposta.
3. Instrumente a consulta de agenda sem registrar dados clínicos sensíveis.
4. Compare a consulta sem eager loading com a versão otimizada.
5. Use Telescope para inspecionar request, query e log do mesmo fluxo.
6. Use Pulse para explicar tendência, saturação e decisão operacional.

## Verificação

No projeto final, execute `php artisan migrate:fresh --seed` e `php artisan test --filter=ObservabilityFlowTest`. Envie `X-Correlation-ID: aula-11` para a rota demonstrada e confirme o mesmo valor no header de resposta e no log. O roteiro termina quando um aluno consegue partir de um sintoma, localizar a requisição e explicar a causa com evidência.

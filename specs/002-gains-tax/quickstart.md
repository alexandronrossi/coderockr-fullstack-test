# Quickstart: Ganho composto e imposto

Validação **depois** dos testes e do código. Sem servidor HTTP.

## Prerequisites

- PHP 8.3, Composer
- Branch `feat/gains-tax`

## Automated proof (required)

```bash
php artisan test tests/Unit/Domain
```

Esperado: todos verdes, em menos de dois minutos (SC-005).

Contrato: [contracts/domain.md](./contracts/domain.md). Modelo: [data-model.md](./data-model.md).

## Casos que a suíte deve cobrir

| Caso | Esperado |
|------|----------|
| 1000,00 no dia da criação | saldo 1000,00, ganho 0 |
| 1000,00 + 1 mês civil completo | saldo 1005,20 |
| 1000,00 + 2 meses compostos | 1000 × 1,0052², half-up a centavos por mês |
| Mês incompleto | igual ao último aniversário completo |
| 31/01 ano comum, 1º mês | 28/02 |
| 31/01 ano comum, 2º mês | 31/03 (não 28/03) |
| 29/01 bissexto → fev | 29/02 |
| 30 ou 31/01 bissexto → fev | 29/02 |
| 31/03 → abr | 30/04 |
| 1000 / saldo 1200 / &lt;1 ano | IR 45,00, líquido 1155,00 |
| mesmo ganho, 1–2 anos | IR 37,00 |
| mesmo ganho, &gt;2 anos | IR 30,00 |
| ganho 0 | IR 0 |
| já resgatado em R, asOf &gt; R | saldo de R |
| asOf &lt; criação | InvalidInvestmentDate |

## Fora desta etapa

Não chamar `POST /api/investments`. Health e login permanecem como na spec `001-user-auth`.

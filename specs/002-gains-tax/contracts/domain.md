# Domain contract: compound gain and withdrawal tax

No HTTP in this feature. Callers (tests now; API later) use these operations.

## CivilMonthAnniversary

```
anniversary(createdOn: date, periodIndex: int) -> date
```

- `periodIndex` 0 = `createdOn`
- `periodIndex` N = original date plus N calendar months, day clamped to last day of target month
- MUST compute from `createdOn`, never from `anniversary(N-1)`

## CompoundGainCalculator

```
evaluate(principalCents: int, createdOn: date, asOf: date) -> {
  completeMonths: int,
  expectedBalanceCents: int,
  gainCents: int
}
```

- Rate 0.52% per complete month, compound, round half up to cents per month
- `asOf < createdOn` → InvalidInvestmentDate
- Incomplete month → not counted

## WithdrawalTaxCalculator

```
evaluate(principalCents: int, expectedBalanceCents: int, createdOn: date, taxAsOf: date) -> {
  gainCents: int,
  rate: "0.225" | "0.185" | "0.15",
  taxCents: int,
  netCents: int
}
```

- Tax only on max(expected − principal, 0)
- Age: taxAsOf vs createdOn using calendar years (addYearsNoOverflow)
- &lt; 1 year → 0.225; 1 year ≤ age ≤ 2 years → 0.185; &gt; 2 years → 0.15

Canonical: principal 100000 cents, expected 120000, age &lt; 1 year → tax 4500, net 115500

## InvestmentValuation

```
evaluate(principalCents, createdOn, asOf, withdrawnOn?: date) -> {
  effectiveAsOf: date,
  completeMonths: int,
  expectedBalanceCents: int,
  gainCents: int,
  rate?: ...,
  taxCents: int,
  netCents: int
}
```

- `effectiveAsOf = withdrawnOn ? min(asOf, withdrawnOn) : asOf`
- Gain + tax both use `effectiveAsOf` unless a later API evaluates tax on a different withdrawal date (then call calculators separately)

import { FormEvent, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { create } from '@/api/investments';
import { ApiError } from '@/api/client';
import styles from '@/pages/CreateInvestmentPage.module.css';

export function CreateInvestmentPage() {
  const navigate = useNavigate();
  const today = new Date().toISOString().slice(0, 10);
  const [amount, setAmount] = useState('');
  const [createdOn, setCreatedOn] = useState(today);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [busy, setBusy] = useState(false);

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError(null);
    setFieldErrors({});

    const normalized = amount.trim();
    if (!normalized) {
      setError('Informe o valor do investimento.');
      return;
    }

    if (normalized === '0' || normalized === '0.00' || Number(normalized) <= 0) {
      setError('O valor deve ser maior que 0.00.');
      return;
    }

    if (!createdOn) {
      setError('Informe a data de criação.');
      return;
    }

    if (createdOn > today) {
      setError('A data de criação não pode ser futura.');
      return;
    }

    setBusy(true);
    try {
      const investment = await create({ amount: normalized, created_on: createdOn });
      navigate(`/investments/${investment.id}`);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        const body = err.body as { errors?: Record<string, string[]> };
        setFieldErrors(body.errors ?? {});
        setError('Verifique os campos e tente novamente.');
      } else {
        setError('Não foi possível criar o investimento.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <section className={styles.page}>
      <h1>Novo investimento</h1>
      <p>Só valor e data — dono, saldo e imposto ficam no servidor.</p>

      <form className={styles.form} onSubmit={handleSubmit} noValidate>
        <label htmlFor="amount">Valor</label>
        <input
          id="amount"
          name="amount"
          inputMode="decimal"
          placeholder="1000.00"
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          required
        />
        {fieldErrors.amount ? <p role="alert">{fieldErrors.amount[0]}</p> : null}

        <label htmlFor="created_on">Data de criação</label>
        <input
          id="created_on"
          name="created_on"
          type="date"
          max={today}
          value={createdOn}
          onChange={(e) => setCreatedOn(e.target.value)}
          required
        />
        {fieldErrors.created_on ? <p role="alert">{fieldErrors.created_on[0]}</p> : null}

        {error ? <p role="alert">{error}</p> : null}

        <button type="submit" disabled={busy}>
          Criar
        </button>
      </form>
    </section>
  );
}

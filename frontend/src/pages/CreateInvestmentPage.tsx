import { FormEvent, useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { create } from '@/api/investments';
import { ApiError } from '@/api/client';
import { canCreateInvestments } from '@/auth/permissions';
import { getUser } from '@/auth/session';
import styles from '@/pages/CreateInvestmentPage.module.css';

export function CreateInvestmentPage() {
  const navigate = useNavigate();
  const user = getUser();
  const today = new Date().toISOString().slice(0, 10);
  const [amount, setAmount] = useState('');
  const [createdOn, setCreatedOn] = useState(today);
  const [error, setError] = useState<string | null>(null);
  const [fieldErrors, setFieldErrors] = useState<Record<string, string[]>>({});
  const [busy, setBusy] = useState(false);

  if (!canCreateInvestments(user)) {
    return <Navigate to="/investments" replace />;
  }

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError(null);
    setFieldErrors({});

    const normalized = amount.trim();
    if (!normalized) {
      setError('Enter the investment amount.');
      return;
    }

    if (normalized === '0' || normalized === '0.00' || Number(normalized) <= 0) {
      setError('Amount must be greater than 0.00.');
      return;
    }

    if (!createdOn) {
      setError('Enter the creation date.');
      return;
    }

    if (createdOn > today) {
      setError('Creation date cannot be in the future.');
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
        setError('Check the fields and try again.');
      } else if (err instanceof ApiError && err.status === 403) {
        setError('Administrators cannot create investments.');
      } else {
        setError('Could not create the investment.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <section className={styles.page}>
      <h1>New investment</h1>
      <p>Amount and date only — owner, balance, and tax stay on the server.</p>

      <form className={styles.form} onSubmit={handleSubmit} noValidate>
        <label htmlFor="amount">Amount</label>
        <input
          id="amount"
          name="amount"
          inputMode="decimal"
          placeholder="1000.00"
          maxLength={32}
          value={amount}
          onChange={(e) => setAmount(e.target.value)}
          required
        />
        {fieldErrors.amount ? <p role="alert">{fieldErrors.amount[0]}</p> : null}

        <label htmlFor="created_on">Creation date</label>
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
          Create
        </button>
      </form>
    </section>
  );
}

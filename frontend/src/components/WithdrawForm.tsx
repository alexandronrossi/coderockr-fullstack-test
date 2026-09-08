import { FormEvent, useState } from 'react';
import { withdraw } from '@/api/investments';
import { ApiError } from '@/api/client';
import type { Investment } from '@/types/api';
import styles from '@/components/WithdrawForm.module.css';

interface WithdrawFormProps {
  investmentId: number;
  createdOn: string;
  onSuccess: (investment: Investment) => void;
}

export function WithdrawForm({ investmentId, createdOn, onSuccess }: WithdrawFormProps) {
  const today = new Date().toISOString().slice(0, 10);
  const [withdrawnOn, setWithdrawnOn] = useState(today);
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError(null);

    if (!withdrawnOn) {
      setError('Enter the withdrawal date.');
      return;
    }

    if (withdrawnOn > today) {
      setError('Withdrawal date cannot be in the future.');
      return;
    }

    if (withdrawnOn < createdOn) {
      setError('Withdrawal date must be on or after the creation date.');
      return;
    }

    setBusy(true);
    try {
      const result = await withdraw(investmentId, { withdrawn_on: withdrawnOn });
      onSuccess(result);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setError('Could not withdraw. Check the date.');
      } else if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError('Failed to withdraw the investment.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <form className={styles.form} onSubmit={handleSubmit} noValidate>
      <h2>Withdraw</h2>
      <p className={styles.hint}>Tax and net come from the API only — this screen does not calculate them.</p>
      <label htmlFor="withdrawn_on">Withdrawal date</label>
      <input
        id="withdrawn_on"
        name="withdrawn_on"
        type="date"
        value={withdrawnOn}
        max={today}
        min={createdOn}
        onChange={(e) => setWithdrawnOn(e.target.value)}
        required
      />
      {error ? <p role="alert">{error}</p> : null}
      <button type="submit" disabled={busy}>
        Confirm withdrawal
      </button>
    </form>
  );
}

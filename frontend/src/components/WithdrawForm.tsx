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
      setError('Informe a data de resgate.');
      return;
    }

    if (withdrawnOn > today) {
      setError('A data de resgate não pode ser futura.');
      return;
    }

    if (withdrawnOn < createdOn) {
      setError('A data de resgate deve ser igual ou posterior à criação.');
      return;
    }

    setBusy(true);
    try {
      const result = await withdraw(investmentId, { withdrawn_on: withdrawnOn });
      onSuccess(result);
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setError('Não foi possível resgatar. Verifique a data.');
      } else if (err instanceof ApiError) {
        setError(err.message);
      } else {
        setError('Falha ao resgatar o investimento.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <form className={styles.form} onSubmit={handleSubmit} noValidate>
      <h2>Resgatar</h2>
      <p className={styles.hint}>O imposto e o líquido vêm só da API — a tela não calcula.</p>
      <label htmlFor="withdrawn_on">Data do resgate</label>
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
        Confirmar resgate
      </button>
    </form>
  );
}

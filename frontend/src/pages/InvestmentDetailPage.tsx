import { useEffect, useState } from 'react';
import { Link, useParams } from 'react-router-dom';
import { ApiError } from '@/api/client';
import { show } from '@/api/investments';
import { WithdrawForm } from '@/components/WithdrawForm';
import type { Investment } from '@/types/api';
import styles from '@/pages/InvestmentDetailPage.module.css';

export function InvestmentDetailPage() {
  const { id } = useParams();
  const investmentId = Number(id);
  const [investment, setInvestment] = useState<Investment | null>(null);
  const [notFound, setNotFound] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!Number.isFinite(investmentId) || investmentId <= 0) {
      setNotFound(true);
      setLoading(false);
      return;
    }

    let cancelled = false;
    setLoading(true);
    setNotFound(false);
    setError(null);

    show(investmentId)
      .then((data) => {
        if (!cancelled) {
          setInvestment(data);
        }
      })
      .catch((err: unknown) => {
        if (cancelled) {
          return;
        }
        if (err instanceof ApiError && err.status === 404) {
          setNotFound(true);
          setInvestment(null);
        } else {
          setError('Could not load the investment.');
        }
      })
      .finally(() => {
        if (!cancelled) {
          setLoading(false);
        }
      });

    return () => {
      cancelled = true;
    };
  }, [investmentId]);

  if (loading) {
    return <p>Loading…</p>;
  }

  if (notFound) {
    return (
      <section className={styles.page}>
        <h1>Investment not found</h1>
        <p>The resource does not exist or you do not have access.</p>
        <Link to="/investments">Back to list</Link>
      </section>
    );
  }

  if (error || !investment) {
    return (
      <section className={styles.page}>
        <p role="alert">{error ?? 'Unexpected failure.'}</p>
        <Link to="/investments">Back to list</Link>
      </section>
    );
  }

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div>
          <p className={styles.eyebrow}>Investment #{investment.id}</p>
          <h1>{investment.owner.name}</h1>
          <p>{investment.owner.email}</p>
        </div>
        <span className={styles.status}>{investment.status}</span>
      </header>

      <dl className={styles.grid}>
        <div>
          <dt>Amount</dt>
          <dd>{investment.amount}</dd>
        </div>
        <div>
          <dt>Created on</dt>
          <dd>{investment.created_on}</dd>
        </div>
        <div>
          <dt>Expected balance</dt>
          <dd>{investment.expected_balance}</dd>
        </div>
        <div>
          <dt>Gain</dt>
          <dd>{investment.gain}</dd>
        </div>
        {investment.status === 'withdrawn' ? (
          <>
            <div>
              <dt>Withdrawn on</dt>
              <dd>{investment.withdrawn_on}</dd>
            </div>
            <div>
              <dt>Tax</dt>
              <dd>{investment.tax}</dd>
            </div>
            <div>
              <dt>Net</dt>
              <dd>{investment.net}</dd>
            </div>
          </>
        ) : null}
      </dl>

      {investment.status === 'active' ? (
        <WithdrawForm
          investmentId={investment.id}
          createdOn={investment.created_on}
          onSuccess={setInvestment}
        />
      ) : null}

      <p className={styles.back}>
        <Link to="/investments">Back to list</Link>
      </p>
    </section>
  );
}

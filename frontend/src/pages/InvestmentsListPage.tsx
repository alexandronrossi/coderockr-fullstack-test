import { useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import { list } from '@/api/investments';
import { canCreateInvestments } from '@/auth/permissions';
import { getUser } from '@/auth/session';
import { InvestmentRow } from '@/components/InvestmentRow';
import { Pagination } from '@/components/Pagination';
import type { InvestmentPage } from '@/types/api';
import styles from '@/pages/InvestmentsListPage.module.css';

export function InvestmentsListPage() {
  const user = getUser();
  const showCreate = canCreateInvestments(user);
  const [page, setPage] = useState(1);
  const [result, setResult] = useState<InvestmentPage | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;
    setLoading(true);
    setError(null);

    list(page)
      .then((data) => {
        if (!cancelled) {
          setResult(data);
        }
      })
      .catch(() => {
        if (!cancelled) {
          setError('Could not load investments.');
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
  }, [page]);

  const lastPage =
    result?.meta.last_page ??
    (result ? Math.max(1, Math.ceil(result.meta.total / result.meta.per_page)) : 1);

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div>
          <h1>Investments</h1>
          <p>
            {showCreate
              ? 'Amounts and status come from the API — no owner filtering in the client.'
              : 'Admin view: list and withdraw. Only Owners can create investments.'}
          </p>
        </div>
        {showCreate ? (
          <Link className={styles.create} to="/investments/new">
            New investment
          </Link>
        ) : null}
      </header>

      {loading ? <p>Loading…</p> : null}
      {error ? <p role="alert">{error}</p> : null}

      {!loading && !error && result ? (
        <div className={styles.summary} aria-live="polite">
          <p className={styles.summaryLabel}>Total balance</p>
          <p className={styles.summaryValue}>{result.summary.total_balance}</p>
        </div>
      ) : null}

      {!loading && !error && result && result.data.length === 0 ? (
        <div className={styles.empty}>
          <p>No investments yet.</p>
          {showCreate ? <Link to="/investments/new">Create the first one</Link> : null}
        </div>
      ) : null}

      {!loading && result && result.data.length > 0 ? (
        <>
          <div className={styles.tableWrap}>
            <table className={styles.table}>
              <thead>
                <tr>
                  <th>Owner</th>
                  <th>Date</th>
                  <th>Amount</th>
                  <th>Expected balance</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {result.data.map((investment) => (
                  <InvestmentRow key={investment.id} investment={investment} />
                ))}
              </tbody>
            </table>
          </div>
          <Pagination currentPage={page} lastPage={lastPage} onPageChange={setPage} />
        </>
      ) : null}
    </section>
  );
}

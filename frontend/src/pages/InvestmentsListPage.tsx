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
          setError('Não foi possível carregar os investimentos.');
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
          <h1>Investimentos</h1>
          <p>
            {showCreate
              ? 'Valores e status vêm da API — sem filtro de dono no cliente.'
              : 'Visão administrativa: listar e resgatar. Criação é só para Owners.'}
          </p>
        </div>
        {showCreate ? (
          <Link className={styles.create} to="/investments/new">
            Novo investimento
          </Link>
        ) : null}
      </header>

      {loading ? <p>Carregando…</p> : null}
      {error ? <p role="alert">{error}</p> : null}

      {!loading && !error && result && result.data.length === 0 ? (
        <div className={styles.empty}>
          <p>Nenhum investimento ainda.</p>
          {showCreate ? <Link to="/investments/new">Criar o primeiro</Link> : null}
        </div>
      ) : null}

      {!loading && result && result.data.length > 0 ? (
        <>
          <div className={styles.tableWrap}>
            <table className={styles.table}>
              <thead>
                <tr>
                  <th>Dono</th>
                  <th>Data</th>
                  <th>Valor</th>
                  <th>Saldo esperado</th>
                  <th>Status</th>
                  <th>Ações</th>
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

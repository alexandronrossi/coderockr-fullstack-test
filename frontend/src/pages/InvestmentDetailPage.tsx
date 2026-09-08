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
          setError('Não foi possível carregar o investimento.');
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
    return <p>Carregando…</p>;
  }

  if (notFound) {
    return (
      <section className={styles.page}>
        <h1>Investimento não encontrado</h1>
        <p>O recurso não existe ou você não tem acesso.</p>
        <Link to="/investments">Voltar à lista</Link>
      </section>
    );
  }

  if (error || !investment) {
    return (
      <section className={styles.page}>
        <p role="alert">{error ?? 'Falha inesperada.'}</p>
        <Link to="/investments">Voltar à lista</Link>
      </section>
    );
  }

  return (
    <section className={styles.page}>
      <header className={styles.header}>
        <div>
          <p className={styles.eyebrow}>Investimento #{investment.id}</p>
          <h1>{investment.owner.name}</h1>
          <p>{investment.owner.email}</p>
        </div>
        <span className={styles.status}>{investment.status}</span>
      </header>

      <dl className={styles.grid}>
        <div>
          <dt>Valor</dt>
          <dd>{investment.amount}</dd>
        </div>
        <div>
          <dt>Criado em</dt>
          <dd>{investment.created_on}</dd>
        </div>
        <div>
          <dt>Saldo esperado</dt>
          <dd>{investment.expected_balance}</dd>
        </div>
        <div>
          <dt>Ganho</dt>
          <dd>{investment.gain}</dd>
        </div>
        {investment.status === 'withdrawn' ? (
          <>
            <div>
              <dt>Resgatado em</dt>
              <dd>{investment.withdrawn_on}</dd>
            </div>
            <div>
              <dt>Imposto</dt>
              <dd>{investment.tax}</dd>
            </div>
            <div>
              <dt>Líquido</dt>
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
        <Link to="/investments">Voltar à lista</Link>
      </p>
    </section>
  );
}

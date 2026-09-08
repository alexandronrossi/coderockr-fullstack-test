import { Link } from 'react-router-dom';
import type { Investment } from '@/types/api';
import styles from '@/components/InvestmentRow.module.css';

interface InvestmentRowProps {
  investment: Investment;
}

export function InvestmentRow({ investment }: InvestmentRowProps) {
  return (
    <tr className={styles.row}>
      <td data-label="Dono">
        <div className={styles.owner}>
          <strong>{investment.owner.name}</strong>
          <span>{investment.owner.email}</span>
        </div>
      </td>
      <td data-label="Data">{investment.created_on}</td>
      <td data-label="Valor">{investment.amount}</td>
      <td data-label="Saldo esperado">{investment.expected_balance}</td>
      <td data-label="Status">
        <span className={investment.status === 'active' ? styles.active : styles.withdrawn}>
          {investment.status}
        </span>
      </td>
      <td data-label="Ações">
        <Link to={`/investments/${investment.id}`}>Abrir</Link>
      </td>
    </tr>
  );
}

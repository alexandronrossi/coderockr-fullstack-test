import { Link } from 'react-router-dom';
import type { Investment } from '@/types/api';
import styles from '@/components/InvestmentRow.module.css';

interface InvestmentRowProps {
  investment: Investment;
}

export function InvestmentRow({ investment }: InvestmentRowProps) {
  return (
    <tr className={styles.row}>
      <td data-label="Owner">
        <div className={styles.owner}>
          <strong>{investment.owner.name}</strong>
          <span>{investment.owner.email}</span>
        </div>
      </td>
      <td data-label="Date">{investment.created_on}</td>
      <td data-label="Amount">{investment.amount}</td>
      <td data-label="Expected balance">{investment.expected_balance}</td>
      <td data-label="Status">
        <span className={investment.status === 'active' ? styles.active : styles.withdrawn}>
          {investment.status}
        </span>
      </td>
      <td data-label="Actions">
        <Link to={`/investments/${investment.id}`}>Open</Link>
      </td>
    </tr>
  );
}

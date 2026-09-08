import { FormEvent, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { logout } from '@/api/auth';
import { canCreateInvestments } from '@/auth/permissions';
import { getUser } from '@/auth/session';
import styles from '@/components/AppShell.module.css';

interface AppShellProps {
  children: React.ReactNode;
}

export function AppShell({ children }: AppShellProps) {
  const navigate = useNavigate();
  const user = getUser();
  const [busy, setBusy] = useState(false);
  const showCreate = canCreateInvestments(user);

  async function handleLogout(event: FormEvent) {
    event.preventDefault();
    if (busy) {
      return;
    }
    setBusy(true);
    try {
      await logout();
    } catch {
      // Session is cleared in logout() finally; still leave the app.
    } finally {
      navigate('/login', { replace: true });
      setBusy(false);
    }
  }

  return (
    <div className={styles.shell}>
      <header className={styles.header}>
        <div className={styles.brand}>
          <img
            src="/images/coderockr.banner.svg"
            alt="Coderockr"
            className={styles.logo}
          />
          <span className={styles.product}>Investimentos</span>
        </div>
        <nav className={styles.nav} aria-label="Principal">
          <Link to="/investments">Lista</Link>
          {showCreate ? <Link to="/investments/new">Novo</Link> : null}
        </nav>
        <div className={styles.user}>
          {user ? (
            <>
              <div className={styles.userMeta}>
                <strong>{user.name}</strong>
                <span>{user.email}</span>
                <span className={styles.role}>{user.role}</span>
              </div>
              <button type="button" onClick={handleLogout} disabled={busy}>
                Sair
              </button>
            </>
          ) : null}
        </div>
      </header>
      <main className={styles.main}>{children}</main>
    </div>
  );
}

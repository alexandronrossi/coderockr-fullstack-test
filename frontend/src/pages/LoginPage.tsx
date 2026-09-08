import { FormEvent, useState } from 'react';
import { Navigate, useNavigate } from 'react-router-dom';
import { login } from '@/api/auth';
import { ApiError } from '@/api/client';
import { isAuthenticated } from '@/auth/session';
import styles from '@/pages/LoginPage.module.css';

export function LoginPage() {
  const navigate = useNavigate();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  if (isAuthenticated()) {
    return <Navigate to="/investments" replace />;
  }

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError(null);
    setBusy(true);

    try {
      await login({ email, password });
      navigate('/investments', { replace: true });
    } catch (err) {
      if (err instanceof ApiError && err.status === 401) {
        setError('Não foi possível entrar. Verifique seus dados e tente novamente.');
      } else {
        setError('Não foi possível entrar. Tente novamente.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className={styles.page}>
      <section className={styles.hero} aria-labelledby="login-brand">
        <img
          src="/images/coderockr.banner.svg"
          alt=""
          className={styles.logo}
        />
        <h1 id="login-brand" className={styles.brand}>
          Coderockr
        </h1>
        <p className={styles.tagline}>Investimentos com ganhos e IR calculados no servidor.</p>
      </section>

      <form className={styles.form} onSubmit={handleSubmit} noValidate>
        <h2>Entrar</h2>
        <label htmlFor="email">E-mail</label>
        <input
          id="email"
          name="email"
          type="email"
          autoComplete="username"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        <label htmlFor="password">Senha</label>
        <input
          id="password"
          name="password"
          type="password"
          autoComplete="current-password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        {error ? <p role="alert">{error}</p> : null}
        <button type="submit" disabled={busy}>
          Continuar
        </button>
      </form>
    </div>
  );
}

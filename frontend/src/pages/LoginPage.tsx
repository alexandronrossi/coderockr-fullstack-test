import { FormEvent, useState } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { login } from '@/api/auth';
import { ApiError } from '@/api/client';
import { AUTH_FIELD_LIMITS } from '@/auth/limits';
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

    if (email.length > AUTH_FIELD_LIMITS.email || password.length > AUTH_FIELD_LIMITS.password) {
      setError('Email or password is too long.');
      return;
    }

    setBusy(true);

    try {
      await login({ email, password });
      navigate('/investments', { replace: true });
    } catch (err) {
      if (err instanceof ApiError && err.status === 401) {
        setError('Could not sign in. Check your details and try again.');
      } else if (err instanceof ApiError && err.status === 422) {
        setError('Could not sign in. Check your details and try again.');
      } else {
        setError('Could not sign in. Try again.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className={styles.page}>
      <section className={styles.hero} aria-labelledby="login-brand">
        <img src="/images/coderockr.banner.svg" alt="" className={styles.logo} />
        <h1 id="login-brand" className={styles.brand}>
          Coderockr
        </h1>
        <p className={styles.tagline}>
          Investments with gains and tax calculated on the server.
        </p>
      </section>

      <form className={styles.form} onSubmit={handleSubmit} noValidate>
        <h2>Sign in</h2>
        <label htmlFor="email">Email</label>
        <input
          id="email"
          name="email"
          type="email"
          autoComplete="username"
          maxLength={AUTH_FIELD_LIMITS.email}
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        <label htmlFor="password">Password</label>
        <input
          id="password"
          name="password"
          type="password"
          autoComplete="current-password"
          maxLength={AUTH_FIELD_LIMITS.password}
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        {error ? <p role="alert">{error}</p> : null}
        <button type="submit" disabled={busy}>
          Continue
        </button>
        <p className={styles.switch}>
          Need an account? <Link to="/register">Create one</Link>
        </p>
      </form>
    </div>
  );
}

import { FormEvent, useState } from 'react';
import { Link, Navigate, useNavigate } from 'react-router-dom';
import { register } from '@/api/auth';
import { ApiError } from '@/api/client';
import { AUTH_FIELD_LIMITS } from '@/auth/limits';
import { isAuthenticated } from '@/auth/session';
import styles from '@/pages/LoginPage.module.css';

export function RegisterPage() {
  const navigate = useNavigate();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);

  if (isAuthenticated()) {
    return <Navigate to="/investments" replace />;
  }

  async function handleSubmit(event: FormEvent) {
    event.preventDefault();
    setError(null);

    if (
      name.length > AUTH_FIELD_LIMITS.name ||
      email.length > AUTH_FIELD_LIMITS.email ||
      password.length > AUTH_FIELD_LIMITS.password ||
      passwordConfirmation.length > AUTH_FIELD_LIMITS.password
    ) {
      setError('One or more fields are too long.');
      return;
    }

    if (password.length < AUTH_FIELD_LIMITS.passwordMin) {
      setError(`Password must be at least ${AUTH_FIELD_LIMITS.passwordMin} characters.`);
      return;
    }

    if (password !== passwordConfirmation) {
      setError('Password confirmation does not match.');
      return;
    }

    setBusy(true);
    try {
      await register({
        name: name.trim(),
        email: email.trim(),
        password,
        password_confirmation: passwordConfirmation,
      });
      navigate('/investments', { replace: true });
    } catch (err) {
      if (err instanceof ApiError && err.status === 422) {
        setError('Could not create the account. Check the fields and try again.');
      } else {
        setError('Could not create the account. Try again.');
      }
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className={styles.page}>
      <section className={styles.hero} aria-labelledby="register-brand">
        <img src="/images/coderockr.banner.svg" alt="" className={styles.logo} />
        <h1 id="register-brand" className={styles.brand}>
          Coderockr
        </h1>
        <p className={styles.tagline}>
          New accounts are always Owners. Admins are seeded for review only.
        </p>
      </section>

      <form className={styles.form} onSubmit={handleSubmit} noValidate>
        <h2>Create account</h2>
        <label htmlFor="name">Name</label>
        <input
          id="name"
          name="name"
          type="text"
          autoComplete="name"
          maxLength={AUTH_FIELD_LIMITS.name}
          value={name}
          onChange={(e) => setName(e.target.value)}
          required
        />
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
          autoComplete="new-password"
          maxLength={AUTH_FIELD_LIMITS.password}
          minLength={AUTH_FIELD_LIMITS.passwordMin}
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        <label htmlFor="password_confirmation">Confirm password</label>
        <input
          id="password_confirmation"
          name="password_confirmation"
          type="password"
          autoComplete="new-password"
          maxLength={AUTH_FIELD_LIMITS.password}
          minLength={AUTH_FIELD_LIMITS.passwordMin}
          value={passwordConfirmation}
          onChange={(e) => setPasswordConfirmation(e.target.value)}
          required
        />
        {error ? <p role="alert">{error}</p> : null}
        <button type="submit" disabled={busy}>
          Create account
        </button>
        <p className={styles.switch}>
          Already registered? <Link to="/login">Sign in</Link>
        </p>
      </form>
    </div>
  );
}

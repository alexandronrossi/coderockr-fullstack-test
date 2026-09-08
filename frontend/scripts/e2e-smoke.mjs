import { chromium } from 'playwright';

const UI = process.env.UI_URL ?? 'http://127.0.0.1:5173';
const API = process.env.API_URL ?? 'http://127.0.0.1:8000/api';

async function assert(condition, message) {
  if (!condition) {
    throw new Error(message);
  }
}

async function apiLogin(email, password) {
  const res = await fetch(`${API}/login`, {
    method: 'POST',
    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
  });
  const body = await res.json();
  assert(res.ok, `API login failed for ${email}: ${res.status}`);
  return body;
}

async function main() {
  const results = [];

  // API smoke
  const owner = await apiLogin('owner@example.com', 'password');
  results.push('API owner login OK');

  const createRes = await fetch(`${API}/investments`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
      Authorization: `Bearer ${owner.token}`,
    },
    body: JSON.stringify({ amount: '1000.00', created_on: '2024-01-15' }),
  });
  const createdEnvelope = await createRes.json();
  const created = createdEnvelope.data;
  assert(createRes.status === 201, `create failed: ${createRes.status}`);
  assert(typeof created?.expected_balance === 'string', 'expected_balance missing');
  results.push(`API create investment #${created.id} balance=${created.expected_balance}`);

  const browser = await chromium.launch({
    headless: true,
    channel: process.env.PW_CHANNEL || 'msedge',
  });
  const page = await browser.newPage();

  // Invalid login
  await page.goto(`${UI}/login`);
  await page.getByLabel(/e-mail/i).fill('owner@example.com');
  await page.getByLabel(/senha/i).fill('wrong-password');
  await page.getByRole('button', { name: /continuar/i }).click();
  await page.getByRole('alert').waitFor();
  const alertText = await page.getByRole('alert').textContent();
  assert(/não foi possível entrar/i.test(alertText ?? ''), `bad alert: ${alertText}`);
  results.push('UI invalid login shows generic error');

  // Owner happy path
  await page.getByLabel(/senha/i).fill('password');
  await page.getByRole('button', { name: /continuar/i }).click();
  await page.waitForURL('**/investments');
  await page.getByRole('heading', { name: /investimentos/i }).waitFor();
  results.push('UI owner login → list');

  await page.getByRole('link', { name: /novo investimento/i }).click();
  await page.waitForURL('**/investments/new');
  await page.getByLabel(/valor/i).fill('500.00');
  await page.getByLabel(/data de criação/i).fill('2024-03-01');
  await page.getByRole('button', { name: /criar/i }).click();
  await page.waitForURL(/\/investments\/\d+/);
  await page.getByText('500.00').first().waitFor();
  assert(await page.getByLabel(/data do resgate/i).isVisible(), 'withdraw form missing');
  results.push('UI create → detail with withdraw form');

  await page.getByLabel(/data do resgate/i).fill('2024-06-01');
  await page.getByRole('button', { name: /confirmar resgate/i }).click();
  await page.getByText('withdrawn').waitFor();
  const tax = await page.getByText(/imposto/i).locator('..').getByRole('definition').textContent().catch(() => null);
  // Prefer reading dd next to Imposto label
  const impostoDd = page.locator('dt', { hasText: 'Imposto' }).locator('xpath=following-sibling::dd[1]');
  await impostoDd.waitFor();
  const taxValue = (await impostoDd.textContent())?.trim();
  const liquidoDd = page.locator('dt', { hasText: 'Líquido' }).locator('xpath=following-sibling::dd[1]');
  const netValue = (await liquidoDd.textContent())?.trim();
  assert(!!taxValue && taxValue !== '', `tax empty: ${taxValue}`);
  assert(!!netValue && netValue !== '', `net empty: ${netValue}`);
  results.push(`UI withdraw shows tax=${taxValue} net=${netValue}`);

  await page.getByRole('button', { name: /sair/i }).click();
  await page.waitForURL('**/login');
  await page.goto(`${UI}/investments`);
  await page.waitForURL('**/login');
  results.push('UI logout clears access to list');

  // Admin login sees list
  await page.getByLabel(/e-mail/i).fill('admin@example.com');
  await page.getByLabel(/senha/i).fill('password');
  await page.getByRole('button', { name: /continuar/i }).click();
  await page.waitForURL('**/investments');
  await page.getByRole('heading', { name: /investimentos/i }).waitFor();
  results.push('UI admin login → list');

  await browser.close();

  console.log(JSON.stringify({ ok: true, results }, null, 2));
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});

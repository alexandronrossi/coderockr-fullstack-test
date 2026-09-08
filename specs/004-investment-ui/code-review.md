# Code review — 004-investment-ui

State: `TESTS_PASSED` → Code Review

## Verdict: CODE_REVIEW_PASSED

### Architecture
- Thin API client + session + pages; no Admin/Owner Strategy on the client (API scopes list).
- Money fields stay strings; display-only — no compound/tax math in `frontend/src`.

### Security
- Payload allowlists on login/create/withdraw.
- 401 clears session; foreign resources rely on API 404.
- Role shown in shell is cosmetic only.

### Quality
- Vitest covers session, client Bearer/401, list/create/show/withdraw allowlists, login/list/detail/withdraw/logout UX.
- No CRITICAL/HIGH findings.

### Notes (non-blocking)
- MEDIUM: screenshots are illustrative UI captures, not live browser recordings.
- LOW: date inputs in tests use `userEvent.type`; acceptable for Vitest.

# Security review — 004-investment-ui

```yaml
security_review:
  rls: PASS
  authorization: PASS
  idor: PASS
  frontend_permissions: PASS
  hardcoded_secrets: PASS
  xss: PASS
  sql_injection: PASS
  mass_assignment: PASS
  authentication: PASS
  data_exposure: PASS
  concurrency: PASS
```

## Notes

- `frontend_permissions`: hiding withdraw when not `active` and showing `role` are UX-only; authorization remains on Sanctum + owner/admin API rules (404 on foreign IDs).
- `hardcoded_secrets`: only public `VITE_API_URL`; no seed passwords in the SPA bundle.
- `idor`: client never sends `user_id` to widen scope; list/show trust API filtering.
- No API/route/domain changes in this feature → no new mass-assignment surface.

State: `SECURITY_REVIEW_PASSED`

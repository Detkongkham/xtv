# 2026-10-22-phase0-a0-local-first-plan

## Auto log (file changes)

| Time | Tool | File |
|------|------|------|
| 15:50:13 | Edit | docs/plan/06-phases.md |
| 15:50:14 | Edit | docs/plan/06-phases.md |
| 15:50:31 | Edit | docs/plan/06-phases.md |
| 15:50:32 | Edit | docs/plan/06-phases.md |
| 15:50:33 | Edit | docs/plan/06-phases.md |

## Summary
- **What changed:** `docs/plan/06-phases.md`: phases 0–3 now run on the local machine; new phase S "ຂຶ້ນ server" before go-live holds P0-A1 (server check), P0-A3 (subdomain), P0-A4 (SSH), P0-A8 (deploy) plus S-1 DB/config, S-2 real mailbox + SPF/DKIM/DMARC, S-3 cron, S-4 check; server-only acceptance items moved there; new P1-B0 (local PHP 8.3, MySQL 8.0, composer, Mailpit); B2/B3/B9 and P2/P3 acceptance reworded for local. `01-architecture.md` §5, `03-backend.md`, `07-deployment.md`: phase references point to phase S.
- **Why:** user wants to develop locally first and do all server work last.
- **Status:** done — plan only, no code changed. Task IDs kept so existing changelog names stay valid.
- **Next:** P0-A2 (move to `public/`). Local machine has no PHP/MySQL/composer yet (needed from phase 1, not phase 0). `07-deployment.md` §4 note about Python-built pages in phase 0–1 is now partly stale.

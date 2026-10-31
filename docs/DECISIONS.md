# Decisions Log

Newest at the bottom. Add a line whenever you decide something, so nobody repeats a discussion.

## Decided

| Date | Decision |
|---|---|
| 2026-10-31 | Source code hosted in a private GitHub repo named `RenukaNovals`. |
| 2026-10-31 | Local development with LocalWP, not Docker. Settings: nginx, PHP 8.2.x, MySQL 8.x, WordPress 7.1.4. |
| 2026-10-31 | The repo root is the WordPress root folder. Only the child theme, `dm-core` and `docs/` are tracked. |
| 2026-10-31 | Branches: `main` (approved), `develop` (shared), `feature/*`. |

## Open questions

| Question | Owner |
|---|---|
| Exact forest green and gold hex values from the design board | Kavi |
| Self-host the Tamil fonts or load them from Google Fonts | to decide |
| How to share the dummy Tamil stories between the two local sites (WP-CLI export or a migration plugin) | to try early |
| Where the prototype is hosted for Renuga's review | to decide |
| How push notifications will be delivered (needs early testing) | to test early |

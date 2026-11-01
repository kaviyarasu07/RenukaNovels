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

## 2026-10-31: Prototype approved by the author
- Prototype v3 (file: docs/prototype/prototype-v3-approved.html) approved after her 9 corrections.
- Palette: the prototype's teal palette with day/night reading mode replaces the Secret Garden board. Tokens are in assets/css/tokens.css.
- Menu: hamburger drawer at every width. Pages: Home, Ongoing, Completed, Updates, Bookmarks, About Us, Contact Us.
- Home shows 3 trending, 1 ongoing, 1 completed, each with a View More. "Continue Where You Left Off" lives on the Ongoing page.
- Announcements: author posts short updates from the WordPress admin; they appear in Latest Updates. To be built in dm-core.
- Reading controls sit at the bottom of the reading page.
- Open: confirm with Kavi that "she ok with that" meant the teal palette as it is.
- 2026-10-31: dm-core spec drafted in docs/DM_CORE_SPEC.md (modules, build order, open questions).
- 2026-10-31: Author answers: email-only login; approve each new reader once (no per-comment approval); hosting decided later; about 15 novels x 10 chapters.
- 2026-10-31: Build step 1 done: dm_story and dm_chapter post types (dm-core 0.2.0), child theme templates (home, ongoing, completed, story, chapter), hamburger drawer, reading controls at the bottom. Needs pages with slugs "ongoing" and "completed" in WordPress.
- 2026-11-01: Home page built to match the prototype (logo, stats, trending, author box, updates from chapters). Rating, notify box, announcements, author text still pending.

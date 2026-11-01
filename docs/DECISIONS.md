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
- 2026-11-01: AdSense planned. Footer links, content page template, Tools > DM Required Pages (creates About/Contact/FAQ/Privacy/Terms drafts) and docs/ADSENSE_CHECKLIST.md added. Do on every site: run DM Required Pages, fill the [bracketed] text, publish.
- 2026-11-01: Trending page and view counter added (dm-core 0.3.1). Counts total views per chapter/story once per visitor per day. Weekly ranking and admin-visit exclusion still to do. Do on every site: run Tools > DM Required Pages to create the trending page.
- 2026-11-01: Updates page added (20 per page, paginated). Do on every site: run Tools > DM Required Pages to create the updates page.
- 2026-11-01: Continue Where You Left Off added to Ongoing page; saved per browser (localStorage). Sync to the user account comes with the login module.
- 2026-11-01: Roles (Admin, Reader=dm_reader), My Account page (tabs: Announcements for admin; Reading; Bookmarks/Comments/Highlights placeholders), announcements CPT merged into updates feed. Do on every site: run Tools > DM Required Pages to create the my-account page. Reader approval and front-end registration are next.
- 2026-11-01: Step B built (dm-core 0.5.0): front-end Log in / Register page, reader status pending/approved/rejected, Reader approvals tab on My Account (admin). Admin and users without status count as approved. Email notices use wp_mail and will not send on local. Email verification not built. Do on every site: run Tools > DM Required Pages to create the login page.
- 2026-11-01: Step D built (dm-core 0.6.0): comments on chapters (approved readers only, one reply level, delete by author/admin, admin Block reader), star ratings per story (table dm_ratings, created on version change), rating shown on library, trending, story page and home hero stat, My Account Comments tab. Bookmarks and highlights (Step C) still to do.
- 2026-11-01: dm-core 0.7.0 / theme 0.9.0: comments paged (5 newest, +10 per click; newest reply only, show all replies), font size scoped to story text only, story text cannot be selected or copied (cannot stop screenshots), sentence-tap highlighter (3 colours + erase) and chapter bookmark saved per approved reader (tables dm_bookmarks, dm_highlights), My Account Bookmarks and Highlights tabs.

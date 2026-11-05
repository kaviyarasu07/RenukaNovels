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
- 2026-11-01: Admin My Account: Comments, Bookmarks, Highlights tabs show all readers (with names) by default; toggle Only mine. Admin can delete any comment and block reader from the list.
- 2026-11-01: My Account lists (announcements, reader approvals, comments, bookmarks, highlights) are paginated, 20 per page (query arg pg).
- 2026-11-01: dm-core 0.8.0 / theme 0.10.0: Stats tab on My Account (admin): daily (30 days) and monthly (12 months) views chart, all-stories table and episode-by-episode table with views, comments and reactions. New tables dm_views_daily and dm_reactions. Admin visits are no longer counted. Trending now ranks by views in the last 7 days (ties: total views). Chapter reactions (heart, laugh, sad, wow, fire, clap; one per approved reader, tap again to remove). Reading page: episode list on the right (reading now / read / not read); on phones it sits under the Previous/Next buttons. Story page: episode list with date, min read, views, comments; small rating box below the list; comments under it. UI wording on these pages is now "Episode". "Read" status is stored per browser (localStorage), like Continue Reading. Sample data now seeds 30 days of fake views (remove and recreate sample data to get them).
- 2026-11-03: dm-core 0.8.1 fix: WordPress's built-in Parent box lists other chapters, not stories, so a chapter could not be attached to a story from the admin (sample data set it in code). Chapter details now has a "Story" dropdown that sets the parent; the story edit screen has an "Add a chapter to this story" button (pre-selects the story and suggests the next number); the Chapters list shows Story and No. columns. Built-in Page Attributes removed from chapters.
- 2026-11-03: Begin Reading decided by the author: option 1, resume the last novel/episode. Home button "Begin Reading" opens the last chapter this browser read, at the saved paragraph, and its label becomes "Continue Reading". First-time visitors (nothing saved) go to episode 1 of the most recently updated ongoing story (completed story if none ongoing). Saved per browser; not synced to the account yet. Theme 0.10.1.
- 2026-11-03: Notification bell in the menu bar (dm-core 0.9.0, theme 0.11.0), logged-in users only. Everyone sees: new story, new episode, announcement (published after they registered, last 30 days). Readers also get: reply to their comment, account approved/rejected. Admins also get: any comment/reply, rating, reaction, new reader waiting for approval. Unread count clears when the bell is opened (user meta dm_notif_seen). Table dm_notifications (max 100 per user; repeat actions on the same item update one row). In-site only; web push (when the site is closed) is a separate later module and needs HTTPS. Guests do not see the bell.
- 2026-11-03: Checked the author's 9-point corrections list against the build. Fixes: menu "Bookmarks" now opens My Account > Bookmarks (was a dead #bookmarks anchor); home button keeps the label "Begin Reading" even when it resumes (her point 7 asked to remove "Continue Reading" from the home page). Theme 0.11.1. Open for her: brightness slider (we have day/night mode) and separate zoom (same as font size).
- 2026-11-03: Reading controls bar hides itself: visible 1.5 s after the page opens and after any scroll, then fades out. Stays visible while the mouse is on it, a button has focus, or the highlighter is on. Comes back on scroll, tap on the story text, or mouse near the bottom edge. Theme 0.11.2.
- 2026-11-03: The "Tap a sentence to highlight it" pop-up covered the colour buttons; it is now a small line inside the colour bar. Theme 0.11.3.
- 2026-11-05: Chapters (episodes) now use the classic editor (dm-core 0.9.1). Reason: in the block editor the side box with Story and Chapter number was collapsed/lost by a click and hard to bring back. Stories still use the block editor. Revisit if Renuga prefers one editor for both.
- 2026-11-05: Fixed a redirect loop on /login/ (dm-core 0.9.2). On a new site where the Login page does not exist yet, WordPress core sends /login/ to wp_login_url(), our login_url filter returned /login/ again, and the browser looped. The filter now only applies when the published "login" page exists; fresh setups must still run Tools > DM Required Pages.

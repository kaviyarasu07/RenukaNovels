# dm-core plugin spec (v0.1 draft)

Plugin holds all site logic so the theme stays presentation-only. Prefix everything `dm_`. One folder per module under `includes/`, each with an `init()` hooked from `dm-core.php`.

Stories use a custom post type `dm_story` (chapters as child posts `dm_chapter`). Status is a meta field: `ongoing` or `completed`.

## 1. Engagement (login required)
- Comments: threaded (max 2 levels), WordPress comments on `dm_chapter`. Fix from Pavithra's site: replies must render cleanly nested, emoji at 1em.
- Ratings: 1-5 stars per user per story, one rating each, editable. Table `dm_ratings(user_id, story_id, stars, created_at)`; average cached in story meta.
- Bookmarks: per user per story, plus last-read paragraph for "Continue Where You Left Off". Table `dm_bookmarks(user_id, story_id, chapter_id, para, pct, updated_at)`.
- Reading stays open to everyone, no login.
- Registration: email and password only (no social login). A new account starts as `pending`. Renuga approves it from an admin screen "Reader approvals" (Approve / Reject). Until approved the reader can read but cannot comment, rate or bookmark, and sees a short "Waiting for approval" message. Store as user meta `dm_status` (pending, approved, rejected). Email verification of the address is recommended on top, to stop fake sign-ups.

## 2. Announcements (correction 8)
- Admin screen "Announcements": a text box, optional "pin to top", Publish button. CPT `dm_announcement`.
- Shown in Latest Updates, merged with chapter updates and sorted by date, pinned first.
- Publishing can also trigger a push notification (toggle, default off).

## 3. Stats
- Count views per story and per chapter, daily rollups. Table `dm_views_daily(date, story_id, chapter_id, views)`.
- Count once per visitor per chapter per day (cookie), ignore bots and the admin.
- Admin dashboard: daily and monthly views, per story and per chapter.

## 4. Moderation and spam
- Reader approval is the gate (see Engagement). Once a reader is approved, their comments and ratings publish immediately, no per-comment approval.
- Admin can still delete or hide a comment or rating afterwards.
- Block list: words, email, IP. Honeypot field plus rate limit (e.g. 5 comments per 10 minutes).
- Admin can approve, reject, ban or unban a user, and trash or restore comments.

## 5. Notifications (web push)
- Browser push through a service worker, VAPID keys, subscriptions in `dm_push_subs`.
- Triggered on new chapter publish and optionally announcements.
- Uncertain: iPhone Safari only supports web push after the reader adds the site to the Home Screen. Needs HTTPS. Plan to test early on a real device.

## 6. Site safety (hosting side, not plugin)
- SSL, daily backups with restore, trash for deleted stories (WordPress trash with 30-day retention, restore from admin).
- Choose host and backup tool before quoting.

## Build order
1. CPTs and story/chapter templates from the approved prototype
2. Login, bookmarks and continue-reading
3. Comments and ratings
4. Announcements
5. Stats
6. Moderation
7. Push notifications

## Decisions from the author (31-Oct-2026)
- Login: email only.
- Moderation: approve each new reader once; comments are not approved one by one.
- Hosting: not decided. Build the application first; she chooses hosting later.
- Content size: about 15 novels, about 10 chapters each (about 150 chapters). A bulk import tool is worth planning.

## Open questions
- Should an email notify Renuga on each new registration, and the reader when approved or rejected? (Needs a mail setup that works on the chosen host.)
- Tamil comment input and Tamil search need testing.
- Hosting is open, so backups, SSL and the push-notification test wait for it.

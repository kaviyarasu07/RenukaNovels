# Project Brief: Renuga Tamil Novels Website

**Client:** author Renuga
**Builders:** Kavi and a teammate (both backend engineers). Claude helps with design, code and planning.

## Scope v1 (prototype)

- Home page
- Story reading page
- Both for mobile and desktop
- More screens if time permits

After Renuga approves the prototype, Kavi quotes and builds the real site in WordPress.

**No final price until the prototype is approved.** The cost goes in a separate cost sheet.

## Design: "Secret Garden" (light theme only, mobile-first)

| Role | Value |
|---|---|
| Cream (background) | `#F8F5E9` |
| Sage (panels, soft sections) | `#A7C1A8` |
| Forest green (text, buttons) | `#2F4A3A` (proposed, confirm against the design board) |
| Dusty rose (accent) | `#B4726A` |
| Gold | small accent only (value to be taken from the board) |

Fonts: Noto Sans Tamil, Hind Madurai, Latha as fallback.

### Contrast notes (calculated, WCAG ratios)

- Forest on cream: about 8.9:1. Good for body text.
- Forest on sage: about 5.0:1. Good.
- Dusty rose on cream (and cream on rose): about 3.5:1. This passes only for large text and decoration, so do **not** use rose for small body text. If rose buttons need small text, use a darker rose or forest text. To be re-checked with a contrast tool once final values are chosen.

## Must-fix lessons (from Pavithra's site)

1. Consistent alignment on mobile, tablet and PC.
2. Clean threaded replies.
3. Emoji sized 1em.
4. No overlapping text on images.
5. Good contrast.

## Features (from Renuga's list)

- Read stories without logging in.
- Login needed for comments, ratings, bookmarks.
- Web push notifications.
- Views per story and per chapter, with daily and monthly stats.
- Moderation of comments and ratings, plus spam blocking.
- SSL, backups, and restore of deleted stories.
- A separate cost sheet.

## Tech

- WordPress
- Kadence parent theme with the child theme `renuga-child`
- Custom plugin `dm-core`

## Rules

- UI text in Tamil. Communication with Claude in English.
- Tell Kavi when something is uncertain.
- Light theme only.

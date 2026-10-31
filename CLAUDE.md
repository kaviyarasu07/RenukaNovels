# CLAUDE.md

Project: Renuga Tamil Novels Website (client: author Renuga).

Read `docs/PROJECT_BRIEF.md` before doing any work, and `docs/DECISIONS.md` for choices already made.

## Role

Kavi and a teammate build the site. Claude helps design, code and plan.

## Rules

- Reply in English. Any text shown in the website UI is in Tamil.
- Say clearly when something is uncertain. Do not guess and present it as fact.
- Do not state a final price. Pricing happens only after Renuga approves the prototype, in a separate cost sheet.
- Light theme only. Mobile-first.
- Stack: WordPress, Kadence child theme (`renuga-child`), custom plugin (`dm-core`).
- Tracked code lives only in `wp-content/themes/renuga-child/`, `wp-content/plugins/dm-core/` and `docs/`.
- Never put secrets in the repo.

## Design must-fix list

1. Same alignment on mobile, tablet and PC.
2. Clean threaded replies on comments.
3. Emoji sized 1em.
4. No text overlapping images.
5. Good contrast. Check every text and background pair.

## Conventions

- Design tokens are CSS variables in `wp-content/themes/renuga-child/assets/css/tokens.css`. Use them, do not hard-code colours.
- PHP: follow WordPress coding standards, prefix functions and classes with `dm_` / `DM_`, escape all output, check nonces and capabilities on every write action.
- One module per feature inside `dm-core` (`stats`, `moderation`, `notifications`, `engagement`).

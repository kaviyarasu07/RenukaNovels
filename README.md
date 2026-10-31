# Renuga Novels Website

Tamil novels website for author Renuga. WordPress + Kadence child theme + custom `dm-core` plugin.

Read `docs/PROJECT_BRIEF.md` first. It holds the scope, design rules and must-fix list.

## What is in this repo

Only our own code is tracked. WordPress core, uploads and other plugins stay out of Git (see `.gitignore`).

```
.gitignore
README.md
CLAUDE.md                              context for Claude Code / Claude
docs/                                  brief, decisions, screen specs
wp-content/themes/renuga-child/        Kadence child theme (design + templates)
wp-content/plugins/dm-core/            custom plugin (stats, moderation, notifications, engagement)
```

The repo root is the WordPress root folder (in LocalWP: `<site>/app/public`).

## Local setup (LocalWP)

Both developers use the same settings so code behaves the same on both machines.

| Setting | Value |
|---|---|
| Site name | `renuga-novels` |
| Environment | Preferred |
| Web server | nginx |
| PHP | 8.2.x |
| Database | MySQL 8.x |
| WordPress | same version on both machines (currently 7.1.4) |

1. Install LocalWP and create the site with the settings above.
2. In WP Admin, install and activate the **Kadence** parent theme (it is not kept in Git).
3. Open the **Site shell** from LocalWP and run:
   ```
   cd app/public
   git init
   git remote add origin https://github.com/<owner>/RenukaNovals.git
   git fetch origin
   git checkout -b develop origin/develop
   ```
   If git complains that files would be overwritten, tell Kavi, do not force it.
4. In WP Admin, activate the **Renuga Child** theme and the **DM Core** plugin.

## Branches

- `main`: approved work only. Nobody pushes straight to it.
- `develop`: shared working branch.
- `feature/<name>`: one branch per task, for example `feature/home-page`, `feature/reader`.

Flow: branch from `develop`, push, open a pull request into `develop`, the other person reviews, then merge.

## Rules

- No passwords, API keys or client logins in the repo.
- Tamil text in the UI, English in code and comments.
- Light theme only. Mobile-first.
- No final price is promised until Renuga approves the prototype.

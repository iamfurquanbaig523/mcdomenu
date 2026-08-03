# Production Deployment Pipeline

This project deploys WordPress as immutable release directories. GitHub Actions builds the release artifact, uploads it to Hostinger over SSH, prepares it outside the live path, and only switches live traffic when activation is explicitly requested.

## Server Layout

Recommended Hostinger layout:

```text
/home/u913239002/domains/mcdomenuusa.com/
  deploy/
    releases/
      20260606-abc123/
      20260607-def456/
    shared/
      .env
      uploads/
      cache/
      litespeed/
    current -> releases/20260607-def456
    previous -> releases/20260606-abc123
    active-release
  public_html -> deploy/current
```

`public_html` should eventually be a symlink to `deploy/current`. The scripts refuse to replace a real `public_html` directory unless `allow_live_path_replace` is explicitly set to `true` in the workflow.

## GitHub Configuration

Create these repository secrets:

```text
HOSTINGER_SSH_HOST=46.202.172.5
HOSTINGER_SSH_PORT=65002
HOSTINGER_SSH_USER=u913239002
HOSTINGER_SSH_PASSWORD=<store in GitHub secrets only>
```

Create these repository variables:

```text
HOSTINGER_DEPLOY_ROOT=/home/u913239002/domains/mcdomenuusa.com/deploy
HOSTINGER_LIVE_PATH=/home/u913239002/domains/mcdomenuusa.com/public_html
PRODUCTION_SITE_URL=https://mcdomenuusa.com
PRODUCTION_HEALTH_URL=https://mcdomenuusa.com/
PRODUCTION_ADMIN_HEALTH_URL=https://mcdomenuusa.com/wp-login.php
```

Prefer SSH keys over passwords when the Hostinger account is ready for that. The workflow supports the current password-based setup through GitHub secrets without committing credentials.

## Production Environment Parity

The checked-in `wp-config.php` reads environment values from either server environment variables or a release-local `.env` symlink. On production, keep the real file here:

```text
/home/u913239002/domains/mcdomenuusa.com/deploy/shared/.env
```

Use `.env.production.example` as the template. The production `.env` is production-connected. The repository examples are not production-connected.

Important production values:

```text
WP_ENVIRONMENT_TYPE=production
WP_HOME=https://mcdomenuusa.com
WP_SITEURL=https://mcdomenuusa.com
DISALLOW_FILE_MODS=true
AUTOMATIC_UPDATER_DISABLED=true
WP_AUTO_UPDATE_CORE=false
MCPRICES_ENABLE_CANONICAL_REDIRECTS=false
```

Keep `MCPRICES_ENABLE_CANONICAL_REDIRECTS=false` during the SEO noindex observation window. Change it to `true` only when you intentionally want canonical loser URLs to become 301 redirects.

## Normal Release Flow

1. Run the `Production Release` workflow manually.
2. Leave `prepare_release=true`.
3. Leave `activate_release=false` for a staged preparation.
4. Confirm the prepared release exists under `deploy/releases/<release_id>`.
5. Re-run the workflow with the same `release_id`, `prepare_release=false`, and `activate_release=true` to switch traffic to that existing prepared release.

This lets you prepare a full release without touching live traffic.

Release IDs may only contain letters, numbers, dots, underscores, and hyphens.

## Local Windows Release Flow

From this Windows PC, run this in PowerShell from the WordPress project folder:

```powershell
.\scripts\deploy\deploy-hostinger.ps1
```

That command builds the current local worktree, uploads a zip to Hostinger, prepares a release under `deploy/releases/<release_id>`, switches `public_html` through the release symlink, checks the public site and `wp-login.php`, and requests a LiteSpeed cache purge.

Useful options:

```powershell
# Prepare a release without switching live traffic.
.\scripts\deploy\deploy-hostinger.ps1 -PrepareOnly

# First-time conversion only, when public_html is still a real folder.
.\scripts\deploy\deploy-hostinger.ps1 -AllowLivePathReplace -SeedSharedFromLive

# Deploy a named release id.
.\scripts\deploy\deploy-hostinger.ps1 -ReleaseId 20260711-author-seo
```

The script reads the password from `HOSTINGER_SSH_PASSWORD` if it exists; otherwise it prompts for it. Do not put the password in the repository.

## Migration Flow

Database migrations are gated by two controls:

```text
run_migrations=true
migration_approval=RUN_MIGRATIONS
```

If either value is missing, the deployment exits before running `wp core update-db`. This is intentional because migrations can affect live production state.

## Health Checks

An activated release is not considered successful until health checks pass:

```text
PRODUCTION_HEALTH_URL=https://mcdomenuusa.com/
PRODUCTION_ADMIN_HEALTH_URL=https://mcdomenuusa.com/wp-login.php
```

If health checks fail after activation, `activate-release.sh` automatically points `current` and `public_html` back to the previous release when a previous release exists.

## Rollback

Use the same `Production Release` workflow and set `rollback_release` to a known release id. The rollback job switches the live symlink back to that release and runs health checks.

Fast manual rollback from SSH:

```bash
ROLLBACK_RELEASE=<release-id> \
DEPLOY_ROOT=/home/u913239002/domains/mcdomenuusa.com/deploy \
LIVE_PATH=/home/u913239002/domains/mcdomenuusa.com/public_html \
SITE_URL=https://mcdomenuusa.com \
HEALTH_URL=https://mcdomenuusa.com/ \
ADMIN_HEALTH_URL=https://mcdomenuusa.com/wp-login.php \
bash /home/u913239002/domains/mcdomenuusa.com/deploy/releases/<release-id>/scripts/deploy/rollback-release.sh
```

## Local Live-Like Test

After the deployment files are committed, run this from Git Bash or WSL:

```bash
bash scripts/deploy/local-release-test.sh
```

The script builds a release artifact, prepares a local release directory, activates it behind a local `public_html` symlink, health-checks it, and serves it at `http://127.0.0.1:8088`.

## What Is Release Prepared vs Production Connected

Release prepared:

```text
deploy/releases/<release_id>/
```

This is an immutable code artifact plus symlinks to shared state. It should not receive live traffic until activation.

Production connected:

```text
deploy/shared/.env
deploy/shared/uploads/
deploy/shared/cache/
deploy/shared/litespeed/
deploy/current
public_html
```

These paths connect a release to the real database, real uploads, live cache, and live traffic. Treat changes here as production operations. LiteSpeed's generated assets must remain shared so cached HTML never points at CSS or JavaScript that disappeared with an earlier immutable release.

## WordPress Update Resilience

`wp-content/mu-plugins/mcprices-update-safety.php` is a must-use safety layer. It keeps the Kadence and McPrices layout styles as direct theme asset URLs instead of LiteSpeed-generated combined files. Because must-use plugins are loaded before the active theme and are not replaced by WordPress core updates, a core update or LiteSpeed cache rebuild cannot leave cached pages with a missing header stylesheet.

After a WordPress, theme, or plugin update, clear Hostinger's Cache Manager and CDN cache once so previously cached HTML is replaced. This only clears stale copies already created before the safety layer; future cache rebuilds retain valid direct URLs for the critical styles.

For a first-time conversion from an existing `public_html` install, run release preparation with `SEED_SHARED_FROM_LIVE=true` so existing `wp-content/uploads`, `wp-content/cache`, and `wp-content/litespeed` are copied into `deploy/shared/` before activation.

# Deployment Guide

## Overview

This project does **not** use Git-based deployment. All production deployments are done via `rsync` over SSH using `sshpass` for non-interactive password authentication.

---

## Pre-requisites

### 1. Install `sshpass`
```bash
brew install sshpass
```

Verify it is available:
```bash
/opt/homebrew/bin/sshpass --version
```

### 2. SSH Credentials
Credentials are stored in `SSH_Access.txt` in the project root. The file must contain a line in the format:
```
pass: your_password_here
```

**Never commit `SSH_Access.txt` to version control.**

---

## Production Server Details

| Property       | Value                              |
|----------------|------------------------------------|
| Host           | `gator4311.hostgator.com`          |
| Username       | `schooics`                         |
| App Root       | `/home4/schooics/public_html`      |
| PHP Binary     | `php` (PHP 8.3)                    |

---

## Step 1 — Deploy Files with rsync

Run from the project root (`/Applications/MAMP/htdocs/schools`):

```bash
SSHPASS="$(awk -F'pass: ' '/^pass:/{print $2; exit}' SSH_Access.txt)" \
sshpass -e rsync -azR \
  -e "ssh -o PubkeyAuthentication=no -o PreferredAuthentications=password,keyboard-interactive -o NumberOfPasswordPrompts=1 -o StrictHostKeyChecking=accept-new" \
  ./path/to/file1 \
  ./path/to/file2 \
  schooics@gator4311.hostgator.com:/home4/schooics/public_html/
```

### Critical flags explained

| Flag | Purpose |
|------|---------|
| `-a` | Archive mode — preserves permissions, timestamps, symlinks |
| `-z` | Compress data during transfer |
| `-R` | **Relative paths** — preserves full directory structure on the remote. Without this, files land flat in the destination root |
| `-e "ssh ..."` | Custom SSH options (password auth, no key auth) |

### Example — deploying a new controller and migration

```bash
SSHPASS="$(awk -F'pass: ' '/^pass:/{print $2; exit}' SSH_Access.txt)" \
sshpass -e rsync -azR \
  -e "ssh -o PubkeyAuthentication=no -o PreferredAuthentications=password,keyboard-interactive -o NumberOfPasswordPrompts=1 -o StrictHostKeyChecking=accept-new" \
  ./app/Admin/Controllers/MyNewController.php \
  ./app/Admin/routes.php \
  ./app/Models/MyModel.php \
  ./database/migrations/2026_04_28_000000_create_my_table.php \
  ./resources/views/admin/my-view.blade.php \
  schooics@gator4311.hostgator.com:/home4/schooics/public_html/
```

---

## Step 2 — Run Post-Deploy Artisan Commands

After files are uploaded, run migrations, seeders, and cache clear in a single SSH session:

```bash
SSHPASS="$(awk -F'pass: ' '/^pass:/{print $2; exit}' SSH_Access.txt)" \
sshpass -e ssh \
  -o PubkeyAuthentication=no \
  -o PreferredAuthentications=password,keyboard-interactive \
  -o NumberOfPasswordPrompts=1 \
  -o StrictHostKeyChecking=accept-new \
  schooics@gator4311.hostgator.com \
  'cd ~/public_html && php artisan migrate --force && php artisan db:seed --force && php artisan optimize:clear'
```

### Command breakdown

| Command | Purpose |
|---------|---------|
| `php artisan migrate --force` | Runs all pending migrations (required on production — `--force` bypasses the confirmation prompt) |
| `php artisan db:seed --force` | Runs database seeders (menu entries, config defaults, etc.) |
| `php artisan optimize:clear` | Clears config, route, view, and event caches so changes take effect immediately |

> **Note:** Run `db:seed --force` only when new seeder classes were added or modified. It is safe to skip if no seeders changed.

---

## Step 3 — Verify the Deployment

### Check routes are registered
```bash
SSHPASS="$(awk -F'pass: ' '/^pass:/{print $2; exit}' SSH_Access.txt)" \
sshpass -e ssh \
  -o PubkeyAuthentication=no \
  -o PreferredAuthentications=password,keyboard-interactive \
  -o NumberOfPasswordPrompts=1 \
  -o StrictHostKeyChecking=accept-new \
  schooics@gator4311.hostgator.com \
  'cd ~/public_html && php artisan route:list | grep your-module-prefix'
```

### Check menu entries in the database
```bash
SSHPASS="$(awk -F'pass: ' '/^pass:/{print $2; exit}' SSH_Access.txt)" \
sshpass -e ssh \
  -o PubkeyAuthentication=no \
  -o PreferredAuthentications=password,keyboard-interactive \
  -o NumberOfPasswordPrompts=1 \
  -o StrictHostKeyChecking=accept-new \
  schooics@gator4311.hostgator.com \
  'cd ~/public_html && php artisan tinker --execute="dump(DB::table(\"admin_menu\")->where(\"uri\", \"like\", \"%your-module%\")->get([\"id\",\"parent_id\",\"order\",\"title\",\"uri\"])->toArray());"'
```

---

## Complete One-Liner (Files + Migrate + Clear)

For convenience, combine Steps 1 and 2 into a shell script or run them back-to-back:

```bash
# 1. Deploy files
SSHPASS="$(awk -F'pass: ' '/^pass:/{print $2; exit}' SSH_Access.txt)" \
sshpass -e rsync -azR \
  -e "ssh -o PubkeyAuthentication=no -o PreferredAuthentications=password,keyboard-interactive -o NumberOfPasswordPrompts=1 -o StrictHostKeyChecking=accept-new" \
  ./app/Admin/Controllers/MyController.php \
  ./database/migrations/2026_xx_xx_xxxxxx_my_migration.php \
  schooics@gator4311.hostgator.com:/home4/schooics/public_html/

# 2. Migrate and clear caches
SSHPASS="$(awk -F'pass: ' '/^pass:/{print $2; exit}' SSH_Access.txt)" \
sshpass -e ssh \
  -o PubkeyAuthentication=no \
  -o PreferredAuthentications=password,keyboard-interactive \
  -o NumberOfPasswordPrompts=1 \
  -o StrictHostKeyChecking=accept-new \
  schooics@gator4311.hostgator.com \
  'cd ~/public_html && php artisan migrate --force && php artisan optimize:clear'
```

---

## Typical Files Changed Per Module

When building a new module, the following file types are usually deployed:

| File Type | Path Pattern |
|-----------|--------------|
| Controller | `app/Admin/Controllers/` |
| Model | `app/Models/` |
| Routes | `app/Admin/routes.php` |
| Migration | `database/migrations/` |
| Seeder | `database/seeders/` |
| Blade views | `resources/views/admin/` or `resources/views/print/` |
| Excel export class | `app/Exports/` |
| Web routes | `routes/web.php` |
| Exception handler | `app/Exceptions/Handler.php` |

---

## Common Mistakes to Avoid

| Mistake | Consequence | Fix |
|---------|-------------|-----|
| Omitting `-R` flag in rsync | Files land flat in `/home4/schooics/public_html/` instead of correct subdirectories | Always use `-azR` |
| Forgetting `./` prefix on file paths | `-R` (relative) does not work correctly without the `./` prefix | Prefix all paths with `./` |
| Running rsync from the wrong directory | Paths become wrong relative to project root | Always `cd` into `/Applications/MAMP/htdocs/schools` first |
| Not running `optimize:clear` | Old cached routes/config serve stale data | Always clear caches after deploy |
| Running `migrate` without `--force` | Artisan prompts for confirmation on production and hangs | Always pass `--force` in SSH sessions |

---

## Security Notes

- `SSH_Access.txt` must not be committed to any repository.
- Add it to `.gitignore` if Git is ever used:
  ```
  SSH_Access.txt
  ```
- Credentials are read at deploy time from the local file only and passed via environment variable (`SSHPASS`) — never hardcoded in scripts.

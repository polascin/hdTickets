# GitHub Actions Deployment

This repository deploys `main` automatically to the production server using
GitHub Actions. The workflow lives in `.github/workflows/deploy.yml` and runs
Deployer against the `production` host configuration defined in `deploy.php`.

## Required Secrets

Configure the following repository secrets (Settings → Secrets and variables → Actions):

- `DEPLOY_HOST` – Hostname or IP of the production server (DigitalOcean droplet, defaults to `hd-tickets.com`).
- `DEPLOY_USER` – SSH user (usually `deploy`).
- `DEPLOY_PATH` – Deployment root (default `/var/www/hdtickets`).
- `DEPLOY_PHP_BINARY` – PHP binary path on the server (default `/usr/bin/php8.3`).
- `DEPLOY_SSH_PRIVATE_KEY` – Private key with access to the deploy user.

The workflow adds these values to the environment so Deployer can connect.

## Triggering

- Automatic: any push to `main`.
- Manual: via the *Run workflow* button in the Actions tab (workflow_dispatch).

## Notes

- The deployment job uses `shivammathur/setup-php` and installs Deployer via
  Composer globally.
- SSH host key verification is handled by `ssh-keyscan`; ensure the server is
  accessible from GitHub runners.
- Concurrency is limited to a single in-flight deployment to avoid overlap.
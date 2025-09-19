# E2E Testing Guide

This project uses Playwright for end-to-end UI checks and screenshots.

Prerequisites
- PHP 8.3, Node 18+, Composer, npm

Local usage
1) Start server + run tests (one command)
```
npm run e2e
```
This starts `php artisan serve` on http://127.0.0.1:8000 and runs Playwright tests.

2) Manual way
```
php artisan serve
npm run e2e:install   # First time only (browsers)
npm run test:e2e
```

Authenticated tests
- For dashboard/profile tests, set environment variables (never commit credentials):
```
E2E_EMAIL=user@example.com E2E_PASSWORD=secret npm run test:e2e
```

Artifacts
- Screenshots: `tests/e2e/screenshots/`
- Traces on failure are retained; you can open them with `npx playwright show-trace <trace.zip>`

Automation
- No hosted CI workflows are configured. Run E2E tests locally with `npm run e2e`.

Tips
- Set `E2E_BASE_URL` if your dev server runs on a different host/port.
- To focus a single test, use `test.fixme` or `test.only` during development.

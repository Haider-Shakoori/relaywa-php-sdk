# RelayWA developer distribution — launch checklist

## Prerequisite
- [ ] Confirm [developer acquisition PR #3](https://github.com/Haider-Shakoori/relaywa-php-sdk/pull/3) has merged and links work on main.
- [ ] Verify Packagist [relaywa/sdk](https://packagist.org/packages/relaywa/sdk) installs in a clean PHP 8.1+ environment.
- [ ] Verify Laravel example is consistent with the SDK interface and the app's consent workflow.
- [ ] Review risk disclosure (unofficial QR-linked WhatsApp Web, not Meta Cloud API).

## GitHub and Packagist
- [ ] Add GitHub repository topics using repository Settings/About: `laravel`, `php`, `whatsapp-api`, `whatsapp-notifications`, `php-sdk`.
- [ ] Update GitHub About summary to: "PHP/Laravel SDK for QR-linked WhatsApp messaging, sessions and webhooks. Unofficial integration."
- [ ] Verify default branch README presents live Packagist availability; release v0.1.0 metadata may remain stale until a new tag is released.
- [ ] Do not tag another package release solely for SEO; version and test actual code changes when needed.

## Distribution
- [ ] Publish `docs/outreach/laravel-order-notifications-devto.md` on DEV Community after reviewing and verifying main links.
- [ ] Link the tutorial from the website/blog documentation via normal code-review/deployment process (not this SDK repository).
- [ ] Answer genuine relevant developer questions with original, useful help; link only where community rules allow.
- [ ] Consider a fully runnable example Laravel app if external users request it; current example is a drop-in integration, not a standalone Laravel project.

## Measurement
- [ ] Use GA4/Search Console to record referrals from GitHub, Packagist and DEV.
- [ ] Measure registrations, QR-linked sessions, first successful messages, trials and payments separately.
- [ ] Review metrics after 30 days; do not claim paid acquisition until verified.

No automated outreach, credential use, live message sends, production changes or third-party publication are performed by this checklist.

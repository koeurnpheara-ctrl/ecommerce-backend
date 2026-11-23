# Security and production readiness checklist

- [ ] Set `APP_DEBUG=false` and `APP_ENV=production`.
- [ ] Use a unique, strong `APP_KEY`; never regenerate it on an existing installation.
- [ ] Store secrets in server environment/secret manager, never in Git or Docker build args.
- [ ] Change seeded administrator credentials immediately; disable public admin registration.
- [ ] Enforce HTTPS and secure headers at the edge.
- [ ] Restrict CORS to the exact Vue frontend origin(s).
- [ ] Keep MySQL on the private Docker network; do not publish port 3306.
- [ ] Configure backups, restore drills, monitoring, and log rotation.
- [ ] Configure queue workers and a scheduler process for production jobs.
- [ ] Configure rate limits appropriate to login, password reset, reviews, and checkout.
- [ ] Verify payment webhook signatures using the payment provider's official SDK.
- [ ] Use idempotency keys for payment callbacks and retryable checkout requests.
- [ ] Scan and validate uploads; use generated filenames and private storage where appropriate.
- [ ] Set correct filesystem ownership and least-privilege database credentials.
- [ ] Run automated tests and dependency vulnerability checks in CI.
- [ ] Review retention, privacy, consumer protection, tax, and payment requirements for target markets.

This scaffold is not a substitute for a security review, load testing, payment-provider certification,
or operational readiness testing.

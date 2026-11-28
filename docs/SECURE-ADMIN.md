# Secure staff operations
The real Laravel dashboard is /admin, login /login. GitHub Pages cannot run the backend. No static login or private records may be published.
- Administrator: operations and security overview, catalogue/content, media, crop periods, documents, sourcing, redirects and SEO. Other staff have scoped editor, quality, sales or analytics access.
- Production requires MFA, regardless of configuration override. Password verification alone does not establish an authenticated session. TOTP uses 30-second steps with replay prevention; recovery codes are hashed and single-use. TOTP secrets encrypted at rest with APP_KEY.
- Per-account and route rate limits; 5-minute pending verification; 15-minute idle and 8-hour absolute session limits; session ID regeneration; revocation and account disable.
- CSRF, private storage, security logging and headers. Never place credentials, secrets, buyer data or real dashboard screenshots on public branches.
## Provisioning (private server terminal only)
Configure production HTTPS, APP_ENV=production, APP_DEBUG=false, unique APP_KEY, SESSION_SECURE_COOKIE=true, SESSION_HTTP_ONLY=true, SESSION_SAME_SITE=lax and private database/storage. Restrict the document root to public/. Configure trusted proxies only if required by the host.
Run migrations and create the administrator using php artisan verdleaf:admin EMAIL. Use a unique generated password of at least 14 characters. Run php artisan verdleaf:mfa-enroll EMAIL; enter the secret in an authenticator app (SHA1, six digits, 30-second period); store the recovery codes offline. Never run enrollment in public CI. Login at /login.
Revoke sessions using php artisan verdleaf:revoke EMAIL, add --disable to disable the account. Reset MFA only after identity verification. No self-signup or public reset endpoint.
## Before production
Perform dependency locking and audit, host/TLS validation, backup/restore test, file quarantine and antivirus scanning, retention policy and logging review, independent penetration testing and security review of the authentication implementation. Verify all staff and upload permissions. No software is completely secure. No live deployment has been performed.

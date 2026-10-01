# WHMCS Customer Billing Portal

Laravel 12 + Blade customer billing portal powered by the WHMCS API.

The portal is designed for shared hosting/cPanel deployment and keeps **WHMCS as the billing source of truth**. Customers can manage billing, services, invoices, domains, transactions, support tickets and hosting-related services from one branded portal.

> **Production status:** The application structure and core modules are prepared for production deployment. Final production readiness still requires live-server verification of your WHMCS API, bKash merchant credentials/callbacks, cPanel/WHM API, DNS/SSL configuration and any enabled hosting/reseller features. Payment and hosting integrations should not be considered live-verified until those tests pass on the target server.

## Main Features

### Customer Portal

- WHMCS customer login through WHMCS API v2
- WHMCS two-factor verification flow
- Customer dashboard
- Services / products
- Domains
- Invoices
- Transactions
- Orders
- Support tickets
- Open and reply to support tickets
- Mobile-friendly Blade UI
- Customer-to-WHMCS authorization checks

### Billing & Checkout

- WHMCS AddOrder based checkout
- WHMCS invoice creation
- Configurable checkout payment method
- Automatic order acceptance option
- Automatic service setup option
- Payment status tracking
- Portal payment records
- Payment/reference/transaction tracking
- Configurable cart and checkout settings

### bKash

The project includes a tokenized bKash checkout integration with:

- Token grant
- Create payment
- Execute payment
- Refund support
- Configurable merchant credentials
- Configurable callback URL
- Configurable refund window
- Payment status stored in the portal database

The default refund policy is designed around a configurable **5-minute window** and can be restricted when the related service is not Active.

**Important:** Before accepting real customer money, verify the exact merchant API contract, credentials, callback URL, payment gateway identifier and refund/accounting behavior with the production bKash account.

### Web Hosting

The portal includes a customer-facing Web Hosting module designed to hide the underlying cPanel implementation from customers.

Current foundation:

- Active WHMCS hosting-service detection
- Customer/service authorization
- Hosting service dashboard
- cPanel fallback access
- Configurable custom hosting-panel switch
- cPanel/WHM integration foundation
- Future-ready resource/hosting controls

The custom HPanel-style layer can be extended with:

- Disk usage
- Bandwidth usage
- CPU usage
- RAM/resource usage
- File Manager
- Email accounts
- MySQL databases/users
- Domains/subdomains
- SSL
- Backups
- Cron jobs
- DNS
- PHP version
- WordPress management
- Git
- Hosting account controls

If the custom panel is disabled or unavailable, the existing cPanel access can remain available as the fallback.

### Reseller Hosting

The reseller module is admin-configurable and disabled by default.

Current foundation:

- Reseller eligibility based on WHMCS active reseller service
- WHM/WHM API integration
- Reseller dashboard
- Hosting account listing
- Create hosting account
- Suspend account
- Unsuspend account
- Terminate account
- Change hosting package
- WHM package listing
- Create package
- Delete package
- Configurable WHM hostname, username, port, API token and SSL verification
- WHM API timeout setting

Planned/extendable reseller features include:

- Account usage
- CPU/RAM/disk/bandwidth statistics
- Customer management
- Account password management
- Domains/subdomains
- Email
- Databases
- DNS
- SSL
- Backups
- White-label branding
- Reseller API
- WHMCS module integration
- Notifications and alerts

### Admin Panel

The admin area includes:

- Admin authentication
- Role/permission system
- Dashboard
- Payment management
- Refund permission
- Staff management
- Runtime settings
- Hosting management
- Reseller management
- Update Center

Sensitive runtime credentials are stored encrypted in the database using Laravel encryption.

### Admin-only Update Center

The application contains an **admin-only GitHub Update Center**.

Features:

- Current version display
- Latest release check
- Release notes display
- Admin-only update notification
- One-click backup + update workflow
- Update lock to reduce concurrent updates
- Application backup before update
- Migration execution after update
- Failed-update application-file restoration
- Private GitHub repository token support
- Configurable GitHub API timeout
- GitHub release workflow

Customers do **not** see:

- Update notifications
- Update controls
- GitHub repository information
- GitHub access tokens
- Release-management settings

The update source is the main repository:

`mahfuzreham/whmcs-order-management`

You can make the repository private later and configure an appropriate GitHub token in **Admin → Updates**.

> **Update Center production note:** Test the update workflow on the live server before relying on one-click updates. Composer dependency changes, database rollback, backup retention and cryptographic release verification should be treated as additional production-hardening steps.

## Requirements

- PHP 8.2 or 8.3
- MySQL/MariaDB
- HTTPS
- Required Laravel PHP extensions
- cPanel/shared hosting
- Composer for building/updating dependencies
- A writable `storage/` directory
- A writable `bootstrap/cache/` directory

The core portal does not require Docker, Redis, Supervisor, systemd or a VPS-only background service.

## Recommended cPanel Deployment

For an existing `resellnom.com` website, do **not** replace the existing root website.

Recommended structure:

```
/home/ACCOUNT/
├── public_html/          # existing resellnom.com
└── billing-portal/       # Laravel application
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/
    ├── resources/
    ├── routes/
    ├── storage/
    └── vendor/
```

### Recommended option

Create a subdomain such as:

`billing.resellnom.com`

and point its document root to:

`/home/ACCOUNT/billing-portal/public`

This keeps the existing `resellnom.com` website separate from Laravel.

### Exact /dashboard and /cart URLs

If the required URLs are:

- `https://resellnom.com/dashboard`
- `https://resellnom.com/cart`

then the existing website's Apache rewrite rules must route only those Laravel paths to the Laravel application's `public/index.php`.

Do **not** replace the existing root `.htaccess` blindly. Preserve the current website rules and add only the required Laravel routing.

## Installation

1. Upload the complete Laravel project.
2. Make sure `vendor/` is present, or run Composer on the server.
3. Point the domain/subdomain document root to the project's `public/` directory.
4. Open `/install`.
5. Enter the MySQL/MariaDB database credentials.
6. The installer creates `.env`.
7. The installer generates the Laravel `APP_KEY`.
8. The installer runs the database migrations.
9. Open `/dashboard`.
10. Configure the application from **Admin → Settings**.

After successful installation, the installer is locked using:

`storage/app/installed`

## Admin Configuration

After installation, configure the required values from the admin panel.

### WHMCS

Configure:

- WHMCS URL
- API identifier
- API secret
- API timeout
- Support department

### bKash

Configure:

- Enable/disable bKash
- Base URL
- App key
- App secret
- Username
- Password
- Callback URL
- Timeout
- Checkout settings
- Refund settings

### Hosting / WHM

Configure only if the corresponding modules are required:

- WHM hostname
- WHM API username
- WHM API token
- WHM port
- SSL verification
- WHM API timeout
- Web Hosting module
- Reseller Hosting module

Never expose WHM API tokens or payment credentials to customers.

## Security

Recommended production practices:

- Use HTTPS everywhere.
- Use a strong Laravel `APP_KEY`.
- Use strong admin credentials.
- Keep GitHub update tokens private.
- Keep WHM API tokens private.
- Never commit `.env` to Git.
- Use least-privilege API credentials where supported.
- Keep PHP and Laravel dependencies updated.
- Restrict database access to the application where possible.
- Verify customer ownership before displaying or modifying WHMCS services.
- Keep admin permissions limited to the required staff role.
- Back up the database before major production changes.
- Test payment/refund flows with controlled transactions before enabling real customer payments.

## Production Checklist

Before going live, verify the following on the actual cPanel server:

### Application

- [ ] PHP 8.2/8.3 selected for the domain
- [ ] Required PHP extensions enabled
- [ ] `storage/` writable
- [ ] `bootstrap/cache/` writable
- [ ] HTTPS/SSL active
- [ ] Correct document root points to Laravel `public/`
- [ ] APP_KEY generated
- [ ] Production `.env` configured
- [ ] Debug disabled
- [ ] Database migrations completed

### WHMCS

- [ ] WHMCS API URL tested
- [ ] API identifier/secret tested
- [ ] Customer login tested
- [ ] 2FA flow tested
- [ ] Services tested
- [ ] Invoices tested
- [ ] Transactions tested
- [ ] Orders tested
- [ ] Support ticket create/reply tested

### Payments

- [ ] bKash production credentials verified
- [ ] bKash callback URL verified
- [ ] Successful payment tested
- [ ] Failed/cancelled payment tested
- [ ] Duplicate callback behavior tested
- [ ] Invoice payment recording tested
- [ ] Order acceptance/setup behavior tested
- [ ] Refund tested with a controlled transaction
- [ ] Refund window tested
- [ ] WHMCS accounting/credit behavior verified

### Hosting

- [ ] WHM/cPanel API connectivity tested
- [ ] Hosting service authorization tested
- [ ] cPanel fallback tested
- [ ] Resource data verified if enabled
- [ ] Customer cannot access another customer's hosting account

### Reseller

- [ ] Reseller eligibility tested
- [ ] WHM account creation tested
- [ ] Package creation tested
- [ ] Suspend/unsuspend tested
- [ ] Terminate tested
- [ ] Package change tested
- [ ] WHM token permissions verified

### Update Center

- [ ] GitHub token tested
- [ ] Private repository access tested if repository is private
- [ ] Release check tested
- [ ] Backup creation tested
- [ ] Update on a staging/live test copy completed
- [ ] Migration execution verified
- [ ] Failed-update recovery tested
- [ ] Composer/dependency changes handled for each release

## Current Scope / Known Production Verification Items

The codebase is structured for production deployment, but some integrations depend on the target provider/server and therefore must be verified in the user's environment.

In particular:

1. **bKash production payment/refund behavior** must be tested with the real merchant account.
2. **WHM/cPanel API access** must be tested with the actual WHM credentials and permissions.
3. **Hosting resource metrics and advanced HPanel features** require additional WHM/cPanel API endpoints before they can be considered complete.
4. **Nagad** is not yet included as a completed payment gateway in this repository.
5. The Update Center should be tested on the target cPanel environment before being relied on for unattended production updates.
6. Composer dependency updates require an appropriate production deployment strategy; the updater does not automatically guarantee dependency installation on every shared-hosting environment.

## Deployment Philosophy

The portal is intentionally designed so that:

```
Customer
   ↓
Laravel Billing Portal
   ↓
WHMCS API
   ↓
WHMCS = Billing Source of Truth
```

For hosting:

```
Customer
   ↓
Laravel Hosting UI
   ↓
WHM / cPanel API
   ↓
Hosting Account
```

For payments:

```
Customer
   ↓
Portal Checkout
   ↓
Payment Gateway
   ↓
WHMCS Invoice Payment
   ↓
Order / Service Setup
```

This keeps customer-facing UI separate from the underlying WHMCS/cPanel implementation while preserving the existing billing infrastructure.

## License

See [LICENSE](LICENSE).

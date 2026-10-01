# WHMCS Customer Billing Portal

**Status: Testing Project / Development**

Laravel + Blade customer billing portal powered by the WHMCS API.

## Current Build

The legacy WHMCS Order Management addon code has been removed. The repository is now being rebuilt as a standalone Laravel customer portal.

Current foundation includes:

- Laravel project structure
- Blade layout and customer login screen
- WHMCS API client service
- WHMCS REST customer authentication foundation
- WHMCS customer lookup
- Dashboard service/domain/unpaid-invoice summary
- Portal session middleware
- Secure environment configuration template

## Architecture

Customer Browser → Laravel + Blade → WHMCS REST/API → WHMCS Billing

WHMCS remains the billing source of truth. Laravel provides the customer-facing interface.

## Authentication

The portal uses the WHMCS REST API user session endpoint for customer authentication. WHMCS documents this endpoint as accepting customer email and password, with a separate verification flow when 2FA is enabled.

Billing data is retrieved separately through WHMCS API commands including GetClients, GetClientsProducts, GetClientsDomains and invoice APIs.

## Planned Modules

- Dashboard
- Services
- Service details
- Domains
- Invoices
- Invoice payment
- Payment history
- Transactions
- Support tickets
- Ticket replies
- Customer profile
- Security / 2FA
- bKash payment integration
- Nagad payment integration
- Payment callbacks/webhooks
- Notifications
- Mobile-first responsive UI

## Requirements

- PHP 8.2+
- Composer
- Node.js / npm
- MySQL or SQLite
- Laravel 12
- WHMCS with API access
- WHMCS 9+ recommended for the REST authentication flow

## Setup

    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate
    php artisan serve

Configure WHMCS in .env:

    WHMCS_URL=https://billing.example.com
    WHMCS_IDENTIFIER=
    WHMCS_SECRET=
    WHMCS_TIMEOUT=15

Never commit .env or API credentials.

## Testing

This is a testing/development project. Use a staging WHMCS installation first.

Test:

1. Customer login
2. WHMCS authentication errors
3. 2FA-required accounts
4. Customer profile loading
5. Service counts
6. Domain counts
7. Unpaid invoice counts
8. API timeout/error handling
9. Session logout
10. Mobile UI

## Security

- WHMCS API secrets remain server-side.
- Customer passwords are sent only to the WHMCS authentication endpoint over HTTPS.
- Do not expose WHMCS API credentials in Blade or JavaScript.
- Regenerate the Laravel session after successful authentication.
- Verify payment callbacks before recording any payment.
- Add rate limiting before production deployment.

## License

See the repository license file.

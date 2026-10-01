# WHMCS Customer Billing Portal

**Status: Testing Project / Development**

Laravel + Blade customer billing portal powered by the WHMCS API.

## Architecture

Customer Browser → Laravel + Blade Portal → WHMCS API → WHMCS Billing System

WHMCS remains the billing source of truth. Laravel provides the customer-facing portal and communicates with WHMCS through its API.

## Planned Features

- Customer authentication
- Customer dashboard
- Products and services
- Service details and status
- Domains
- Invoices and invoice details
- Payment history and transactions
- Support tickets and replies
- Customer profile and security settings
- Responsive mobile UI
- WHMCS API integration
- Payment gateway integration
- Secure payment callbacks/webhooks

## Requirements

- PHP 8.2+
- Composer
- Node.js / npm
- MySQL or MariaDB
- Laravel
- WHMCS with API access

## Basic Setup

    composer install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate
    npm install
    npm run build
    php artisan serve

## WHMCS API

Keep all WHMCS credentials in `.env`. Never commit API credentials to GitHub.

Example configuration:

    WHMCS_URL=https://billing.example.com
    WHMCS_IDENTIFIER=
    WHMCS_SECRET=

Use the authentication method supported by the target WHMCS installation.

## Testing Project

This repository is currently intended for **testing and development**. Test against a staging/test WHMCS installation before connecting a production billing system.

Test at minimum:

1. Customer authentication
2. Customer and service retrieval
3. Invoice retrieval
4. Invoice payment
5. Payment callback/webhook verification
6. Recording payments in WHMCS
7. Domain information
8. Support ticket creation and replies
9. API errors and timeouts
10. Session and authentication security
11. Mobile responsive UI

## Security

- Never commit `.env` or WHMCS API secrets.
- Keep API credentials server-side.
- Do not expose secrets in Blade or browser JavaScript.
- Validate customer input.
- Verify payment callbacks/webhooks before recording payments.
- Use HTTPS in production.
- Rate-limit authentication and sensitive endpoints.

## License

See the repository license file.

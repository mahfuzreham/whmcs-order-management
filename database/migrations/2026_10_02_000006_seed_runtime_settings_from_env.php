<?php

use App\Models\AdminSetting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        $values = [
            'whmcs_url' => env('WHMCS_URL', ''),
            'whmcs_identifier' => env('WHMCS_IDENTIFIER', ''),
            'whmcs_secret' => env('WHMCS_SECRET', ''),
            'whmcs_timeout' => env('WHMCS_TIMEOUT', 15),
            'whmcs_support_dept_id' => env('WHMCS_SUPPORT_DEPT_ID', 1),
            'bkash_enabled' => env('BKASH_ENABLED', false),
            'bkash_base_url' => env('BKASH_BASE_URL', 'https://tokenized.pay.bka.sh/v1.2.0-beta'),
            'bkash_app_key' => env('BKASH_APP_KEY', ''),
            'bkash_app_secret' => env('BKASH_APP_SECRET', ''),
            'bkash_username' => env('BKASH_USERNAME', ''),
            'bkash_password' => env('BKASH_PASSWORD', ''),
            'bkash_callback_url' => env('BKASH_CALLBACK_URL', ''),
            'bkash_timeout' => env('BKASH_TIMEOUT', 20),
            'checkout_payment_method' => env('CHECKOUT_PAYMENT_METHOD', 'bkash'),
            'refund_window_minutes' => env('REFUND_WINDOW_MINUTES', 5),
            'refund_enabled' => true,
            'cart_enabled' => true,
            'cart_show_descriptions' => true,
            'cart_allow_quantity' => false,
            'cart_currency' => 'BDT',
            'auto_accept_order' => true,
            'auto_setup_order' => true,
            'order_email' => true,
            'admin_master_email' => env('ADMIN_MASTER_EMAIL', ''),
            'admin_master_password' => env('ADMIN_MASTER_PASSWORD', ''),
        ];

        foreach ($values as $key => $value) {
            if (!AdminSetting::where('key', $key)->exists() && $value !== '') {
                AdminSetting::put($key, $value);
            }
        }
    }

    public function down(): void {}
};

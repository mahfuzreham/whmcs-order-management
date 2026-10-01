<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(WhmcsClient $whmcs)
    {
        abort_unless(AdminSetting::bool('cart_enabled', true), 404);

        $response = $whmcs->call('GetProducts');
        abort_unless(($response['result'] ?? null) === 'success', 502);

        $products = $response['products']['product'] ?? [];
        $currency = AdminSetting::get('cart_currency', 'BDT');
        $groups = [];

        foreach ($products as $product) {
            if (!empty($product['hidden'])) {
                continue;
            }
            $gid = (string) ($product['gid'] ?? 0);
            $groups[$gid][] = $product;
        }

        return view('cart.index', [
            'groups' => $groups,
            'currency' => $currency,
            'settings' => [
                'show_descriptions' => AdminSetting::bool('cart_show_descriptions', true),
                'allow_quantity' => AdminSetting::bool('cart_allow_quantity', false),
                'checkout_notice' => AdminSetting::get('cart_checkout_notice', ''),
                'terms_url' => AdminSetting::get('cart_terms_url', ''),
            ],
        ]);
    }

    public function add(Request $request)
    {
        abort_unless(AdminSetting::bool('cart_enabled', true), 404);

        $data = $request->validate([
            'pid' => ['required', 'integer', 'min:1'],
            'billingcycle' => ['nullable', 'string', 'in:monthly,quarterly,semiannually,annually,biennially,triennially,onetime'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:20'],
        ]);

        $cart = session('billing_cart', []);
        $key = (string) $data['pid'].'|'.($data['billingcycle'] ?? 'monthly');
        $cart[$key] = [
            'pid' => (int) $data['pid'],
            'billingcycle' => $data['billingcycle'] ?? 'monthly',
            'qty' => AdminSetting::bool('cart_allow_quantity', false) ? (int) ($data['qty'] ?? 1) : 1,
        ];

        session(['billing_cart' => $cart]);

        return redirect()->route('cart')->with('success', 'Product added to cart.');
    }

    public function remove(string $key)
    {
        $cart = session('billing_cart', []);
        unset($cart[$key]);
        session(['billing_cart' => $cart]);

        return back()->with('success', 'Product removed from cart.');
    }
}

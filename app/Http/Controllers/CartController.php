<?php

namespace App\Http\Controllers;

use App\Models\AdminSetting;
use App\Services\Whmcs\WhmcsClient;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(WhmcsClient $whmcs): View
    {
        abort_unless(AdminSetting::bool('cart_enabled', true), 404);

        $response = $whmcs->call('GetProducts');
        abort_unless(($response['result'] ?? null) === 'success', 502);

        $products = $response['products']['product'] ?? [];
        $currency = AdminSetting::get('cart_currency', 'BDT');
        $groups = [];

        foreach ($products as $product) {
            if (!empty($product['hidden'])) continue;
            $gid = (string) ($product['gid'] ?? 0);
            $groups[$gid][] = $product;
        }

        return view('cart.index', [
            'groups'=>$groups,
            'currency'=>$currency,
            'settings'=>[
                'show_descriptions'=>AdminSetting::bool('cart_show_descriptions', true),
                'allow_quantity'=>AdminSetting::bool('cart_allow_quantity', false),
                'checkout_notice'=>AdminSetting::get('cart_checkout_notice', ''),
                'terms_url'=>AdminSetting::get('cart_terms_url', ''),
            ],
        ]);
    }

    public function add(Request $request, WhmcsClient $whmcs)
    {
        abort_unless(AdminSetting::bool('cart_enabled', true), 404);

        $data=$request->validate([
            'pid'=>['required','integer','min:1'],
            'billingcycle'=>['nullable','string','in:monthly,quarterly,semiannually,annually,biennially,triennially,onetime'],
            'qty'=>['nullable','integer','min:1','max:20'],
        ]);

        $products=$whmcs->call('GetProducts',['pid'=>$data['pid']]);
        $product=($products['products']['product'] ?? [])[0] ?? null;
        abort_unless($product && empty($product['hidden']), 404);

        $pricing=$product['pricing'][AdminSetting::get('cart_currency','BDT')] ?? reset($product['pricing']) ?: [];
        $cycle=$data['billingcycle'] ?? 'monthly';
        abort_unless(isset($pricing[$cycle]) && is_numeric($pricing[$cycle]) && (float)$pricing[$cycle] >= 0, 422);

        $cart=session('billing_cart', []);
        $key=(string)$data['pid'].'|'.$cycle;
        $cart[$key]=[
            'pid'=>(int)$data['pid'],
            'billingcycle'=>$cycle,
            'qty'=>AdminSetting::bool('cart_allow_quantity', false) ? (int)($data['qty'] ?? 1) : 1,
            'name'=>$product['name'] ?? 'Product',
        ];
        session(['billing_cart'=>$cart]);

        return redirect()->route('cart')->with('success','Product added to cart.');
    }

    public function remove(string $key)
    {
        $cart=session('billing_cart', []);
        unset($cart[$key]);
        session(['billing_cart'=>$cart]);
        return back()->with('success','Product removed from cart.');
    }

    public function checkout(WhmcsClient $whmcs): View
    {
        abort_unless(AdminSetting::bool('cart_enabled', true), 404);

        $cart=session('billing_cart', []);
        if (!$cart) return redirect()->route('cart')->withErrors(['cart'=>'Your cart is empty.']);

        $products=[];
        foreach($cart as $item){
            $data=$whmcs->call('GetProducts',['pid'=>(int)$item['pid']]);
            $product=($data['products']['product'] ?? [])[0] ?? null;
            if(!$product || !empty($product['hidden'])) continue;
            $pricing=$product['pricing'][AdminSetting::get('cart_currency','BDT')] ?? reset($product['pricing']) ?: [];
            $price=(float)($pricing[$item['billingcycle']] ?? 0);
            $products[]=[
                'pid'=>(int)$item['pid'],'name'=>$product['name'] ?? 'Product',
                'billingcycle'=>$item['billingcycle'],'qty'=>(int)$item['qty'],
                'price'=>$price,'prefix'=>$pricing['prefix'] ?? '',
                'suffix'=>$pricing['suffix'] ?? AdminSetting::get('cart_currency','BDT'),
            ];
        }

        abort_unless(count($products)===count($cart), 422);

        return view('cart.checkout',[
            'products'=>$products,
            'client'=>session('whmcs_client',[]),
            'paymentMethod'=>config('services.checkout.payment_method','bkash'),
            'currency'=>AdminSetting::get('cart_currency','BDT'),
            'notice'=>AdminSetting::get('cart_checkout_notice',''),
            'termsUrl'=>AdminSetting::get('cart_terms_url',''),
        ]);
    }

    public function placeOrder(Request $request, WhmcsClient $whmcs)
    {
        $cart=session('billing_cart',[]);
        if(!$cart) return redirect()->route('cart')->withErrors(['cart'=>'Your cart is empty.']);

        $data=$request->validate([
            'paymentmethod'=>['required','string','in:bkash'],
            'promo_code'=>['nullable','string','max:100'],
        ]);

        $client=session('whmcs_client',[]);
        $clientId=(int)($client['id'] ?? 0);
        abort_unless($clientId>0,403);

        $products=[];
        foreach($cart as $item){
            $check=$whmcs->call('GetProducts',['pid'=>(int)$item['pid']]);
            $product=($check['products']['product'] ?? [])[0] ?? null;
            if(!$product || !empty($product['hidden'])) {
                return back()->withErrors(['cart'=>'One of the selected products is no longer available.']);
            }
            $pricing=$product['pricing'][AdminSetting::get('cart_currency','BDT')] ?? reset($product['pricing']) ?: [];
            $cycle=$item['billingcycle'];
            if(!isset($pricing[$cycle]) || !is_numeric($pricing[$cycle])) {
                return back()->withErrors(['cart'=>'The selected billing cycle is no longer available.']);
            }
            $products[]=[
                'pid'=>(int)$item['pid'],
                'qty'=>(int)$item['qty'],
                'billingcycle'=>$cycle,
            ];
        }

        $payload=[
            'clientid'=>$clientId,
            'pid'=>array_column($products,'pid'),
            'qty'=>array_column($products,'qty'),
            'billingcycle'=>array_column($products,'billingcycle'),
            'paymentmethod'=>$data['paymentmethod'],
            'noinvoiceemail'=>true,
            'noemail'=>false,
            'clientip'=>$request->ip(),
        ];
        if(!empty($data['promo_code'])) $payload['promocode']=$data['promo_code'];

        $result=$whmcs->call('AddOrder',$payload);
        if(($result['result'] ?? '')!=='success'){
            return back()->withErrors(['order'=>$result['message'] ?? 'Unable to create the order.']);
        }

        $invoiceId=(int)($result['invoiceid'] ?? 0);
        $orderId=(int)($result['orderid'] ?? 0);
        if(!$invoiceId || !$orderId){
            return back()->withErrors(['order'=>'WHMCS created the order but did not return a valid invoice.']);
        }

        session()->forget('billing_cart');

        return redirect()->route('payment.show',$invoiceId)
            ->with('success',"Order #{$orderId} created. Please complete payment for invoice #{$invoiceId}.");
    }
}

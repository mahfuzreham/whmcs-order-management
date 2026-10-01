<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('portal_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->index();
            $table->unsignedBigInteger('invoice_id')->index();
            $table->string('gateway', 32);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 8)->default('BDT');
            $table->string('status', 24)->default('pending')->index();
            $table->string('payment_id')->nullable()->index();
            $table->string('transaction_id')->nullable()->index();
            $table->string('reference')->unique();
            $table->json('payload')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('refund_status', 24)->nullable()->index();
            $table->decimal('refund_amount', 15, 2)->nullable();
            $table->string('refund_id')->nullable()->index();
            $table->json('refund_payload')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('portal_payments'); }
};

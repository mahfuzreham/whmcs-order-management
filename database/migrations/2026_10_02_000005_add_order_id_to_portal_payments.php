<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('portal_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->nullable()->after('client_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('portal_payments', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};

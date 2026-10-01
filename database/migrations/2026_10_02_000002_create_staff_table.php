<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role', 40)->default('staff');
            $table->boolean('active')->default(true);
            $table->json('permissions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('staff'); }
};

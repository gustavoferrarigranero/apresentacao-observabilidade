<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminder_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->cascadeOnDelete();
            $table->string('provider');
            $table->string('request_id', 100);
            $table->string('status');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->unique(['appointment_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_deliveries');
    }
};

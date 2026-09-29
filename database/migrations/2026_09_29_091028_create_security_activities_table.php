<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_activities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('activity_type', 100);
            $table->text('description')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_os')->nullable();

            $table->text('credential_id')->nullable();

            $table->string('status', 30)->default('success');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'activity_type']);
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_activities');
    }
};
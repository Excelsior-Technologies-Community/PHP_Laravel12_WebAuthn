<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('webauthn_credentials', function (Blueprint $table) {

            $table->id();

            // User relationship
            $table->unsignedBigInteger('user_id');

            // Polymorphic relationship
            $table->string('authenticatable_type')->nullable();
            $table->unsignedBigInteger('authenticatable_id')->nullable();

            // Device information
            $table->string('name')->default('Passkey Device');
            $table->string('alias')->nullable();

            // WebAuthn credential information
            $table->text('credential_id');
            $table->longText('credential_public_key')->nullable();
            $table->text('transports')->nullable();

            // WebAuthn authentication counter
            $table->unsignedBigInteger('sign_count')->default(0);

            // WebAuthn configuration
            $table->string('rp_id')->nullable();
            $table->text('origin')->nullable();

            // Device information
            $table->string('device_type')->nullable();
            $table->string('device_os')->nullable();

            // Last successful authentication
            $table->timestamp('last_used_at')->nullable();

            // Laravel timestamps
            $table->timestamps();

            // Soft deletion
            $table->softDeletes();

            // Indexes with SHORT custom names
            $table->index('user_id', 'webauthn_user_idx');

            $table->index(
                'credential_id',
                'webauthn_credential_idx'
            );

            $table->index(
                ['authenticatable_type', 'authenticatable_id'],
                'webauthn_authenticatable_idx'
            );

            // Foreign key
            $table->foreign(
                'user_id',
                'webauthn_user_fk'
            )
            ->references('id')
            ->on('users')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webauthn_credentials');
    }
};
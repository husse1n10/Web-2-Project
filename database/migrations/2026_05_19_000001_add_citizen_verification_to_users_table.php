<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('citizen_verification_status', 20)
                ->default('pending')
                ->after('id_document');
            $table->text('citizen_verification_notes')->nullable()->after('citizen_verification_status');
            $table->timestamp('citizen_verified_at')->nullable()->after('citizen_verification_notes');
            $table->foreignId('citizen_verified_by')
                ->nullable()
                ->after('citizen_verified_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('citizen_verified_by');
            $table->dropColumn([
                'citizen_verified_at',
                'citizen_verification_notes',
                'citizen_verification_status',
            ]);
        });
    }
};

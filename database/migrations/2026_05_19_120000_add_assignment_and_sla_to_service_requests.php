<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('service_requests', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('office_id')
                    ->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('service_requests', 'due_at')) {
                $table->timestamp('due_at')->nullable()->after('completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'assigned_to')) {
                $table->dropConstrainedForeignId('assigned_to');
            }
            if (Schema::hasColumn('service_requests', 'due_at')) {
                $table->dropColumn('due_at');
            }
        });
    }
};

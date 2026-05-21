<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('body');
            $table->string('attachment_name')->nullable()->after('attachment');
            $table->unsignedInteger('attachment_size')->nullable()->after('attachment_name');
        });
    }

    public function down(): void
    {
        Schema::table('support_ticket_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment', 'attachment_name', 'attachment_size']);
        });
    }
};

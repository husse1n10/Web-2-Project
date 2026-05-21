<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicateRequestIds = DB::table('feedbacks')
            ->select('service_request_id')
            ->whereNotNull('service_request_id')
            ->groupBy('service_request_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('service_request_id');

        foreach ($duplicateRequestIds as $serviceRequestId) {
            $keepId = DB::table('feedbacks')
                ->where('service_request_id', $serviceRequestId)
                ->orderBy('id')
                ->value('id');

            DB::table('feedbacks')
                ->where('service_request_id', $serviceRequestId)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('feedbacks', function (Blueprint $table) {
            $table->unique('service_request_id');
        });
    }

    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            $table->dropUnique('feedbacks_service_request_id_unique');
        });
    }
};
